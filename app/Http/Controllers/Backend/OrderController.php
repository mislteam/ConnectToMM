<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\RoamOrder;
use App\Services\Roam\OrderStateMachineService;
use App\Services\Roam\RoamOrderService;
use App\Services\Roam\RoamProvisioningFlowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        $matchingReferences = $this->filteredOrdersQuery($request)
            ->latest()
            ->get()
            ->groupBy(fn(RoamOrder $order) => $this->orderReference($order))
            ->keys();

        $groupedOrders = $this->ordersByReferences($matchingReferences)
            ->groupBy(fn(RoamOrder $order) => $this->orderReference($order))
            ->map(fn(Collection $orders, string $reference) => $this->summarizeOrderGroup($reference, $orders))
            ->when($request->filled('payment_status'), function (Collection $summaries) use ($request) {
                $status = strtolower((string) $request->input('payment_status'));
                $allowedStatuses = ['pending_payment', 'failed', 'refunded', 'cancelled', 'completed', 'new', 'processing'];

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

        return view('admin.order.roam-list', compact('logo', 'title', 'orders', 'stats'));
    }

    public function show(string $reference)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        $orders = $this->ordersByReference($reference);

        abort_if($orders->isEmpty(), 404);

        $orders->transform(function (RoamOrder $order) {
            $order->formatted_product_name = $this->formatRoamOrderProductName($order);

            return $order;
        });

        $summary = $this->summarizeOrderGroup($reference, $orders);

        return view('admin.order.roam-order-view', compact('logo', 'title', 'orders', 'summary'));
    }

    public function joytelIndex()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view('admin.order.joytel-list', compact('logo', 'title'));
    }

    public function approvePayment(RoamOrder $roamOrder, RoamProvisioningFlowService $provisioning)
    {
        $reference = $roamOrder->outer_order_id ?: $roamOrder->roam_order_num;

        if (!$reference) {
            return back()->with('error', 'Order reference is missing for payment approval.');
        }

        $pendingOrders = RoamOrder::query()
            ->where('customer_id', $roamOrder->customer_id)
            ->where(function ($query) use ($reference) {
                $query->where('outer_order_id', $reference)
                    ->orWhere('roam_order_num', $reference);
            })
            ->orderBy('id')
            ->get();

        if ($pendingOrders->isEmpty()) {
            return back()->with('error', 'No orders were found for this payment approval.');
        }

        if ($pendingOrders->every(fn(RoamOrder $order) => (int) $order->our_status !== RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return back()->with('error', 'This order reference is no longer waiting for payment approval.');
        }

        $preservedPayment = (array) data_get($pendingOrders->first()?->raw_response, 'payment', []);
        $paymentSlipPath = data_get($preservedPayment, 'slip.path');
        if (!$paymentSlipPath) {
            return back()->with('error', 'Please wait for the customer to upload a JPG or PNG payment slip before approving payment.');
        }

        try {
            $approvedBy = auth()->user();
            $orders = $provisioning->provisionAfterPayment($roamOrder->customer, $reference);

            $orders->each(function (RoamOrder $order) use ($approvedBy, $preservedPayment) {
                $rawResponse = (array) ($order->raw_response ?? []);
                $payment = array_replace_recursive($preservedPayment, (array) data_get($rawResponse, 'payment', []));
                $payment['approval'] = [
                    'source' => 'admin_manual_approval',
                    'approved_by' => $approvedBy?->name ?? 'Admin',
                    'approved_at' => now()->toDateTimeString(),
                ];
                $rawResponse['payment'] = $payment;
                $order->raw_response = $rawResponse;
                $order->save();
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $completedCount = $orders->filter(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED
        )->count();

        $failedCount = $orders->filter(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_API_FAILED
        )->count();

        $totalCount = $orders->count();

        if ($completedCount === $totalCount) {
            $message = "Payment approved successfully. {$completedCount}/{$totalCount} order(s) were provisioned.";
        } elseif ($completedCount > 0) {
            $message = "Payment approved with partial provisioning. {$completedCount}/{$totalCount} order(s) completed and {$failedCount} failed.";
        } else {
            $message = 'Payment approved, but provisioning did not complete successfully. Please review the order status.';
        }

        return back()->with('success', $message);
    }

    public function retryRoamApi(RoamOrder $roamOrder, RoamProvisioningFlowService $provisioning)
    {
        $reference = $roamOrder->outer_order_id ?: $roamOrder->roam_order_num;

        if (!$reference) {
            return back()->with('error', 'Order reference is missing for Roam API retry.');
        }

        $failedOrders = RoamOrder::query()
            ->where('customer_id', $roamOrder->customer_id)
            ->where(function ($query) use ($reference) {
                $query->where('outer_order_id', $reference)
                    ->orWhere('roam_order_num', $reference);
            })
            ->where('our_status', RoamOrder::OUR_STATUS_API_FAILED)
            ->orderBy('id')
            ->get();

        if ($failedOrders->isEmpty()) {
            return back()->with('error', 'This order reference does not have any failed Roam API orders to retry.');
        }

        try {
            $orders = $provisioning->retryFailedAfterPayment($roamOrder->customer, $reference);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $completedCount = $orders->filter(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED
        )->count();

        $failedCount = $orders->filter(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_API_FAILED
        )->count();

        $totalCount = $orders->count();

        if ($completedCount === $totalCount) {
            return back()->with('success', "Retry Roam API success. {$completedCount}/{$totalCount} failed order(s) completed.");
        }

        if ($completedCount > 0) {
            return back()->with('success', "Retry Roam API partially succeeded. {$completedCount}/{$totalCount} failed order(s) completed and {$failedCount} still failed.");
        }

        return back()->with('error', 'Retry Roam API failed. Please review the order and try again later.');
    }

    public function refund(
        Request $request,
        RoamOrder $roamOrder,
        OrderStateMachineService $stateMachine,
        RoamOrderService $roamOrderService
    ) {
        $validated = $request->validate([
            'refund_method' => ['required', Rule::in([
                RoamOrder::REFUND_METHOD_INTERNAL_PAYMENT,
                RoamOrder::REFUND_METHOD_ROAM_API,
            ])],
        ]);

        try {
            if ($validated['refund_method'] === RoamOrder::REFUND_METHOD_ROAM_API) {
                if ((int) $roamOrder->our_status === RoamOrder::OUR_STATUS_REFUNDED) {
                    return back()->with('error', 'This order is already refunded.');
                }

                if ((int) $roamOrder->our_status !== RoamOrder::OUR_STATUS_COMPLETED) {
                    return back()->with('error', 'Roam-side refund is only available after Roam API success.');
                }

                if ((int) $roamOrder->roam_status !== RoamOrder::ROAM_STATUS_NORMAL) {
                    return back()->with('error', 'Roam-side refund is only available when Roam status is Normal / Paid.');
                }

                if (!$roamOrder->purchase_date) {
                    return back()->with('error', 'Purchase date is required before requesting a Roam-side refund.');
                }

                if ($roamOrder->purchase_date->copy()->addDays(90)->lt(Carbon::now())) {
                    return back()->with('error', 'Roam-side refund is only available within 90 days of purchase date.');
                }

                if (data_get($roamOrder->raw_response, 'refund.roam_api.response')) {
                    return back()->with('error', 'Roam-side refund was already requested for this order.');
                }

                $refundedOrder = $roamOrderService->refundOrder(
                    $roamOrder,
                    'Admin requested Roam-side refund within 90 days of purchase.'
                );

                return back()->with('success', 'Roam portal refund completed successfully.');
            }

            if ((int) $roamOrder->our_status === RoamOrder::OUR_STATUS_REFUNDED) {
                return back()->with('error', 'This order is already refunded.');
            }

            $canRefundApiFailedOrder = (int) $roamOrder->our_status === RoamOrder::OUR_STATUS_API_FAILED;
            $canRefundRoamRefundedOrder =
                (int) $roamOrder->our_status === RoamOrder::OUR_STATUS_COMPLETED &&
                (
                    (bool) data_get($roamOrder->raw_response, 'refund.roam_api.response') ||
                    (int) $roamOrder->roam_status === RoamOrder::ROAM_STATUS_CANCELLED
                );

            if (!$canRefundApiFailedOrder && !$canRefundRoamRefundedOrder) {
                return back()->with('error', 'Payment refund is only available after Roam API failure, or after a successful Roam-side refund.');
            }

            $rawResponse = (array) ($roamOrder->raw_response ?? []);
            $refund = (array) data_get($rawResponse, 'refund', []);
            $roamRefundAmount = data_get($refund, 'roam_api.amount');
            $refundAmount = is_numeric($roamRefundAmount)
                ? (float) $roamRefundAmount
                : (float) $roamOrder->billable_total_price;

            $refund['internal_payment'] = [
                'method' => RoamOrder::REFUND_METHOD_INTERNAL_PAYMENT,
                'amount' => $refundAmount,
                'response' => [
                    'source' => 'admin_internal_payment',
                    'message' => 'Payment refund marked by admin.',
                    'amount' => $refundAmount,
                ],
            ];
            $refund['method'] = $canRefundRoamRefundedOrder
                ? RoamOrder::REFUND_METHOD_ROAM_API
                : RoamOrder::REFUND_METHOD_INTERNAL_PAYMENT;
            $refund['amount'] = $refundAmount;
            $rawResponse['refund'] = $refund;

            $stateMachine->transitionRoamOrder($roamOrder, RoamOrder::OUR_STATUS_REFUNDED, [
                'raw_response' => $rawResponse,
            ]);

            return back()->with('success', 'Customer payment refund marked successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function filteredOrdersQuery(Request $request)
    {
        return RoamOrder::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->where('outer_order_id', 'like', '%' . $keyword . '%')
                        ->orWhere('roam_order_num', 'like', '%' . $keyword . '%');
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
        $summaries = RoamOrder::query()
            ->with(['customer', 'items'])
            ->latest()
            ->get()
            ->groupBy(fn(RoamOrder $order) => $this->orderReference($order))
            ->map(fn(Collection $orders, string $reference) => $this->summarizeOrderGroup($reference, $orders));

        return [
            'completed' => $summaries->where('status_key', 'completed')->count(),
            'pending_payment' => $summaries->where('status_key', 'pending_payment')->count(),
            'cancelled' => $summaries->where('status_key', 'cancelled')->count(),
            'new' => $summaries->where('status_key', 'new')->count(),
            'failed' => $summaries->where('status_key', 'failed')->count(),
        ];
    }

    private function summarizeOrderGroup(string $reference, Collection $orders): array
    {
        $sortedOrders = $orders->sortByDesc('created_at')->values();
        /** @var RoamOrder $latestOrder */
        $latestOrder = $sortedOrders->first();
        /** @var RoamOrder|null $primaryOrder */
        $primaryOrder = $orders->firstWhere('our_status', RoamOrder::OUR_STATUS_PENDING_PAYMENT)
            ?? $sortedOrders->first();

        [$statusKey, $statusLabel, $statusClass] = $this->resolveGroupStatus($orders);
        [$paymentLabel, $paymentClass] = $this->resolveGroupPayment($orders);

        $productNames = $orders
            ->map(fn(RoamOrder $order) => $this->formatRoamOrderProductName($order))
            ->filter()
            ->unique()
            ->values();
        $hasRoamRefund = $orders->contains(
            fn(RoamOrder $order) =>
            (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED &&
                (int) $order->roam_status === RoamOrder::ROAM_STATUS_CANCELLED
        );

        return [
            'reference' => $reference,
            'primary_order' => $primaryOrder,
            'retry_order' => $orders->firstWhere('our_status', RoamOrder::OUR_STATUS_API_FAILED),
            'created_at' => $latestOrder?->created_at,
            'customer_name' => $latestOrder?->customer?->name ?? '-',
            'customer_email' => $latestOrder?->customer?->email ?? '-',
            'product_names' => $productNames,
            'product_summary' => $productNames->implode(', '),
            'item_count' => $orders->count(),
            'amount' => $orders->sum(fn(RoamOrder $order) => (float) $order->billable_total_price),
            'payment_label' => $paymentLabel,
            'payment_class' => $paymentClass,
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'has_roam_refund' => $hasRoamRefund,
            'payment_slip_path' => $this->extractPaymentSlipPath($orders),
            'orders' => $sortedOrders,
        ];
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

    private function resolveGroupStatus(Collection $orders): array
    {
        if ($orders->every(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_REFUNDED)) {
            return ['refunded', 'Refunded', 'text-info'];
        }

        if ($orders->contains(
            fn(RoamOrder $order) =>
            (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED &&
                (int) $order->roam_status === RoamOrder::ROAM_STATUS_CANCELLED
        )) {
            return ['refunded', 'Partially Refunded', 'text-info'];
        }

        if ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return ['pending_payment', 'Pending Payment', 'text-warning'];
        }

        if ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_API_FAILED)) {
            return ['failed', 'Failed', 'text-danger'];
        }

        if ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_CANCELLED)) {
            return ['cancelled', 'Order Cancelled', 'text-danger'];
        }

        if ($orders->every(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED)) {
            return ['completed', 'Completed', 'text-success'];
        }

        if ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_ORDER_START)) {
            return ['new', 'Order Started', 'text-info'];
        }

        return ['processing', 'Provisioning in Progress', 'text-primary'];
    }

    private function resolveGroupPayment(Collection $orders): array
    {
        if ($orders->every(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_REFUNDED)) {
            return ['Refunded', 'text-info'];
        }

        if ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return ['Pending', 'text-warning'];
        }

        if ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_CANCELLED)) {
            return ['Cancelled', 'text-danger'];
        }

        return ['Paid', 'text-success'];
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function ordersByReference(string $reference): Collection
    {
        return RoamOrder::query()
            ->with(['customer', 'items'])
            ->where(function ($query) use ($reference) {
                $query->where('outer_order_id', $reference)
                    ->orWhere(function ($fallbackQuery) use ($reference) {
                        $fallbackQuery->where(function ($blankReferenceQuery) {
                            $blankReferenceQuery->whereNull('outer_order_id')
                                ->orWhere('outer_order_id', '');
                        })
                            ->where('roam_order_num', $reference);
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

        return RoamOrder::query()
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
                                    ->where('roam_order_num', $reference);
                            });
                    });
                }
            })
            ->latest()
            ->get();
    }

    private function orderReference(RoamOrder $order): string
    {
        return $order->outer_order_id ?: $order->roam_order_num;
    }

    private function formatRoamOrderProductName(RoamOrder $order): string
    {
        $productName = trim((string) ($order->remark ?: $order->sku_id));

        $parts = $productName !== '' ? (preg_split('/\s*\|\s*/', $productName) ?: []) : [];
        $parts = array_map(function (string $part): string {
            return preg_replace('/^(Country|Plan):\s*/i', '', trim($part)) ?? trim($part);
        }, $parts);

        $parts = array_values(array_filter($parts, fn(string $part) => $part !== ''));

        if (!empty($order->daypass_days)) {
            $parts[] = (int) $order->daypass_days . ' ' . ((int) $order->daypass_days === 1 ? 'day' : 'days');
        }

        return !empty($parts) ? implode('  ', $parts) : $productName;
    }
}
