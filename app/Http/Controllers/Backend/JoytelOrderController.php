<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\JoytelEsim;
use App\Models\JoytelOrder;
use App\Models\JoytelPhysical;
use App\Services\OrderNotificationService;
use App\Services\Joytel\JoytelOrderApiService;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JoytelOrderController extends Controller
{
    public function index(Request $request)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $joytel_title = GeneralSetting::where('name', 'joytel_title')->first();
        $roam_title = GeneralSetting::where('name', 'roam_title')->first();

        $matchingReferences = $this->filteredOrdersQuery($request)
            ->latest()
            ->get()
            ->groupBy(fn(JoytelOrder $order) => $this->orderReference($order))
            ->keys();

        $groupedOrders = $this->ordersByReferences($matchingReferences)
            ->groupBy(fn(JoytelOrder $order) => $this->orderReference($order))
            ->map(fn(Collection $orders, string $reference) => $this->summarizeOrderGroup($reference, $orders))
            ->when($request->filled('payment_status'), function (Collection $summaries) use ($request) {
                $status = strtolower((string) $request->input('payment_status'));
                $allowedStatuses = ['pending_payment', 'failed', 'refunded', 'cancelled', 'admin_cancelled', 'completed', 'new', 'processing'];

                if (!in_array($status, $allowedStatuses, true)) {
                    return $summaries;
                }

                return $summaries->where('status_key', $status);
            })
            ->sortByDesc('created_at')
            ->values();

        $perPage = $request->integer('per_page', 20);
        $orders = $this->paginateCollection($groupedOrders, $perPage, $request);
        $stats = $this->buildStats();

        return view('admin.order.joytel-list', compact('joytel_title','roam_title','logo', 'title', 'orders', 'stats'));
    }

    public function show(string $reference)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $joytel_title = GeneralSetting::where('name', 'joytel_title')->first();
        $roam_title = GeneralSetting::where('name', 'roam_title')->first();

        $orders = $this->ordersByReference($reference);

        abort_if($orders->isEmpty(), 404);

        $orders->transform(function (JoytelOrder $order) {
            $order->formatted_product_name = $this->formatOrderProductName($order);
            return $order;
        });
       

        $summary = $this->summarizeOrderGroup($reference, $orders);

        return view('admin.order.joytel-order-view', compact('joytel_title','roam_title','logo', 'title', 'orders', 'summary'));
    }

    public function approvePayment(
        JoytelOrder $joytelOrder,
        JoytelOrderApiService $joytelApi,
        OrderNotificationService $notifications
    )
    {
        $reference = $joytelOrder->outer_order_id ?: $joytelOrder->joytel_order_num;

        if (!$reference) {
            return back()->with('error', 'Order reference is missing for payment approval.');
        }

        $pendingOrders = JoytelOrder::query()
            ->where('customer_id', $joytelOrder->customer_id)
            ->where(function ($query) use ($reference) {
                $query->where('outer_order_id', $reference)
                    ->orWhere('joytel_order_num', $reference);
            })
            ->orderBy('id')
            ->get();

        if ($pendingOrders->isEmpty()) {
            return back()->with('error', 'No Joytel orders were found for this payment approval.');
        }

        if ($pendingOrders->every(fn(JoytelOrder $order) => (int) $order->our_status !== JoytelOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return back()->with('error', 'This Joytel order reference is no longer waiting for payment approval.');
        }

        $preservedPayment = (array) data_get($pendingOrders->first()?->raw_response, 'payment', []);
        $paymentSlipPath = data_get($preservedPayment, 'slip.path');
        if (!$paymentSlipPath) {
            return back()->with('error', 'Please wait for the customer to upload a JPG or PNG payment slip before approving payment.');
        }

        $approvedBy = auth()->user();
        $completedOrders = collect();
        $failedOrders = collect();
       

        $pendingOrders
            ->filter(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT)
            ->each(function (JoytelOrder $order) use ($approvedBy, $preservedPayment, $joytelApi, $completedOrders, $failedOrders) {
                $rawResponse = (array) ($order->raw_response ?? []);
                $payment = array_replace_recursive($preservedPayment, (array) data_get($rawResponse, 'payment', []));
                $payment['approval'] = [
                    'source' => 'admin_manual_approval',
                    'approved_by' => $approvedBy?->name ?? 'Admin',
                    'approved_at' => now()->toDateTimeString(),
                ];

                $rawResponse['payment'] = $payment;
                $order->raw_response = $rawResponse;
                $order->our_status = JoytelOrder::OUR_STATUS_API_PROCESSING;
                $order->save();

                try {
                    $completedOrders->push($this->submitOrderToJoytel($order, $joytelApi));
                } catch (\Throwable $e) {
                    $failedOrders->push($this->markJoytelSubmitFailed($order, $e));
                }
            });

        $completedOrders
            ->filter(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_COMPLETED)
            ->each(fn(JoytelOrder $order) => $notifications->paymentApproved($order));

        if ($failedOrders->isNotEmpty()) {
            return back()->with('error', 'Payment approved, but one or more Joytel API orders failed. Please review the order status.');
        }

        $message = $completedOrders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_API_PROCESSING)
            ? 'Payment approved. Joytel order is processing; sync it again after Joytel marks it Delivered.'
            : 'Joytel payment approved and order completed successfully.';

        return back()->with('success', $message);
    }

    public function retryJoytelApi(
        JoytelOrder $joytelOrder,
        JoytelOrderApiService $joytelApi,
        OrderNotificationService $notifications
    ) {
        if (!in_array((int) $joytelOrder->our_status, [
            JoytelOrder::OUR_STATUS_PAID,
            JoytelOrder::OUR_STATUS_API_PROCESSING,
            JoytelOrder::OUR_STATUS_API_FAILED,
        ], true)) {
            return back()->with('error', 'This Joytel order is not ready for API retry.');
        }

        if (!str_starts_with((string) $joytelOrder->joytel_order_num, 'JTMP-')) {
            return back()->with('error', 'This Joytel order already has a Joytel order number.');
        }

        $joytelOrder->forceFill([
            'our_status' => JoytelOrder::OUR_STATUS_API_PROCESSING,
        ])->save();

        try {
            $completedOrder = $this->submitOrderToJoytel($joytelOrder, $joytelApi);
        } catch (\Throwable $e) {
            $this->markJoytelSubmitFailed($joytelOrder, $e);

            return back()->with('error', 'Retry Joytel API failed. Please review the order status.');
        }

        if ((int) $completedOrder->our_status === JoytelOrder::OUR_STATUS_COMPLETED) {
            $notifications->paymentApproved($completedOrder);
        }

        return back()->with('success', (int) $completedOrder->our_status === JoytelOrder::OUR_STATUS_COMPLETED
            ? 'Retry Joytel API success. Joytel order completed.'
            : 'Joytel order submitted and is waiting for delivery.');
    }

    public function syncJoytelItems(
        JoytelOrder $joytelOrder,
        JoytelOrderApiService $joytelApi,
        OrderNotificationService $notifications
    )
    {
        if (str_starts_with((string) $joytelOrder->joytel_order_num, 'JTMP-')) {
            return back()->with('error', 'Submit the Joytel order before syncing items.');
        }

        $orderTid = (string) (
            data_get($joytelOrder->raw_response, 'joytel_create_order.response.data.orderTid')
            ?: $joytelOrder->outer_order_id
        );
        $orderCode = (string) $joytelOrder->joytel_order_num;

        try {
            $queryResult = $this->isRechargeOrder($joytelOrder)
                ? $joytelApi->queryRechargeOrder($orderTid, $orderCode)
                : $joytelApi->queryOrder($orderTid, $orderCode);
            $rawResponse = (array) ($joytelOrder->raw_response ?? []);
            $rawResponse['joytel_query_order'] = [
                'request' => $queryResult['payload'],
                'response' => $queryResult['response'],
                'queried_at' => now()->toDateTimeString(),
            ];

            $joytelOrder->forceFill([
                'joytel_status' => is_numeric($queryResult['joytel_status'] ?? null)
                    ? (int) $queryResult['joytel_status']
                    : $joytelOrder->joytel_status,
                'raw_response' => $rawResponse,
            ])->save();

            $createResult = [
                'order_tid' => $orderTid,
                'order_code' => $orderCode,
                'response' => data_get($joytelOrder->raw_response, 'joytel_create_order.response', []),
            ];

            $this->storeJoytelOrderItems($joytelOrder, $createResult, $queryResult);
            $wasCompleted = (int) $joytelOrder->our_status === JoytelOrder::OUR_STATUS_COMPLETED;
            $hadIncompleteEsimDetails = $this->hasIncompleteEsimDetails($joytelOrder->fresh(['items']));
            $joytelOrder = $this->completeProvisioningWhenReady($joytelOrder, $joytelApi, $queryResult);

            if (
                (int) $joytelOrder->our_status === JoytelOrder::OUR_STATUS_COMPLETED
                && (!$wasCompleted || $hadIncompleteEsimDetails)
            ) {
                $notifications->paymentApproved($joytelOrder);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Sync Joytel items failed: ' . $e->getMessage());
        }

        return back()->with('success', (int) $joytelOrder->our_status === JoytelOrder::OUR_STATUS_COMPLETED
            ? 'Joytel eSIM details synced and customer email sent.'
            : 'Joytel order is not Delivered yet. It remains Processing.');
    }

    public function cancelPayment(JoytelOrder $joytelOrder, OrderNotificationService $notifications)
    {
        $reference = $joytelOrder->outer_order_id ?: $joytelOrder->joytel_order_num;

        if (!$reference) {
            return back()->with('error', 'Order reference is missing for cancellation.');
        }

        $pendingOrders = JoytelOrder::query()
            ->where('customer_id', $joytelOrder->customer_id)
            ->where(function ($query) use ($reference) {
                $query->where('outer_order_id', $reference)
                    ->orWhere('joytel_order_num', $reference);
            })
            ->where('our_status', JoytelOrder::OUR_STATUS_PENDING_PAYMENT)
            ->orderBy('id')
            ->get();

        if ($pendingOrders->isEmpty()) {
            return back()->with('error', 'This Joytel order reference is no longer waiting for admin cancellation.');
        }

        $cancelledBy = auth()->user();

        $pendingOrders->each(function (JoytelOrder $order) use ($cancelledBy) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $rawResponse['admin_cancel'] = [
                'cancelled_by' => $cancelledBy?->name ?? 'Admin',
                'cancelled_at' => now()->toDateTimeString(),
            ];

            $order->raw_response = $rawResponse;
            $order->our_status = JoytelOrder::OUR_STATUS_ADMIN_CANCELLED;
            $order->save();
        });

        $notifications->adminCancelled($pendingOrders->first()->refresh());

        return back()->with('success', 'Joytel order cancelled by admin successfully.');
    }

    private function submitOrderToJoytel(JoytelOrder $order, JoytelOrderApiService $joytelApi): JoytelOrder
    {
        $result = $joytelApi->createOrder($order->fresh(['customer', 'items']));
        $rawResponse = (array) ($order->raw_response ?? []);
        $rawResponse['joytel_create_order'] = [
            'request' => $result['payload'],
            'response' => $result['response'],
            'submitted_at' => now()->toDateTimeString(),
        ];

        $orderCode = (string) ($result['order_code'] ?: $order->joytel_order_num);

        $order->forceFill([
            'joytel_order_num' => $orderCode,
            'joytel_status' => is_numeric($result['joytel_status']) ? (int) $result['joytel_status'] : null,
            'our_status' => JoytelOrder::OUR_STATUS_API_PROCESSING,
            'purchase_date' => now(),
            'is_send_email' => false,
            'raw_response' => $rawResponse,
        ])->save();

        try {
            $queryResult = ($result['api_type'] ?? null) === 'recharge_order'
                ? $joytelApi->queryRechargeOrder((string) $result['order_tid'], (string) $result['order_code'])
                : $joytelApi->queryOrder((string) $result['order_tid'], (string) $result['order_code']);
            $rawResponse = (array) ($order->raw_response ?? []);
            $rawResponse['joytel_query_order'] = [
                'request' => $queryResult['payload'],
                'response' => $queryResult['response'],
                'queried_at' => now()->toDateTimeString(),
            ];

            $order->forceFill([
                'joytel_status' => is_numeric($queryResult['joytel_status'] ?? null)
                    ? (int) $queryResult['joytel_status']
                    : $order->joytel_status,
                'raw_response' => $rawResponse,
            ])->save();

            $this->storeJoytelOrderItems($order, $result, $queryResult);
            $order = $this->completeProvisioningWhenReady($order, $joytelApi, $queryResult);
        } catch (\Throwable $e) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $rawResponse['joytel_query_order_error'] = [
                'message' => $e->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ];
            $order->forceFill(['raw_response' => $rawResponse])->save();

            Log::warning('JOYTEL_QUERY_ORDER_AFTER_CREATE_FAILED', [
                'joytel_order_id' => $order->id,
                'order_code' => $orderCode,
                'message' => $e->getMessage(),
            ]);
        }

        return $order->fresh(['customer', 'items']);
    }

    private function completeProvisioningWhenReady(
        JoytelOrder $order,
        JoytelOrderApiService $joytelApi,
        array $queryResult
    ): JoytelOrder {
        if ($this->isRechargeOrder($order)) {
            $isSuccessful = (int) ($queryResult['joytel_status'] ?? -1) === 1;

            $order->forceFill([
                'our_status' => $isSuccessful
                    ? JoytelOrder::OUR_STATUS_COMPLETED
                    : JoytelOrder::OUR_STATUS_API_PROCESSING,
                'is_send_email' => $isSuccessful,
            ])->save();

            return $order->fresh(['customer', 'items']);
        }

        if ((int) ($queryResult['joytel_status'] ?? -1) !== 4) {
            $order->forceFill([
                'our_status' => JoytelOrder::OUR_STATUS_API_PROCESSING,
                'is_send_email' => false,
            ])->save();

            return $order->fresh(['customer', 'items']);
        }

        foreach ($order->items()->whereNotNull('sn_pin')->get() as $item) {
            if (!$this->hasIncompleteProvisioningDetails($item)) {
                continue;
            }

            $callbackData = (array) ($item->raw_callback_data ?? []);
            $transactionId = (string) data_get($callbackData, 'joytel_coupon_redeem.transaction_id', '');

            if ($transactionId === '') {
                $redeemResult = $joytelApi->redeemCoupon((string) $item->sn_pin, 1);
                $transactionId = (string) $redeemResult['transaction_id'];
                $callbackData['joytel_coupon_redeem'] = $redeemResult;
                $item->forceFill(['raw_callback_data' => $callbackData])->save();
            }

            $transactionResult = (array) data_get($callbackData, 'joytel_transaction_status', []);
            $provisioning = (array) (
                data_get($transactionResult, 'data')
                ?: data_get($transactionResult, 'response.data')
                ?: []
            );

            if (empty($provisioning)) {
                $transactionResult = $joytelApi->getTransactionStatus($transactionId);
                $provisioning = (array) $transactionResult['data'];
            }

            $qrContent = trim((string) data_get($provisioning, 'qrcode', ''));

            if ($qrContent === '') {
                throw new \RuntimeException('Joytel transaction completed without QR activation content.');
            }

            $qrPath = $this->storePlainQrCode($order, $item->id, $qrContent);
            $callbackData = (array) ($item->fresh()->raw_callback_data ?? []);
            $callbackData['joytel_transaction_status'] = $transactionResult;
            $callbackData['plain_qr_path'] = $qrPath;

            $item->forceFill([
                'sn_pin' => $this->firstProvisioningValue($provisioning, ['coupon'], $item->sn_pin),
                'cid' => $this->firstProvisioningValue($provisioning, ['cid'], $item->cid),
                'qrcode_type' => is_numeric(data_get($provisioning, 'qrcodeType'))
                    ? (int) data_get($provisioning, 'qrcodeType')
                    : ($item->qrcode_type ?: 1),
                'qrcode' => $qrContent ?: $item->qrcode,
                'pin1' => $this->firstProvisioningValue($provisioning, ['pin1', 'pin_1'], $item->pin1),
                'pin2' => $this->firstProvisioningValue($provisioning, ['pin2', 'pin_2'], $item->pin2),
                'puk1' => $this->firstProvisioningValue($provisioning, ['puk1', 'puk_1'], $item->puk1),
                'puk2' => $this->firstProvisioningValue($provisioning, ['puk2', 'puk_2'], $item->puk2),
                'sale_plan_name' => $this->firstProvisioningValue($provisioning, ['salePlanName', 'sale_plan_name'], $item->sale_plan_name),
                'sale_plan_days' => $this->firstProvisioningValue($provisioning, ['salePlanDays', 'sale_plan_days'], $item->sale_plan_days),
                'raw_callback_data' => $callbackData,
            ])->save();
        }

        $isReady = $order->items()->exists()
            && !$order->items()->get()->contains(fn($item) => !$item->sn_code || !$item->sn_pin || !$item->qrcode);

        $order->forceFill([
            'our_status' => $isReady
                ? JoytelOrder::OUR_STATUS_COMPLETED
                : JoytelOrder::OUR_STATUS_API_PROCESSING,
            'is_send_email' => $isReady,
        ])->save();

        return $order->fresh(['customer', 'items']);
    }

    private function storePlainQrCode(JoytelOrder $order, int $itemId, string $content): string
    {
        $qrCode = QrCode::create($content)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->setSize(360)
            ->setMargin(16);
        $png = (new PngWriter())->write($qrCode)->getString();
        $path = 'joytel-qrcodes/' . $order->id . '-' . $itemId . '.png';

        Storage::disk('local')->put($path, $png);

        return $path;
    }

    private function hasIncompleteEsimDetails(JoytelOrder $order): bool
    {
        if (strtolower((string) $order->service_type) !== 'esim') {
            return false;
        }

        return $order->items->contains(fn($item) => $this->hasIncompleteProvisioningDetails($item));
    }

    private function hasIncompleteProvisioningDetails($item): bool
    {
        return empty($item->sn_code)
            || empty($item->sn_pin)
            || empty($item->cid)
            || empty($item->qrcode)
            || $item->pin1 === null
            || $item->pin2 === null
            || $item->puk1 === null
            || $item->puk2 === null;
    }

    private function firstProvisioningValue(array $provisioning, array $keys, mixed $fallback = null): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($provisioning, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    private function storeJoytelOrderItems(JoytelOrder $order, array $createResult, array $queryResult): void
    {
        $snRows = collect((array) ($queryResult['item_list'] ?? []))
            ->flatMap(function ($item) {
                $productCode = data_get($item, 'productCode');
                $snList = collect((array) data_get($item, 'snList', []));

                if ($snList->isEmpty() && (data_get($item, 'snCode') || data_get($item, 'rspOrderId'))) {
                    return [[
                        'productCode' => $productCode,
                        'snCode' => data_get($item, 'snCode'),
                        'status' => data_get($item, 'status'),
                        'statusDesc' => data_get($item, 'statusDesc'),
                        'productExpireDate' => data_get($item, 'productExpireDate'),
                        'rspOrderId' => data_get($item, 'rspOrderId'),
                        'rspTid' => data_get($item, 'rspTid'),
                    ]];
                }

                return $snList
                    ->map(function ($sn) use ($productCode) {
                        $sn['productCode'] = data_get($sn, 'productCode') ?: $productCode;

                        return $sn;
                    });
            })
            ->values();

        if ($snRows->isEmpty()) {
            $order->items()->each(function ($item) use ($createResult, $queryResult) {
                $callbackData = (array) ($item->raw_callback_data ?? []);
                $callbackData['joytel_create_order'] = [
                    'orderTid' => $createResult['order_tid'],
                    'orderCode' => $createResult['order_code'],
                    'response' => $createResult['response'],
                ];
                $callbackData['joytel_query_order'] = [
                    'orderTid' => $queryResult['order_tid'],
                    'orderCode' => $queryResult['order_code'],
                    'response' => $queryResult['response'],
                ];

                $item->raw_callback_data = $callbackData;
                $item->save();
            });

            return;
        }

        foreach ($snRows as $index => $sn) {
            $item = $order->items()->skip($index)->first() ?: $order->items()->create([
                'product_code' => data_get($sn, 'productCode') ?: $order->items()->value('product_code'),
            ]);
            $callbackData = (array) ($item->raw_callback_data ?? []);
            $callbackData['joytel_create_order'] = [
                'orderTid' => $createResult['order_tid'],
                'orderCode' => $createResult['order_code'],
                'response' => $createResult['response'],
            ];
            $callbackData['joytel_query_order'] = [
                'orderTid' => $queryResult['order_tid'],
                'orderCode' => $queryResult['order_code'],
                'response' => $queryResult['response'],
                'sn' => $sn,
            ];

            $item->forceFill([
                'product_code' => data_get($sn, 'productCode') ?: $item->product_code,
                'sn_code' => data_get($sn, 'snCode') ?: data_get($sn, 'sn_code') ?: $item->sn_code,
                'sn_pin' => data_get($sn, 'snPin') ?: data_get($sn, 'sn_pin') ?: $item->sn_pin,
                'product_expire_date' => data_get($sn, 'productExpireDate') ?: $item->product_expire_date,
                'esim_status' => is_numeric(data_get($sn, 'status')) ? (int) data_get($sn, 'status') : $item->esim_status,
                'raw_callback_data' => $callbackData,
            ])->save();
        }
    }

    private function markJoytelSubmitFailed(JoytelOrder $order, \Throwable $e): JoytelOrder
    {
        $rawResponse = (array) ($order->raw_response ?? []);
        $rawResponse['joytel_create_order_error'] = [
            'message' => $e->getMessage(),
            'failed_at' => now()->toDateTimeString(),
        ];

        $order->forceFill([
            'our_status' => JoytelOrder::OUR_STATUS_API_FAILED,
            'raw_response' => $rawResponse,
        ])->save();

        Log::error('JOYTEL_CREATE_ORDER_FAILED', [
            'joytel_order_id' => $order->id,
            'message' => $e->getMessage(),
        ]);

        return $order->fresh(['customer', 'items']);
    }

    private function filteredOrdersQuery(Request $request)
    {
        return JoytelOrder::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->where('outer_order_id', 'like', '%' . $keyword . '%')
                        ->orWhere('joytel_order_num', 'like', '%' . $keyword . '%')
                        ->orWhere('product_name', 'like', '%' . $keyword . '%');
                });
            })
            ->when($request->filled('date_range'), function ($query) use ($request) {
                $range = strtolower((string) $request->input('date_range'));
                $now = Carbon::now();

                return match ($range) {
                    'today' => $query->whereDate('created_at', $now->toDateString()),
                    'last_7_days' => $query->where('created_at', '>=', $now->copy()->subDays(7)->startOfDay()),
                    'last_30_days' => $query->where('created_at', '>=', $now->copy()->subDays(30)->startOfDay()),
                    'this_year' => $query->whereYear('created_at', $now->year),
                    default => $query,
                };
            })
            ->when($request->filled('our_status'), fn($q) => $q->where('our_status', $request->integer('our_status')))
            ->when($request->filled('service_type'), fn($q) => $q->where('service_type', $request->input('service_type')));
    }

    private function buildStats(): array
    {
        $summaries = JoytelOrder::query()
            ->with(['customer', 'items'])
            ->latest()
            ->get()
            ->groupBy(fn(JoytelOrder $order) => $this->orderReference($order))
            ->map(fn(Collection $orders, string $reference) => $this->summarizeOrderGroup($reference, $orders));

        return [
            'completed' => $summaries->where('status_key', 'completed')->count(),
            'pending_payment' => $summaries->where('status_key', 'pending_payment')->count(),
            'cancelled' => $summaries->where('status_key', 'cancelled')->count(),
            'admin_cancelled' => $summaries->where('status_key', 'admin_cancelled')->count(),
            'refunded' => $summaries->where('status_key', 'refunded')->count(),
            'failed' => $summaries->where('status_key', 'failed')->count(),
        ];
    }

    private function summarizeOrderGroup(string $reference, Collection $orders): array
    {
        $sortedOrders = $orders->sortByDesc('created_at')->values();
        $latestOrder = $sortedOrders->first();
        $primaryOrder = $orders->firstWhere('our_status', JoytelOrder::OUR_STATUS_PENDING_PAYMENT)
            ?? $sortedOrders->first();

        [$statusKey, $statusLabel, $statusClass] = $this->resolveGroupStatus($orders);
        [$paymentLabel, $paymentClass] = $this->resolveGroupPayment($orders);

        $productNames = $orders
            ->map(fn(JoytelOrder $order) => $this->formatOrderProductName($order))
            ->filter()
            ->unique()
            ->values();


        return [
            'reference' => $reference,
            'primary_order' => $primaryOrder,
            'created_at' => $latestOrder?->created_at,
            'customer_name' => $latestOrder?->customer?->name ?? data_get($latestOrder?->raw_response, 'billing.name', '-'),
            'customer_email' => $latestOrder?->customer?->email ?? data_get($latestOrder?->raw_response, 'billing.email', '-'),
            'customer_phone' => data_get($latestOrder?->raw_response, 'billing.phone', $latestOrder?->customer?->phone ?? '-'),
            'product_names' => $productNames,
            'product_summary' => $productNames->implode("\n"),
            'item_count' => $orders->sum(fn(JoytelOrder $order) => max(1, (int) $order->quantity)),
            'amount' => $orders->sum(fn(JoytelOrder $order) => (float) $order->billable_total_price),
            'payment_label' => $paymentLabel,
            'payment_class' => $paymentClass,
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'payment_slip_path' => $this->extractPaymentSlipPath($orders),
            'orders' => $sortedOrders,
        ];
    }

    private function resolveGroupStatus(Collection $orders): array
    {
        if ($orders->every(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_REFUNDED)) {
            return ['refunded', 'Refunded', 'text-info'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return ['pending_payment', 'Pending Payment', 'text-warning'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_API_FAILED)) {
            return ['failed', 'Failed', 'text-danger'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_ADMIN_CANCELLED)) {
            return ['admin_cancelled', 'Admin Cancel', 'text-danger'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_CANCELLED)) {
            return ['cancelled', 'Order Cancelled', 'text-danger'];
        }

        if ($orders->every(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_COMPLETED)) {
            return ['completed', 'Completed', 'text-success'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_ORDER_START)) {
            return ['new', 'Order Started', 'text-info'];
        }

        return ['processing', 'Provisioning in Progress', 'text-primary'];
    }

    private function resolveGroupPayment(Collection $orders): array
    {
        if ($orders->every(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_REFUNDED)) {
            return ['Refunded', 'text-info'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return ['Pending', 'text-warning'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_ADMIN_CANCELLED)) {
            return ['Admin Cancel', 'text-danger'];
        }

        if ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_CANCELLED)) {
            return ['Cancelled', 'text-danger'];
        }

        return ['Paid', 'text-success'];
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function ordersByReference(string $reference): Collection
    {
        return JoytelOrder::query()
            ->with(['customer', 'items'])
            ->where(function ($query) use ($reference) {
                $query->where('outer_order_id', $reference)
                    ->orWhere(function ($fallbackQuery) use ($reference) {
                        $fallbackQuery->where(function ($blankReferenceQuery) {
                            $blankReferenceQuery->whereNull('outer_order_id')
                                ->orWhere('outer_order_id', '');
                        })
                            ->where('joytel_order_num', $reference);
                    });
            })
            ->orderBy('id')
            ->get();
    }

    private function ordersByReferences(Collection $references): Collection
    {
        if ($references->isEmpty()) {
            return collect();
        }

        return JoytelOrder::query()
            ->with(['customer', 'items'])
            ->where(function ($query) use ($references) {
                foreach ($references as $reference) {
                    $query->orWhere(function ($referenceQuery) use ($reference) {
                        $referenceQuery->where('outer_order_id', $reference)
                            ->orWhere(function ($fallbackQuery) use ($reference) {
                                $fallbackQuery->where(function ($blankReferenceQuery) {
                                    $blankReferenceQuery->whereNull('outer_order_id')
                                        ->orWhere('outer_order_id', '');
                                })
                                    ->where('joytel_order_num', $reference);
                            });
                    });
                }
            })
            ->latest()
            ->get();
    }

    private function extractPaymentSlipPath(Collection $orders): ?string
    {
        foreach ($orders as $order) {
            $path = data_get($order->raw_response, 'payment.slip.path');

            if (is_string($path) && $path !== '') {
                return $path;
            }
        }

        return null;
    }

    private function orderReference(JoytelOrder $order): string
    {
        return $order->outer_order_id ?: $order->joytel_order_num;
    }

    private function isRechargeOrder(JoytelOrder $order): bool
    {
        return strtolower((string) $order->service_type) === 'physical'
            && strtolower((string) $order->order_type) === 'recharge';
    }

    // private function formatOrderProductName(JoytelOrder $order): string
    // {
    //     $name = trim((string) ($order->product_name ?: $order->remark));
    //     $data = $this->resolveOrderProductData($order);
    //     $days = (int) ($order->validity_days ?? 0);

    //     if ($data !== '' && !str_contains(strtolower($name), strtolower($data))) {
    //         $name = trim($name . ' ' . $data);
    //     }

    //     if ($days > 0 && !str_contains(strtolower($name), strtolower($days . ' day'))) {
    //         $name = trim($name . ' ' . $days . ' ' . ($days === 1 ? 'day' : 'days'));
    //     }

    //     return $name !== '' ? $name : (string) $order->joytel_order_num;
    // }

    private function formatOrderProductName(JoytelOrder $order): array
    {
        $name = trim((string) ($order->product_name ?: $order->remark));
        $data = trim((string) $this->resolveOrderProductData($order));
        $days = (int) ($order->validity_days ?? 0);

        $meta = [];

        if ($data !== '') {
            $meta[] = $data;
        }

        if ($days > 0) {
            $meta[] = $days . ' ' . ($days === 1 ? 'day' : 'days');
        }

        return [
            'name' => $name !== '' ? $name : (string) $order->joytel_order_num,
            'meta' => implode(' • ', $meta),
        ];
    }

    private function resolveOrderProductData(JoytelOrder $order): string
    {
        $cartItemData = data_get($order->raw_response, 'cart_item.service_data')
            ?? data_get($order->raw_response, 'cart_item.data')
            ?? data_get($order->raw_response, 'request_payload.serviceData')
            ?? data_get($order->raw_response, 'request_payload.data');

        if (is_string($cartItemData) && trim($cartItemData) !== '') {
            return trim($cartItemData);
        }

        $productCode = $order->items->pluck('product_code')->filter()->first()
            ?? data_get($order->raw_response, 'cart_item.product_code')
            ?? data_get($order->raw_response, 'request_payload.productCode');

        if (!$productCode && !$order->product_name) {
            return '';
        }

        $productQuery = strtolower((string) $order->service_type) === 'physical'
            ? JoytelPhysical::query()
            : JoytelEsim::query();

        $productData = $productQuery
            ->where(function ($query) use ($productCode, $order) {
                if ($productCode) {
                    $query->where('code', $productCode);
                }

                if ($order->product_name) {
                    $method = $productCode ? 'orWhere' : 'where';
                    $query->{$method}('product_name', $order->product_name);
                }
            })
            ->value('data');

        return is_string($productData) ? trim($productData) : '';
    }
}
