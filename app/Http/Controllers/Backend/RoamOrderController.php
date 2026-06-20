<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RoamOrder;
use App\Services\Roam\RoamOrderService;
use App\Services\Roam\OrderStateMachineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoamOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = RoamOrder::with(['customer', 'items'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->integer('customer_id'));
            })
            ->when($request->filled('our_status'), function ($query) use ($request) {
                $query->where('our_status', $request->integer('our_status'));
            })
            ->when($request->filled('roam_status'), function ($query) use ($request) {
                $query->where('roam_status', $request->integer('roam_status'));
            })
            ->when($request->filled('service_type') || $request->filled('type'), function ($query) use ($request) {
                $query->where('service_type', $request->input('service_type', $request->input('type')));
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($orders);
    }

    public function create()
    {
        return response()->json([
            'customer_options' => Customer::query()
                ->select('id', 'name', 'email', 'phone')
                ->orderBy('name')
                ->get(),
            'service_types' => ['esim', 'physical'],
            'order_types' => ['new', 'recharge'],
            'our_status_options' => RoamOrder::OUR_STATUS_LABELS,
            'roam_status_options' => RoamOrder::ROAM_STATUS_LABELS,
            'back_info' => [
                0 => 'Order number only',
                1 => 'Order details',
                2 => 'PDF URL information',
            ],
        ]);
    }

    public function store(Request $request, RoamOrderService $service)
    {
        // Supports:
        // 1) Admin single-order payload (legacy)
        // 2) Frontend-style payload: { customer_id, items: [...], iccid_numbers: [...] }
        if ($request->has('items')) {
            $validated = $request->validate([
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                'outer_order_id' => ['nullable', 'string', 'max:100'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.sku_id' => ['nullable', 'string', 'max:255'],
                'items.*.sku' => ['nullable', 'string', 'max:255'],
                'items.*.price_id' => ['nullable', 'integer'],
                'items.*.api_code' => ['nullable', 'string', 'max:255'],
                'items.*.service_type' => ['nullable', Rule::in(['esim', 'physical'])],
                'items.*.order_type' => ['nullable', Rule::in(['new', 'recharge'])],
                'items.*.quantity' => ['nullable', 'integer', 'min:1'],
                'items.*.qty' => ['nullable', 'integer', 'min:1'],
                'items.*.total_price' => ['nullable', 'integer', 'min:0'],
                'items.*.price' => ['nullable', 'integer', 'min:0'],
                'items.*.unit_price' => ['nullable', 'integer', 'min:0'],
                'items.*.service_day' => ['nullable'],
                'items.*.daypass_days' => ['nullable'],
                'items.*.service_data' => ['nullable', 'string', 'max:255'],
                'items.*.country_name' => ['nullable', 'string', 'max:255'],
                'items.*.remark' => ['nullable', 'string'],
                'items.*.source_iccid' => ['nullable', 'string', 'max:50'],
                'items.*.iccid' => ['nullable', 'string', 'max:50'],
                'items.*.iccid_no' => ['nullable', 'string', 'max:50'],
                'items.*.other_item_id' => ['nullable', 'string', 'max:100'],
                'items.*.other_price' => ['nullable', 'numeric', 'min:0'],
                'iccid_numbers' => ['nullable', 'array'],
                'back_info' => ['nullable', 'integer', Rule::in([0, 1, 2])],
                'dp_id' => ['nullable', 'integer'],
                'is_send_email' => ['nullable', 'boolean'],
                'pdf_language' => ['nullable', 'string', 'max:10'],
                'customer_email' => ['nullable', 'email'],
            ]);

            $customer = Customer::findOrFail($validated['customer_id']);
            $outerOrderId = $validated['outer_order_id'] ?? $service->generateOuterOrderId();
            $iccidNumbersByIndex = (array) ($validated['iccid_numbers'] ?? []);

            $orders = DB::transaction(function () use ($validated, $customer, $service, $outerOrderId, $iccidNumbersByIndex) {
                $created = collect();

                foreach ($validated['items'] as $index => $item) {
                    $skuId = $item['sku_id'] ?? $item['sku'] ?? null;
                    if (!$skuId) {
                        continue;
                    }

                    $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 1);
                    $quantity = max(1, $quantity);

                    $unitPrice = $item['unit_price'] ?? $item['ori_price'] ?? null;
                    if ($unitPrice === null || $unitPrice === '') {
                        $submittedTotalPrice = $item['total_price'] ?? $item['price'] ?? null;
                        if ($submittedTotalPrice === null || $submittedTotalPrice === '') {
                            continue;
                        }
                        $unitPrice = $quantity > 0 ? round((float) $submittedTotalPrice / $quantity) : (float) $submittedTotalPrice;
                    }
                    $unitPrice = (int) round((float) $unitPrice);
                    if ($unitPrice <= 0) {
                        continue;
                    }
                    $totalPrice = $unitPrice * $quantity;

                    $daypassDaysRaw = $item['daypass_days'] ?? $item['service_day'] ?? null;
                    $daypassDays = null;
                    if ($daypassDaysRaw !== null && $daypassDaysRaw !== '') {
                        $digits = is_string($daypassDaysRaw) ? preg_replace('/[^0-9]/', '', $daypassDaysRaw) : $daypassDaysRaw;
                        $daypassDays = $digits === '' ? null : (int) $digits;
                    }

                    $iccids = (array) ($iccidNumbersByIndex[$index] ?? []);
                    $iccids = array_values(array_filter(array_map('trim', $iccids), static fn($v) => $v !== ''));

                    $payload = [
                        'customer_id' => $customer->id,
                        'outer_order_id' => $outerOrderId,
                        'sku_id' => (string) $skuId,
                        'price_id' => $item['price_id'] ?? null,
                        'api_code' => $item['api_code'] ?? null,
                        'service_type' => $item['service_type'] ?? 'esim',
                        'order_type' => $item['order_type'] ?? 'new',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'daypass_days' => $daypassDays,
                        'remark' => $item['remark'] ?? null,
                        'source_iccid' => $item['source_iccid'] ?? ($item['iccid'] ?? ($item['iccid_no'] ?? null)),
                        'iccid' => $item['iccid'] ?? ($item['iccid_no'] ?? null),
                        // Roam API optional fields (2.24)
                        'other_item_id' => $item['other_item_id'] ?? (string) $index,
                        'dp_id' => $validated['dp_id'] ?? null,
                        'back_info' => $validated['back_info'] ?? 1,
                        'is_send_email' => (bool) ($validated['is_send_email'] ?? true),
                        'pdf_language' => $validated['pdf_language'] ?? null,
                        'customer_email' => $validated['customer_email'] ?? $customer->email,
                    ];

                    if (!empty($item['country_name']) || !empty($item['service_data'])) {
                        $remarkParts = array_values(array_filter([
                            !empty($item['country_name']) ? 'Country: ' . $item['country_name'] : null,
                            !empty($item['service_data']) ? 'Plan: ' . $item['service_data'] : null,
                        ]));
                        $payload['remark'] = $payload['remark'] ?? (!empty($remarkParts) ? implode(' | ', $remarkParts) : null);
                    }

                    if (!empty($iccids)) {
                        $payload['iccids'] = implode(',', $iccids);
                    }

                    $created->push($service->placeOrder($payload, $customer));
                }

                return $created;
            });

            return response()->json([
                'success' => true,
                'message' => 'Roam orders created successfully.',
                'data' => [
                    'outer_order_id' => $outerOrderId,
                    'orders' => $orders,
                ],
            ], 201);
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'sku_id' => ['required', 'string', 'max:255'],
            'price_id' => ['required', 'integer'],
            'api_code' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(['esim', 'physical'])],
            'order_type' => ['required', Rule::in(['new', 'recharge'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'integer'],
            'total_price' => ['nullable', 'integer'],
            'daypass_days' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'remark' => ['nullable', 'string'],
            'outer_order_id' => ['nullable', 'string', 'max:100'],
            'order_num' => ['nullable', 'string', 'max:100'],
            'main_order_num' => ['nullable', 'string', 'max:100'],
            'source_iccid' => ['nullable', 'string', 'max:50'],
            'iccid' => ['nullable', 'string', 'max:50'],
            'other_item_id' => ['nullable', 'string', 'max:100'],
            'other_price' => ['nullable', 'numeric'],
            'is_send_email' => ['nullable', 'boolean'],
            'pdf_language' => ['nullable', 'string', 'max:10'],
            'back_info' => ['nullable', 'integer', Rule::in([0, 1, 2])],
            'dp_id' => ['nullable', 'string', 'max:50'],
            'iccids' => ['nullable', 'string'],
            'customer_email' => ['nullable', 'email'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $order = $service->placeOrder($validated, $customer);

        return response()->json([
            'success' => true,
            'message' => 'Roam order created successfully.',
            'data' => $order,
        ], 201);
    }

    public function show(Request $request, RoamOrder $roamOrder, RoamOrderService $service)
    {
        if ($request->boolean('refresh')) {
            $roamOrder = $service->syncByOrderNum($roamOrder->roam_order_num);
        } else {
            $roamOrder->load(['customer', 'items']);
        }

        return response()->json([
            'success' => true,
            'data' => $roamOrder,
        ]);
    }

    public function sync(RoamOrder $roamOrder, RoamOrderService $service)
    {
        try {
            $service->syncByOrderNum($roamOrder->roam_order_num);

            return back()->with('success', 'Sync Roam API success.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Sync Roam API failed.');
        }
    }

    public function transition(
        Request $request,
        RoamOrder $roamOrder,
        OrderStateMachineService $stateMachine
    ) {
        $validated = $request->validate([
            'to_status' => ['required', 'integer', Rule::in(array_keys(RoamOrder::OUR_STATUS_LABELS))],
        ]);

        try {
            $order = $stateMachine->transitionRoamOrder($roamOrder, $validated['to_status']);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }

    public function sendPdf(Request $request, RoamOrder $roamOrder, RoamOrderService $service)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'iccids' => ['nullable', 'string'],
            'save_email' => ['nullable', 'boolean'],
            'type' => ['nullable', Rule::in(['email', 'link'])],
        ]);

        $response = $service->sendPdfEmail(
            $roamOrder->roam_order_num,
            $validated['email'],
            $validated['iccids'] ?? null,
            (bool) ($validated['save_email'] ?? false),
            $validated['type'] ?? 'email'
        );

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
