<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\HelpSection;
use App\Models\JoytelOrder;
use App\Models\RoamOrder;
use App\Models\Section;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        return view('frontend.about', compact('banner', 'company', 'about_repay', 'work_section', 'faq_section', 'faqs'));
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
            $activeOrderTab = request()->has('joytel_page') ? 'joytel' : 'roam';
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

    public function roamOrderDetail(?string $outerOrderId = null)
    {
        $customer = auth()->user();
        $roamOrderGroups = $this->customerRoamOrderGroups($customer);

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

    private function customerRoamOrderGroups(Customer $customer): Collection
    {
        return RoamOrder::query()
            ->where('customer_id', $customer->id)
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
        } elseif ($orders->contains(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_CANCELLED)) {
            $statusLabel = 'Cancelled';
            $statusClass = 'text-danger';
        } elseif ($orders->every(fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED)) {
            $statusLabel = 'Completed';
            $statusClass = 'text-success';
        }

        return [
            'provider' => 'Roam',
            'outer_order_id' => $outerOrderId,
            'created_at' => optional($orders->sortByDesc('created_at')->first())->created_at,
            'product_name' => $orders
                ->map(fn(RoamOrder $order) => $this->formatRoamOrderProductName($order))
                ->filter()
                ->unique()
                ->implode("\n"),
            'amount' => $orders->sum(fn(RoamOrder $order) => (float) $order->billable_total_price),
            'payment_method' => 'Online Payment',
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

        return JoytelOrder::query()
            ->with('items')
            ->where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn(JoytelOrder $order) => $this->summarizeJoytelOrder($order))
            ->values();
    }

    private function customerJoytelOrderGroupsPaginated(Customer $customer): LengthAwarePaginator
    {
        return $this->paginateCollection($this->customerJoytelOrderGroups($customer), 10, 'joytel_page');
    }

    private function summarizeJoytelOrder(JoytelOrder $order): array
    {
        $statusMap = [
            0 => ['Draft', 'text-muted'],
            1 => ['Submitted', 'text-primary'],
            2 => ['Processing', 'text-warning'],
            3 => ['Completed', 'text-success'],
            4 => ['Failed', 'text-danger'],
            5 => ['Cancelled', 'text-danger'],
        ];

        [$statusLabel, $statusClass] = $statusMap[(int) $order->status] ?? ['Processing', 'text-primary'];

        $productName = $order->items
            ->map(function ($item) {
                $parts = array_filter([
                    $item->product_name,
                    $item->sale_plan_name,
                    $item->sale_plan_days ? ((int) $item->sale_plan_days . ' day' . ((int) $item->sale_plan_days === 1 ? '' : 's')) : null,
                ]);

                return implode('  ', $parts);
            })
            ->filter()
            ->unique()
            ->implode("\n");

        $amount = $order->items->sum(function ($item) {
            return (float) ($item->line_total ?? $item->total_price ?? 0);
        });

        return [
            'provider' => 'Joytel',
            'order_id' => $order->order_no,
            'created_at' => $order->created_at,
            'product_name' => $productName !== '' ? $productName : ($order->remark ?: '-'),
            'amount' => $amount,
            'payment_method' => Str::headline((string) $order->channel_type),
            'service_type' => Str::headline((string) $order->service_type),
            'status_label' => $order->remote_status_label ?: $statusLabel,
            'status_class' => $statusClass,
        ];
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
}
