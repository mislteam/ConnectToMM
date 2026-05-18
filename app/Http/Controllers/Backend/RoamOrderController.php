<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RoamOrder;
use App\Services\Roam\RoamOrderService;
use App\Services\Roam\OrderStateMachineService;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'sku_id' => ['required', 'string', 'max:255'],
            'price_id' => ['required', 'integer'],
            'api_code' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(['esim', 'physical'])],
            'order_type' => ['required', Rule::in(['new', 'recharge'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric'],
            'total_price' => ['nullable', 'numeric'],
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
        $order = $service->syncByOrderNum($roamOrder->roam_order_num);

        return response()->json([
            'success' => true,
            'message' => 'Roam order synced successfully.',
            'data' => $order,
        ]);
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
