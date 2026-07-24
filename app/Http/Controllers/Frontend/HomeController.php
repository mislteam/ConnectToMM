<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\JoytelOrder;
use App\Models\RoamOrder;
use App\Services\Joytel\JoytelOrderApiService;
use App\Models\Section;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerWallet;
use App\Models\Currency;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function index()
    {
        $help_section = get_section('need_more_help');
        $simple_section = get_section('simple_transparent_secure');
        $action_section = get_section('what_we_do');
        $service_section = get_section('our_services');
        $manage_section = get_section('manage_section');
        $about_repay = get_section('about_connect_to_myanmar');
        return view('frontend.home', compact('help_section', 'simple_section', 'action_section', 'service_section', 'manage_section', 'about_repay'));
    }

    public function about()
    {
        $banner = Banner::where('banner_type', 'about_us')->first();
        $company = get_section('about_company');
        $about_repay = get_section('about_connect_to_myanmar');
        $work_section = get_section('how_we_work');
        $faq_section = get_section('frequently_asked_questions');
        $faqs = Faq::latest()->take(3)->get();
        $blogs = Blog::latest()->take(3)->get();
        return view('frontend.about', compact('banner', 'company', 'about_repay', 'work_section', 'faq_section', 'faqs', 'blogs'));
    }

    public function faq()
    {
        $banner = Banner::where('banner_type', 'faq')->first();
        $faqs = Faq::latest()->get();
        $section = Section::where('section_key', 'need_more_help')->first();
        return view('frontend.faq', compact('banner', 'faqs', 'section'));
    }

    public function blog()
    {
        $banner = Banner::where('banner_type', 'blog')->first();
        $blogs = Blog::latest()->get();
        return view('frontend.blog', compact('banner', 'blogs'));
    }

    public function blogDetail(Blog $blog)
    {
        return view('frontend.blog-detail', compact('blog'));
    }

    public function contact()
    {
        $banner = Banner::where('banner_type', 'contact_us')->first();
        $section = Section::where('section_key', 'need_more_help')->first();
        return view('frontend.contact', compact('banner', 'section'));
    }

    public function customerProfile()
    {
        $banner = Banner::where('banner_type', 'my_account')->first();
        $customer = auth()->user();
        $roamOrderGroups = $this->customerRoamOrderGroupsPaginated($customer);
        $joytelOrderGroups = $this->customerJoytelOrderGroupsPaginated($customer);
        $activeOrderTab = request('orders_tab');

        if (!in_array($activeOrderTab, ['roam', 'joytel'], true)) {
            $activeOrderTab = request()->has('joytel_page') || request()->has('joytel_search') ? 'joytel' : 'roam';
        }

        return view('frontend.user.profile', compact('banner', 'roamOrderGroups', 'joytelOrderGroups', 'activeOrderTab'));
    }

    public function customerEdit(Customer $customer, $edit_type, Request $request)
    {
        if ($edit_type === 'profile') {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'file' => 'nullable|image|mimes:jpeg,jpg,png'
            ]);
            $customer->name = $request->name;
            $customer->email = $request->email;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('profile_images', $fileName, 'public');

                $customer->profile_image = $fileName;
                $customer->save();
            }
            $customer->save();
            return back()->with('success', 'Customer Profile Updated Successfully!');
        } else if ($edit_type === 'password') {
            $request->validate([
                'old_password' => 'required|min:8',
                'new_password' => 'required|min:8|different:old_password',
                'confirm_password' => 'required|same:new_password'
            ]);

            if (!Hash::check($request->old_password, $customer->password)) {
                return back()->withErrors(['old_password' => 'Old password is incorrect.']);
            }

            $customer->password = Hash::make($request->new_password);
            $customer->save();
            auth()->logout();
            return redirect()->route('user.login');
        }
    }

    public function checkJoytelUsage(Request $request, JoytelOrderApiService $joytelApi)
    {
        $validated = $request->validate([
            'outer_order_id' => 'nullable|string',
            'service_type' => 'required|in:esim,physical',
            'sn_pin' => 'nullable|string|max:100',
            'cid' => 'nullable|string|max:128',
            'rsp_order_id' => 'nullable|string|max:100',
            'imsi' => 'nullable|string|max:50',
        ]);

        $customer = auth()->user();
        $orders = JoytelOrder::query()
            ->with('items')
            ->where('customer_id', $customer->id)
            ->when(!empty($validated['outer_order_id']), function ($query) use ($validated) {
                $query->where(function ($orderQuery) use ($validated) {
                    $orderQuery->where('outer_order_id', $validated['outer_order_id'])
                        ->orWhere('joytel_order_num', $validated['outer_order_id']);
                });
            })
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Joytel order not found.',
            ], 404);
        }

        try {
            if ($validated['service_type'] === 'esim') {
                $snPin = trim((string) ($validated['sn_pin'] ?? ''));

                if ($snPin === '') {
                    return response()->json([
                        'ok' => false,
                        'message' => 'SN PIN is required for eSIM usage check.',
                    ], 422);
                }

                $matchedItem = $orders
                    ->flatMap(fn(JoytelOrder $order) => $order->items)
                    ->first(fn($item) => hash_equals((string) $item->sn_pin, $snPin));

                if (!$matchedItem) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'This SN PIN does not belong to your Joytel order.',
                    ], 422);
                }

                $result = $joytelApi->queryEsimUsage($snPin);
            } else {
                $cid = trim((string) ($validated['cid'] ?? ''));
                $rspOrderId = trim((string) ($validated['rsp_order_id'] ?? ''));

                if ($cid === '' || $rspOrderId === '') {
                    return response()->json([
                        'ok' => false,
                        'message' => 'SN Code / CID and RSP Order ID are required for recharge usage check.',
                    ], 422);
                }

                $matchedItem = $orders
                    ->flatMap(fn(JoytelOrder $order) => $order->items)
                    ->first(function ($item) use ($cid, $rspOrderId) {
                        $storedRspOrderId = (string) data_get($item->raw_callback_data, 'joytel_query_order.sn.rspOrderId', '');

                        return hash_equals((string) ($item->cid ?: $item->sn_code), $cid)
                            && ($storedRspOrderId === '' || hash_equals($storedRspOrderId, $rspOrderId));
                    });

                if (!$matchedItem) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'These recharge details do not belong to your Joytel order.',
                    ], 422);
                }

                $result = $joytelApi->querySimUsage($cid, $rspOrderId, (string) ($validated['imsi'] ?? ''));
            }

            $matchedOrder = $orders->first(fn(JoytelOrder $order) => $order->items->contains('id', $matchedItem->id));

            $this->storeJoytelUsageData($matchedItem, (array) $result['data']);

            return response()->json([
                'ok' => true,
                'message' => 'Joytel usage query success.',
                'summary' => $this->summarizeJoytelUsageData((array) $result['data'], $matchedItem, $matchedOrder),
                'raw' => $this->formatJoytelUsageValue((array) $result['data']),
            ]);
        } catch (\Throwable $e) {
            Log::warning('JOYTEL_CUSTOMER_USAGE_CHECK_FAILED', [
                'customer_id' => $customer->id,
                'outer_order_id' => $validated['outer_order_id'] ?? null,
                'service_type' => $validated['service_type'],
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function roamOrderDetail(?string $outerOrderId = null)
    {
        return $this->orderDetail($outerOrderId);
    }


    public function orderDetail(?string $outerOrderId = null)
    {
        $customer = auth()->user();
        $roamOrderGroups = $this->customerRoamOrderGroups($customer);
        $joytelOrderGroups = $this->customerJoytelOrderGroups($customer);

        if ($outerOrderId && Schema::hasTable('joytel_orders')) {
            $hasJoytelOrder = JoytelOrder::query()
                ->where('customer_id', $customer->id)
                ->where('outer_order_id', $outerOrderId)
                ->exists();

            if ($hasJoytelOrder) {
                return $this->joytelOrderDetail($customer, $outerOrderId, $joytelOrderGroups);
            }
        }

        if (!$outerOrderId && session('joytel_last_outer_order_id')) {
            return $this->joytelOrderDetail($customer, session('joytel_last_outer_order_id'), $joytelOrderGroups);
        }

        $outerOrderId = $outerOrderId
            ?: session('roam_last_outer_order_id')
            ?: data_get($roamOrderGroups->first(), 'outer_order_id');

        $orders = collect();
        if ($outerOrderId) {
            $orders = RoamOrder::query()
                ->with('items')
                ->where('customer_id', $customer->id)
                ->where('outer_order_id', $outerOrderId)
                ->orderBy('id')
                ->get();
        }

        if ($orders->isEmpty() && $roamOrderGroups->isNotEmpty()) {
            $outerOrderId = data_get($roamOrderGroups->first(), 'outer_order_id');
            $orders = RoamOrder::query()
                ->with('items')
                ->where('customer_id', $customer->id)
                ->where('outer_order_id', $outerOrderId)
                ->orderBy('id')
                ->get();
        }

        $orders->transform(function (RoamOrder $order) {
            $order->formatted_product_name = $this->formatRoamOrderProductName($order);

            return $order;
        });

        $summary = $orders->isNotEmpty()
            ? $this->summarizeRoamOrderGroup($outerOrderId, $orders)
            : [
                'outer_order_id' => $outerOrderId,
                'amount' => 0,
                'status_label' => 'No Orders',
                'status_class' => 'text-muted',
                'can_pay' => false,
            ];

        return view('frontend.user.roam-order-detail', [
            'provider' => 'roam',
            'provider_order_no_label' => 'Roam Order No',
            'payment_route' => !empty($outerOrderId) ? route('roam.payment.show', ['outerOrderId' => $outerOrderId]) : null,
            'outer_order_id' => $outerOrderId,
            'orders' => $orders,
            'total' => $summary['amount'],
            'created_at' => $summary['created_at'] ?? null,
            'payment_method' => $summary['payment_method'] ?? null,
            'status_label' => $summary['status_label'],
            'status_class' => $summary['status_class'],
            'can_pay' => $summary['can_pay'],
            'roamOrderGroups' => $roamOrderGroups,
        ]);
    }

    private function joytelOrderDetail(Customer $customer, ?string $outerOrderId, Collection $joytelOrderGroups)
    {
        $outerOrderId = $outerOrderId
            ?: session('joytel_last_outer_order_id')
            ?: data_get($joytelOrderGroups->first(), 'outer_order_id');

        $orders = collect();
        if ($outerOrderId && Schema::hasTable('joytel_orders')) {
            $orders = JoytelOrder::query()
                ->with('items')
                ->where('customer_id', $customer->id)
                ->where('outer_order_id', $outerOrderId)
                ->orderBy('id')
                ->get();
        }

        if ($orders->isEmpty() && $joytelOrderGroups->isNotEmpty()) {
            $outerOrderId = data_get($joytelOrderGroups->first(), 'outer_order_id');
            $orders = JoytelOrder::query()
                ->with('items')
                ->where('customer_id', $customer->id)
                ->where('outer_order_id', $outerOrderId)
                ->orderBy('id')
                ->get();
        }

        $orders->transform(function (JoytelOrder $order) {
            $order->formatted_product_name = $this->formatJoytelOrderProductName($order);

            return $order;
        });

        $summary = $orders->isNotEmpty()
            ? $this->summarizeJoytelOrderGroup($outerOrderId, $orders)
            : [
                'outer_order_id' => $outerOrderId,
                'amount' => 0,
                'status_label' => 'No Orders',
                'status_class' => 'text-muted',
                'can_pay' => false,
            ];

        return view('frontend.user.joytel-order-detail', [
            'provider' => 'joytel',
            'provider_order_no_label' => 'Joytel Order No',
            'payment_route' => !empty($outerOrderId) ? route('joytel.payment.show', ['outerOrderId' => $outerOrderId]) : null,
            'outer_order_id' => $outerOrderId,
            'orders' => $orders,
            'total' => $summary['amount'],
            'created_at' => $summary['created_at'] ?? null,
            'payment_method' => $summary['payment_method'] ?? null,
            'status_label' => $summary['status_label'],
            'status_class' => $summary['status_class'],
            'can_pay' => $summary['can_pay'],
            'roamOrderGroups' => collect(),
        ]);
    }

    public function joytelOrderDetailPage(?string $outerOrderId = null)
    {
        $customer = auth()->user();

        return $this->joytelOrderDetail($customer, $outerOrderId, $this->customerJoytelOrderGroups($customer));
    }

    private function customerRoamOrderGroups(Customer $customer): Collection
    {
        $search = trim((string) request('search'));

        return RoamOrder::query()
            ->where('customer_id', $customer->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($orderQuery) use ($search) {
                    $orderQuery->where('outer_order_id', 'like', "%{$search}%")
                        ->orWhere('roam_order_num', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->groupBy(fn(RoamOrder $order) => $order->outer_order_id ?: $order->roam_order_num)
            ->map(fn(Collection $orders, string $outerOrderId) => $this->summarizeRoamOrderGroup($outerOrderId, $orders))
            ->values();
    }

    private function customerRoamOrderGroupsPaginated(Customer $customer): LengthAwarePaginator
    {
        return $this->paginateCollection($this->customerRoamOrderGroups($customer), 10, 'roam_page');
    }

    private function summarizeRoamOrderGroup(string $outerOrderId, Collection $orders): array
    {
        $statusLabel = 'Processing';
        $statusClass = 'text-primary';

        if ($orders->every(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_REFUNDED)) {
            $statusLabel = 'Refunded';
            $statusClass = 'text-info';
        } elseif ($orders->contains(
            fn(RoamOrder $order) =>
            (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED &&
                (int) $order->roam_status === RoamOrder::ROAM_STATUS_CANCELLED
        )) {
            $statusLabel = 'Refunded';
            $statusClass = 'text-info';
        } elseif ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            $statusLabel = 'Pending Payment';
            $statusClass = 'text-warning';
        } elseif ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_API_FAILED)) {
            $statusLabel = 'Failed';
            $statusClass = 'text-danger';
        } elseif ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_ADMIN_CANCELLED)) {
            $statusLabel = 'Admin Cancel';
            $statusClass = 'text-danger';
        } elseif ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_CANCELLED)) {
            $statusLabel = 'Cancelled';
            $statusClass = 'text-danger';
        } elseif ($orders->every(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED)) {
            $statusLabel = 'Completed';
            $statusClass = 'text-success';
        }

        $serviceItems = $orders
            ->map(function (RoamOrder $order) {
                return [
                    'service_type' => Str::headline((string) $order->service_type),
                    'order_type' => Str::headline((string) $order->order_type),
                ];
            })
            ->filter(fn(array $item) => $item['service_type'] !== '' || $item['order_type'] !== '')
            ->unique(fn(array $item) => $item['service_type'] . '|' . $item['order_type'])
            ->values();

        return [
            'provider' => 'Roam',
            'outer_order_id' => $outerOrderId,
            'created_at' => optional($orders->sortByDesc('created_at')->first())->created_at,
            'product_name' => $orders
                ->map(fn(RoamOrder $order) => $this->formatRoamOrderProductName($order))
                ->filter()
                ->unique()
                ->implode("\n"),
            'service_items' => $serviceItems,
            'service_summary' => $serviceItems
                ->map(fn(array $item) => trim($item['service_type'] . ' ' . $item['order_type']))
                ->implode(' '),
            'amount' => $orders->sum(fn(RoamOrder $order) => (float) $order->billable_total_price),
            'payment_method' => $orders
                ->map(fn(RoamOrder $order) => payment_method_display_label($order->payment_method, $outerOrderId))
                ->filter()
                ->unique()
                ->implode(', '),
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'can_pay' => $orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT),
        ];
    }

    private function customerJoytelOrderGroups(Customer $customer): Collection
    {
        if (!Schema::hasTable('joytel_orders')) {
            return collect();
        }

        $search = trim((string) request('joytel_search'));

        return JoytelOrder::query()
            ->with('items')
            ->where('customer_id', $customer->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($orderQuery) use ($search) {
                    $orderQuery->where('outer_order_id', 'like', "%{$search}%")
                        ->orWhere('joytel_order_num', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->groupBy(fn(JoytelOrder $order) => $order->outer_order_id ?: $order->joytel_order_num)
            ->map(fn(Collection $orders, string $outerOrderId) => $this->summarizeJoytelOrderGroup($outerOrderId, $orders))
            ->values();
    }

    private function customerJoytelOrderGroupsPaginated(Customer $customer): LengthAwarePaginator
    {
        return $this->paginateCollection($this->customerJoytelOrderGroups($customer), 10, 'joytel_page');
    }

    private function summarizeJoytelOrderGroup(string $outerOrderId, Collection $orders): array
    {
        $statusLabel = 'Processing';
        $statusClass = 'text-primary';

        if ($orders->every(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_REFUNDED)) {
            $statusLabel = 'Refunded';
            $statusClass = 'text-info';
        } elseif ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT)) {
            $statusLabel = 'Pending Payment';
            $statusClass = 'text-warning';
        } elseif ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_API_FAILED)) {
            $statusLabel = 'Failed';
            $statusClass = 'text-danger';
        } elseif ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_ADMIN_CANCELLED)) {
            $statusLabel = 'Admin Cancel';
            $statusClass = 'text-danger';
        } elseif ($orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_CANCELLED)) {
            $statusLabel = 'Cancelled';
            $statusClass = 'text-danger';
        } elseif ($orders->every(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_COMPLETED)) {
            $statusLabel = 'Completed';
            $statusClass = 'text-success';
        }

        $productName = $orders
            ->map(fn(JoytelOrder $order) => $this->joytelProductLabel($order))
            ->filter()
            ->unique()
            ->implode("\n");

        $usageCheckItems = $this->joytelUsageCheckItems($orders);

        return [
            'provider' => 'Joytel',
            'order_id' => $outerOrderId,
            'outer_order_id' => $outerOrderId,
            'created_at' => optional($orders->sortByDesc('created_at')->first())->created_at,
            'product_name' => $productName !== '' ? $productName : '-',
            'amount' => $orders->sum(fn(JoytelOrder $order) => (float) $order->billable_total_price),
            'payment_method' => $orders
                ->map(fn(JoytelOrder $order) => payment_method_display_label($order->payment_method, $outerOrderId))
                ->filter()
                ->unique()
                ->implode(', '),
            'service_type' => Str::headline((string) optional($orders->first())->service_type),
            'order_type' => $orders->first()->order_type,
            'usage_check_items' => $usageCheckItems,
            'usage_check_items_json' => json_encode($usageCheckItems),
            'usage_service_type' => strtolower((string) data_get($usageCheckItems, '0.service_type', optional($orders->first())->service_type ?: 'esim')),
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'can_pay' => $orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT),
        ];
    }

    private function joytelUsageCheckItems(Collection $orders): array
    {
        return $orders
            ->flatMap(function (JoytelOrder $order) {
                return $order->items->map(function ($item) use ($order) {
                    return [
                        'service_type' => strtolower((string) $order->service_type),
                        'product_name' => $this->joytelProductLabel($order),
                        'product_code' => $item->product_code,
                        'sn_pin' => $item->sn_pin,
                        'cid' => $item->cid ?: $item->sn_code,
                        'sn_code' => $item->sn_code,
                        'rsp_order_id' => data_get($item->raw_callback_data, 'joytel_query_order.sn.rspOrderId'),
                    ];
                });
            })
            ->values()
            ->all();
    }

    private function summarizeJoytelUsageData(array $data, $item = null, ?JoytelOrder $order = null): array
    {
        $usageList = collect((array) (
            data_get($data, 'dataUsageList')
            ?: data_get($data, 'usageList')
            ?: data_get($data, 'list')
            ?: []
        ));

        $totalUsage = data_get($data, 'totalUsage');
        if ($totalUsage === null) {
            $totalUsage = $usageList->sum(fn($row) => is_numeric(data_get($row, 'usage')) ? (float) data_get($row, 'usage') : 0);
        }

        $effectiveDate = $this->resolveJoytelUsageEffectiveDate($data, $item);
        $expiryDate = $this->resolveJoytelUsageExpiryDate($data, $item);
        // $durationDays = data_get($data, 'duration')
        //     ?: data_get($data, 'days')
        //     ?: $item?->sale_plan_days
        //     ?: $order?->validity_days;

        $durationDays = data_get($data, 'duration')
            ?: data_get($data, 'days');


        $summary = [
            '__joytelUsageSummary' => true,
            'title' => $order ? $this->joytelProductLabel($order) : 'Total Usage',
            'rows' => [
                [
                    'label' => 'Total Data Balance',
                    'value' => $this->bytesToMb((float) $totalUsage),
                ],
                [
                    'label' => 'Duration',
                    // 'value' => $durationDays ? ((int) $durationDays . ' days') : '-',
                    'value' => ($effectiveDate && $expiryDate) ? $this->durationLabel($effectiveDate, $expiryDate) : '-',
                ],
                [
                    'label' => 'Remaining Duration',
                    'value' => $expiryDate ? $this->remainingDaysLabel($expiryDate) : '-',
                ],
                [
                    'label' => 'Expired Date',
                    'value' => $expiryDate ? $expiryDate->format('d-M-Y') : '-',
                ],
            ],
            'usage_records' => $usageList
                ->map(function ($row) {
                    $usage = data_get($row, 'usage');

                    return [
                        'Usage Date' => $this->formatJoytelUsageDate(data_get($row, 'usageDate')),
                        'MCC' => data_get($row, 'mcc') ?: '-',
                        'Usage' => is_numeric($usage) ? $this->bytesToMb((float) $usage) : '-',
                    ];
                })
                ->values()
                ->all(),
        ];

        return [$summary];
    }

    private function storeJoytelUsageData($item, array $data): void
    {
        $usedBytes = $this->firstNumericJoytelUsageValue($data, [
            'usedBytes',
            'used_bytes',
            'used',
            'usageBytes',
            'usage',
            'dataUsage',
            'dataUsageList.0.usedBytes',
            'dataUsageList.0.used',
            'dataUsageList.0.usageBytes',
            'dataUsageList.0.usage',
        ]);
        $totalUsageBytes = $this->firstNumericJoytelUsageValue($data, [
            'totalUsageBytes',
            'total_usage_bytes',
            'totalBytes',
            'total',
            'dataTotal',
            'dataUsageList.0.totalUsageBytes',
            'dataUsageList.0.totalBytes',
            'dataUsageList.0.total',
        ]);

        $item->forceFill([
            'raw_usage_data' => $data,
            'used_bytes' => $usedBytes ?? $item->used_bytes,
            'total_usage_bytes' => $totalUsageBytes ?? $item->total_usage_bytes,
        ])->save();
    }

    private function firstNumericJoytelUsageValue(array $data, array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = data_get($data, $key);

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function formatJoytelUsageValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $formatted = [];

            foreach ($value as $key => $item) {
                $formatted[$key] = $this->looksLikeMillisecondTimeField((string) $key) && is_numeric($item)
                    ? $this->formatJoytelMillisecondTime($item)
                    : $this->formatJoytelUsageValue($item);

                if (is_numeric($item) && $this->looksLikeByteField((string) $key)) {
                    $formatted[$key . '_mb'] = $this->bytesToMb((float) $item);
                }
            }

            return $formatted;
        }

        return $value;
    }

    private function looksLikeMillisecondTimeField(string $key): bool
    {
        $key = strtolower($key);

        return str_ends_with($key, 'time')
            || str_contains($key, 'efftime')
            || str_contains($key, 'exptime');
    }

    private function formatJoytelMillisecondTime(mixed $timestamp): string
    {
        if (!is_numeric($timestamp)) {
            return (string) $timestamp;
        }

        return date('Y-m-d H:i:s', (int) floor(((int) $timestamp) / 1000));
    }

    private function resolveJoytelUsageEffectiveDate(array $data, $item = null): ?\Carbon\Carbon
    {
        $effTime = data_get($data, 'effTime');

        if (is_numeric($effTime)) {
            return \Carbon\Carbon::createFromTimestampMs((int) $effTime);
        }

        return null;
    }

    private function resolveJoytelUsageExpiryDate(array $data, $item = null): ?\Carbon\Carbon
    {


        $expTime = data_get($data, 'expTime');

        if (is_numeric($expTime)) {
            return \Carbon\Carbon::createFromTimestampMs((int) $expTime);
        }

        // if ($item?->expiration_time) {
        //     return $item->expiration_time instanceof \Carbon\Carbon
        //         ? $item->expiration_time
        //         : \Carbon\Carbon::parse($item->expiration_time);
        // }

        // if ($item?->product_expire_date) {
        //     return $item->product_expire_date instanceof \Carbon\Carbon
        //         ? $item->product_expire_date
        //         : \Carbon\Carbon::parse($item->product_expire_date);
        // }

        return null;
    }

    private function durationLabel(\Carbon\Carbon $effectiveDate, \Carbon\Carbon $expiryDate): string
    {
        $durationDays = $effectiveDate
            ->copy()
            ->startOfDay()
            ->diffInDays($expiryDate->copy()->startOfDay());

        return $durationDays . ' days';
    }

    private function remainingDaysLabel(\Carbon\Carbon $expiryDate): string
    {
        $remainingDays = max(0, now()->startOfDay()->diffInDays($expiryDate->copy()->startOfDay(), false));

        return $remainingDays . ' days';
    }

    private function formatJoytelUsageDate(mixed $date): string
    {
        $date = trim((string) $date);

        if ($date === '') {
            return '-';
        }

        if (preg_match('/^\d{8}$/', $date)) {
            return \Carbon\Carbon::createFromFormat('Ymd', $date)->format('d-M-Y');
        }

        return $date;
    }

    private function looksLikeByteField(string $key): bool
    {
        $key = strtolower($key);

        return str_contains($key, 'byte')
            || str_contains($key, 'usage')
            || str_contains($key, 'used')
            || str_contains($key, 'total');
    }

    private function bytesToMb(float $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 2) . ' MB';
    }

    private function formatJoytelOrderProductName(JoytelOrder $order): string
    {
        $name = trim((string) ($order->product_name ?: $order->remark ?: $order->items->pluck('product_code')->filter()->first()));
        $meta = [];
        $data = $this->resolveJoytelOrderProductData($order);

        if ($data !== '') {
            $meta[] = $data;
        }

        if (!empty($order->validity_days)) {
            $days = (int) $order->validity_days;
            $meta[] = $days . ' ' . ($days === 1 ? 'day' : 'days');
        }

        return trim($name . (!empty($meta) ? ' - ' . implode(' - ', $meta) : ''));
    }

    private function joytelProductLabel(JoytelOrder $order): string
    {
        $label = trim((string) ($order->formatted_product_name ?? ''));

        return $label !== '' ? $label : $this->formatJoytelOrderProductName($order);
    }

    private function resolveJoytelOrderProductData(JoytelOrder $order): string
    {
        $data = data_get($order->raw_response, 'cart_item.service_data')
            ?? data_get($order->raw_response, 'cart_item.data')
            ?? data_get($order->raw_response, 'request_payload.serviceData')
            ?? data_get($order->raw_response, 'request_payload.data');

        return is_string($data) ? trim($data) : '';
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $pageItems = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }

    private function formatRoamOrderProductName(RoamOrder $order): string
    {
        $productName = trim((string) ($order->remark ?: $order->sku_id));

        $parts = $productName !== '' ? (preg_split('/\s*\|\s*/', $productName) ?: []) : [];
        $parts = array_map(function (string $part): string {
            return preg_replace('/^(Country|Plan):\s*/i', '', trim($part)) ?? trim($part);
        }, $parts);
        $parts = array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));

        if (!empty($order->daypass_days)) {
            $parts[] = (int) $order->daypass_days . ' ' . ((int) $order->daypass_days === 1 ? 'day' : 'days');
        }

        return !empty($parts) ? implode('  ', $parts) : $productName;
    }

    public function refundsPolicy()
    {
        $content = Section::where('section_key', 'refunds_policy')->first();
        $description = preg_replace('/font-family\s*:[^;"]+;?/i', '', $content->description);
        return view('frontend.refunds-policy', compact('content', 'description'));
    }

    public function customerWallet(Request $request)
    {
        $request->validate([
            'transaction_date' => ['nullable', 'date'],
        ]);

        $customer = auth()->user();
        $wallet = $customer?->customerWallet;

        $transactions = $wallet
            ? $wallet
                ->walletTransactions()
                ->when($request->filled('transaction_date'), function ($query) use ($request) {
                    $query->whereDate('created_at', $request->input('transaction_date'));
                })
                ->latest()
                ->paginate(10, ['*'], 'transaction_page')
                ->withQueryString()
            : new LengthAwarePaginator(
                [],
                0,
                10,
                $request->integer('transaction_page', 1),
                ['path' => $request->url()]
            );

        return view('frontend.user.wallet', compact('customer', 'transactions'));
    }

    public function customerTopUp(Request $request)
    {
        Auth::shouldUse('customers');
        $validated = $request->validate([
            'amount' => 'required|integer|min:1000'
        ]);
        $customer = auth()->user();

        $transaction = DB::transaction(function () use ($customer, $validated) {
            $wallet = CustomerWallet::firstOrCreate([
                'customer_id' => $customer->id,
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $validated['amount'],
                'type' => 'credit',
                'reference_type' => 'topup',
                'balance_after' => 0,
                'transaction_state' => WalletTransaction::STATUS_PENDING
            ]);
        });

        return redirect()->route('frontend.user.topup-payment', $transaction->id);
    }

    public function customerTopUpPayment(WalletTransaction $transaction)
    {
        $credentials = \App\Models\DirectBankCredential::all();
        return view('frontend.user.topup-payment', compact('transaction', 'credentials'));
    }

    public function topupPaymentSlip(Request $request, WalletTransaction $transaction)
    {
        Auth::shouldUse('customers');

        if ($transaction->transaction_state !== 'pending') {
            return back()->with('error', 'This top-up has already been processed.');
        }

        $request->validate([
            'payment_slip' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if ($transaction->payment_slip) {
            Storage::disk('public')->delete($transaction->payment_slip);
        }

        $path = $request->file('payment_slip')->store(
            'topup-payment-slips',
            'public'
        );

        $transaction->update([
            'payment_slip' => $path,
        ]);

        return back()->with(
            'success',
            'Payment slip sent to the admin successfully!'
        );
    }

    public function topupDetail(WalletTransaction $transaction)
    {
        return view('frontend.user.topup-detail', compact('transaction'));
    }

    public function currencyChange(Request $request)
    {
        $validated = $request->validate([
            'currency' => ['required', Rule::in(config('currency.supported'))]
        ]);
        session([
            'currency' => $validated['currency']
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'currency' => $validated['currency'],
                'usd_rate' => Currency::where('name', 'user_usd_rate')->value('value'),
            ]);
        }

        return back();
    }
}
