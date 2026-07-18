<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JoytelOrder;
use App\Models\JoytelEsim;
use App\Models\JoytelPhysical;
use App\Models\JoyUsageLocation;
use App\Models\PriceList;
use App\Models\Section;
use App\Services\Joytel\JoytelOrderDraftService;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FrontendJoytelController extends Controller
{
    public function esimIndex()
    {
        return $this->esimSearchPage();
    }

    public function physicalIndex(Request $request)
    {
        return $this->physicalSearchPage($request);
    }

    private function esimSearchPage()
    {
        $usage_locations = JoyUsageLocation::where('status', 1)->pluck('location')->values();
        $section = Section::where('section_key', 'need_more_help')->first();

        $query = JoytelEsim::whereRaw('LOWER(type) LIKE ?', ['%esim%']);

        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });

        $orderTabs = getOrderTypes('joytel_order_types', 'esim');
        $selectedSimType = collect($orderTabs)->keys()->first();

        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        return view('frontend.joytel.esim.search', compact(
            'usage_locations',
            'packages',
            'section'
        ));
    }

    private function physicalSearchPage($request)
    {
        $usage_locations = JoyUsageLocation::where('status', 1)->pluck('location')->values();
        $section = Section::where('section_key', 'need_more_help')->first();

        $query = JoytelPhysical::whereRaw('LOWER(type) LIKE ?', ['%recharge%']);


        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });



        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        $orderTabs = getOrderTypes('joytel_order_types', 'physical');
        $selectedSimType = collect($orderTabs)->keys()->first();

        return view('frontend.joytel.physical.search', compact(
            'usage_locations',
            'packages',
            'section'
        ));
    }

    public function esimSearch(Request $request)
    {
        return $this->esimPackages($request);
    }

    public function physicalSearch(Request $request)
    {
        return $this->physicalPackages($request);
    }

    private function esimPackages(Request $request)
    {
        $simType = $this->normalizeSimType($request->input('type', session('sim_type', 'new_esim')));
        if (!in_array($simType, ['new_esim', 'recharge_esim'], true)) {
            $simType = 'new_esim';
        }

        session(['sim_type' => $simType]);

        $validated = $request->validate([
            'locations' => 'required|array',
            'locations.*' => 'string'
        ]);

        $query = JoytelEsim::whereRaw('LOWER(type) LIKE ?', ['%esim%']);

        foreach ($validated['locations'] as $location) {
            $query->where(function ($q) use ($location) {
                $q->whereJsonContains('coverage', $location)
                    ->orWhereRaw("JSON_SEARCH(coverage, 'one', ?) IS NOT NULL", [$location . '%']);
            });
        }

        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });

        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        return view('frontend.joytel.esim.packages', compact('packages'));
    }

    private function physicalPackages(Request $request)
    {
        $simType = $this->normalizeSimType($request->input('type', session('sim_type', 'new_physical')));
        if (!in_array($simType, ['new_physical', 'recharge_physical'], true)) {
            $simType = 'new_physical';
        }

        session(['sim_type' => $simType]);

        $validated = $request->validate([
            'locations' => 'required|array',
            'locations.*' => 'string'
        ]);

        $query = JoytelPhysical::whereRaw('LOWER(type) LIKE ?', ['%recharge%']);

        foreach ($validated['locations'] as $location) {
            $query->where(function ($q) use ($location) {
                $q->whereJsonContains('coverage', $location)
                    ->orWhereRaw("JSON_SEARCH(coverage, 'one', ?) IS NOT NULL", [$location . '%']);
            });
        }

        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });

        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        return view('frontend.joytel.physical.packages', compact('packages'));
    }


    // jotel add to cart
    public function cart($joytel, Request $request)
    {
        if (!empty(session('roam_order_cart', []))) {
            return redirect()->back()
                ->with('error', 'Roam order already exists in your cart. Please checkout or remove it before adding a Joytel order.');
        }

        $request->validate([
            'sday' => 'required',
            'sdata' => 'required',
            'display_price' => 'required',
            'qty' => 'required',
            'product_code' => 'required|string',
        ]);

        $joytel = $this->resolveJoytelProduct((int) $joytel, $request->input('joytel_type'));
        $qty = max(1, (int) $request->qty);
        $price = (float) $request->display_price;
        $unitPrice = $qty > 0 ? round($price / $qty, 2) : $price;

        $cart = [
            'joytel' => $joytel->id,
            'joytel_type' => $joytel instanceof JoytelPhysical ? 'physical' : 'esim',
            'sim_type' => $joytel instanceof JoytelPhysical ? 'recharge_physical' : $this->normalizeSimType($request->input('sim_type', session('sim_type', 'new_esim'))),
            'sim_type_label' => $this->buildSimTypeLabel($joytel instanceof JoytelPhysical ? 'physical' : 'esim', $joytel instanceof JoytelPhysical || str_contains($this->normalizeSimType($request->input('sim_type', session('sim_type', 'new_esim'))), 'recharge') ? 'recharge' : 'new'),
            'order_type' => $joytel instanceof JoytelPhysical || str_contains($this->normalizeSimType($request->input('sim_type', session('sim_type', 'new_esim'))), 'recharge') ? 'recharge' : 'new',
            'product_code' => (string) $request->product_code,
            'product_name' => $joytel->product_name,
            'plan_type' => $request->input('tType'),
            'plan_type_label' => $request->input('tType') ? trim((string) $request->input('tType')) . ' Plan' : null,
            'service_day' => $request->sday,
            'service_data' => $request->sdata,
            'qty' => $qty,
            'price' => $price,
            'ori_price' => $unitPrice,
        ];

        $joytelCart = session()->get('joytel_cart', []);
        $found = false;
        foreach ($joytelCart as &$item) {
            if (
                ($item['joytel'] ?? null) === $cart['joytel']
                && ($item['joytel_type'] ?? null) === $cart['joytel_type']
                && ($item['product_code'] ?? null) === $cart['product_code']
                && ($item['service_day'] ?? null) == $cart['service_day']
                && ($item['service_data'] ?? null) === $cart['service_data']
            ) {
                if ($cart['joytel_type'] === 'esim' && ($cart['sim_type'] ?? '') === 'new_esim') {
                    $item['qty'] = (int) ($item['qty'] ?? 0) + $qty;
                    $item['price'] = (float) ($item['ori_price'] ?? $unitPrice) * (int) $item['qty'];
                } else {
                    $item['qty'] = 1;
                    $item['price'] = (float) ($item['ori_price'] ?? $unitPrice);
                }
                $found = true;
                break;
            }
        }

        unset($item);

        if (!$found) {
            $joytelCart[] = $cart;
        }

        session(['joytel_cart' => $joytelCart]);

        return redirect()->route('joytelpackage.cartpage');
    }

    public function cartPage()
    {
        return view('frontend.joytel.cart', [
            'cartItems' => session()->get('joytel_cart', []),
        ]);
    }

    public function removeCart($key)
    {
        $cart = session()->get('joytel_cart', []);

        if (!isset($cart[$key])) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.',
                'count' => count($cart),
            ], 404);
        }

        unset($cart[$key]);
        $cart = array_values($cart);
        session()->put('joytel_cart', $cart);

        return response()->json([
            'success' => true,
            'count' => count($cart),
        ]);
    }

    public function updateCartQuantity(Request $request, $key)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = session()->get('joytel_cart', []);

        if (!isset($cart[$key])) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.',
            ], 404);
        }

        $unitPrice = (float) ($cart[$key]['ori_price'] ?? $cart[$key]['price'] ?? 0);

        if (!$this->canAdjustQuantity($cart[$key])) {
            $cart[$key]['qty'] = 1;
            $cart[$key]['price'] = $unitPrice;
        } else {
            $cart[$key]['qty'] = (int) $validated['qty'];
            $cart[$key]['price'] = $unitPrice * (int) $validated['qty'];
        }

        $cart[$key]['ori_price'] = $unitPrice;
        session()->put('joytel_cart', $cart);

        return response()->json([
            'success' => true,
            'qty' => (int) $cart[$key]['qty'],
            'total' => (float) $cart[$key]['price'],
        ]);
    }

    // joytel checkout
    public function checkout()
    {
        $cart = session('joytel_cart', []);
        $cartItems = collect($this->normalizeCartItems($cart));

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Cart is Empty!');
        }

        Auth::shouldUse('customers');
        $customer = auth()->user();
        $paymentSetting = \App\Models\PaymentSetting::orderBy('id')->get();
        $isDirectActive = $paymentSetting->first()?->status;
        $isUabActive = $paymentSetting->last()?->status;


        return view('frontend.joytel.check-out', [
            'cartItems' => $cartItems->values()->all(),
            'selectedCartItems' => $cartItems->values()->all(),
            'subtotal' => $cartItems->sum(fn($item) => (float) ($item['price'] ?? 0)),
            'customer' => $customer,
            'is_direct' => $isDirectActive,
            'is_uab' => $isUabActive
        ]);
    }

    public function placeOrder(Request $request, JoytelOrderDraftService $draftService)
    {
        $cart = (array) session('joytel_cart', []);
        $cartItems = $this->normalizeCartItems($cart);

        $request->validate([
            'payment_method' => ['required', 'in:direct_bank_transfer,uab_pay,UAB Pay'],
            'phone' => ['required', 'string', 'max:50'],
            'source_sn_codes' => ['nullable', 'array'],
            'source_sn_codes.*' => ['nullable', 'string', 'size:20', 'regex:/^[0-9]{20}$/'],
            'terms' => ['accepted'],
        ]);

        foreach ($cartItems as $index => $item) {
            if (!$this->requiresCheckoutSnCode($item)) {
                continue;
            }

            $snCode = trim((string) $request->input("source_sn_codes.{$index}", ''));
            if ($snCode === '') {
                return back()
                    ->withInput()
                    ->withErrors(["source_sn_codes.{$index}" => 'SN Code is required for physical SIM recharge.']);
            }
        }

        Auth::shouldUse('customers');
        $customer = auth()->user();

        try {
            $result = $draftService->createDraftOrdersFromCart($customer, $cart, [
                'payment_method' => $request->input('payment_method') === 'UAB Pay' ? 'uab_pay' : $request->input('payment_method'),
                'phone' => $request->input('phone'),
                'source_sn_codes' => $request->input('source_sn_codes', []),
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        session()->forget('joytel_cart');
        session()->put('joytel_last_outer_order_id', $result['outer_order_id']);

        return redirect()->route('joytel.payment.show', ['outerOrderId' => $result['outer_order_id']]);
    }

    public function showPayment(string $outerOrderId)
    {
        Auth::shouldUse('customers');
        $customer = auth()->user();

        $orders = JoytelOrder::query()
            ->with('items')
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('customer.order.detail')->with('error', 'Order not found.');
        }

        $slipPath = data_get($orders->first()?->raw_response, 'payment.slip.path');

        return view('frontend.payment', [
            'provider' => 'joytel',
            'outer_order_id' => $outerOrderId,
            'orders' => $orders,
            'total' => $orders->sum(fn($order) => (float) $order->billable_total_price),
            'payment_status_view' => $this->buildPaymentStatusView($orders, !empty($slipPath)),
            'payment_detail_route' => route('customer.order.detail', ['outerOrderId' => $outerOrderId]),
            'payment_upload_route' => route('joytel.payment.upload-slip', ['outerOrderId' => $outerOrderId]),
        ]);
    }

    public function uploadPaymentSlip(Request $request, string $outerOrderId, OrderNotificationService $notifications)
    {
        Auth::shouldUse('customers');
        $customer = auth()->user();

        $validated = $request->validate([
            'payment_slip' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $orders = JoytelOrder::query()
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('customer.order.detail')->with('error', 'Order not found.');
        }

        if ($orders->every(fn(JoytelOrder $order) => (int) $order->our_status !== JoytelOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return redirect()->route('joytel.payment.show', ['outerOrderId' => $outerOrderId])
                ->with('error', 'Payment slip can only be updated while the order is pending payment.');
        }

        $existingSlipPath = data_get($orders->first()?->raw_response, 'payment.slip.path');
        $uploadedFile = $validated['payment_slip'];
        $storedPath = $uploadedFile->store('payment-slips', 'public');

        foreach ($orders as $order) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $payment = (array) data_get($rawResponse, 'payment', []);
            $payment['slip'] = [
                'path' => $storedPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getClientMimeType(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
            $rawResponse['payment'] = $payment;
            $order->raw_response = $rawResponse;
            $order->save();
        }

        $notifications->paymentSlipUploaded($orders->first()->refresh());

        if ($existingSlipPath && $existingSlipPath !== $storedPath && Storage::disk('public')->exists($existingSlipPath)) {
            Storage::disk('public')->delete($existingSlipPath);
        }

        return redirect()->route('joytel.payment.show', ['outerOrderId' => $outerOrderId])
            ->with('success', 'Payment slip uploaded successfully. Our admin team can review it now.');
    }


    public function esimPackageView($id, Request $request)
    {
        $simType = $this->normalizeSimType($request->input('sim_type', session('sim_type', 'new_esim')));

        session(['sim_type' => $simType]);

        $joytel = JoytelEsim::findOrFail($id);

        $packages = JoytelEsim::where('product_name', $joytel->product_name)
            ->where('status', 1)
            ->get();

        $traffic_types = $packages->pluck('traffic_type')->unique()->values();

        $daily_types = $packages->where('traffic_type', 'daily')->values();
        $total_types = $packages->where('traffic_type', 'total')->values();
        $unlimited_types = $packages->where('traffic_type', 'unlimited')->values();

        $service_days = $packages->pluck('service_day')->unique();

        $validPlans = PriceList::where('exchange_rate', '>', 0)
            ->pluck('plan')
            ->filter()
            ->unique()
            ->toArray();

        $random_packages = JoytelEsim::where('status', 1)
            ->where('product_name', '!=', $joytel->product_name)
            ->whereIn('product_name', $validPlans)
            ->inRandomOrder()
            ->get()
            ->unique('product_name');

        $network_types = $packages->pluck('network')->unique();

        $price_lists = PriceList::latest()->get();

        $joytel_type_label = 'E-SIM';

        return view('frontend.joytel.esim.package-view', compact(
            'joytel',
            'packages',
            'traffic_types',
            'daily_types',
            'total_types',
            'unlimited_types',
            'service_days',
            'random_packages',
            'network_types',
            'price_lists',
            'joytel_type_label',
            'simType'
        ));
    }


    public function physicalPackageView($id)
    {
        $joytel = JoytelPhysical::findOrFail($id);

        $packages = JoytelPhysical::where('product_name', $joytel->product_name)
            ->where('status', 1)
            ->get();

        $traffic_types = $packages->pluck('traffic_type')->unique()->values();

        $daily_types = $packages->where('traffic_type', 'daily')->values();
        $total_types = $packages->where('traffic_type', 'total')->values();
        $unlimited_types = $packages->where('traffic_type', 'unlimited')->values();

        $service_days = $packages->pluck('service_day')->unique();

        $validPlans = PriceList::where('exchange_rate', '>', 0)
            ->pluck('plan')
            ->filter()
            ->unique()
            ->toArray();

        $random_packages = JoytelPhysical::where('status', 1)
            ->where('product_name', '!=', $joytel->product_name)
            ->whereIn('product_name', $validPlans)
            ->inRandomOrder()
            ->get()
            ->unique('product_name');

        $network_types = $packages->pluck('network')->unique();


        $price_lists = PriceList::latest()->get();

        $joytel_type_label = 'Physical SIM';

        return view('frontend.joytel.physical.package-view', compact(
            'joytel',
            'packages',
            'traffic_types',
            'daily_types',
            'total_types',
            'unlimited_types',
            'service_days',
            'random_packages',
            'network_types',
            'price_lists',
            'joytel_type_label'
        ));
    }


    private function normalizeSimType(?string $simType): string
    {
        $simType = strtolower(trim((string) $simType));

        if ($simType === '' || !in_array($simType, ['new_esim', 'recharge_esim', 'new_physical', 'recharge_physical'], true)) {
            return 'new_esim';
        }

        return $simType;
    }

    private function resolveJoytelProduct(int $id, ?string $joytelType)
    {
        $joytelType = strtolower((string) $joytelType);

        if ($joytelType === 'physical') {
            return JoytelPhysical::findOrFail($id);
        }

        return JoytelEsim::findOrFail($id);
    }

    private function normalizeCartItems(array $cart): array
    {
        if (array_key_exists('joytel', $cart) || array_key_exists('product_code', $cart)) {
            return [$cart];
        }

        return array_values(array_filter($cart, static fn($item) => is_array($item)));
    }

    private function canAdjustQuantity(array $cartItem): bool
    {
        $serviceType = strtolower((string) ($cartItem['joytel_type'] ?? $cartItem['service_type'] ?? 'esim'));
        $simType = strtolower((string) ($cartItem['sim_type'] ?? ''));
        $orderType = strtolower((string) ($cartItem['order_type'] ?? (str_contains($simType, 'recharge') ? 'recharge' : 'new')));

        return $serviceType === 'esim' && $orderType === 'new' && !str_contains($simType, 'recharge');
    }

    private function requiresCheckoutSnCode(array $cartItem): bool
    {
        $serviceType = strtolower((string) ($cartItem['joytel_type'] ?? $cartItem['service_type'] ?? ''));
        $simType = strtolower((string) ($cartItem['sim_type'] ?? ''));
        $orderType = strtolower((string) ($cartItem['order_type'] ?? ''));

        return $serviceType === 'physical'
            && ($orderType === 'recharge' || str_contains($simType, 'recharge'));
    }

    private function buildSimTypeLabel(string $serviceType, string $orderType): string
    {
        $prefix = strtolower($orderType) === 'recharge' ? 'Recharge' : 'New';
        $suffix = strtolower($serviceType) === 'physical' ? 'Physical' : 'Esim';

        return $prefix . ' ' . $suffix;
    }

    private function buildPaymentStatusView($orders, bool $hasUploadedSlip): array
    {
        $hasPendingPayment = $orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_PENDING_PAYMENT);
        $hasFailed = $orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_API_FAILED);
        $hasAdminCancelled = $orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_ADMIN_CANCELLED);
        $hasCancelled = $orders->contains(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_CANCELLED);
        $allCompleted = $orders->every(fn(JoytelOrder $order) => (int) $order->our_status === JoytelOrder::OUR_STATUS_COMPLETED);
        $isOngoing = $orders->contains(fn(JoytelOrder $order) => in_array((int) $order->our_status, [
            JoytelOrder::OUR_STATUS_PAID,
            JoytelOrder::OUR_STATUS_API_PROCESSING,
            JoytelOrder::OUR_STATUS_API_SUCCESS,
        ], true));

        if ($allCompleted) {
            return [
                'badge' => 'Completed',
                'title' => 'Your order is successful.',
                'message' => 'Your payment was approved and your Joytel order has been completed successfully.',
                'tone' => 'success',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($isOngoing) {
            return [
                'badge' => 'Ongoing',
                'title' => 'Payment approved. Your Joytel order is being processed.',
                'message' => 'Our team is provisioning your order now. You can check the latest details from your account page.',
                'tone' => 'primary',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($hasFailed || $hasAdminCancelled || $hasCancelled) {
            return [
                'badge' => $hasAdminCancelled ? 'Admin Cancel' : ($hasCancelled ? 'Cancelled' : 'Failed'),
                'title' => $hasAdminCancelled ? 'This order was cancelled by admin.' : 'We could not complete this order.',
                'message' => 'Please contact support or check your order detail page for the latest status update.',
                'tone' => 'danger',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($hasPendingPayment && $hasUploadedSlip) {
            return [
                'badge' => 'Waiting Approval',
                'title' => 'Upload complete. Waiting for payment approval.',
                'message' => 'Your payment slip has been received. Our admin team will review it and approve your payment soon.',
                'tone' => 'warning',
                'show_upload_form' => true,
                'show_payment_guide' => true,
                'show_bank_accounts' => true,
                'upload_label' => 'Reupload Payment Slip',
                'upload_button_text' => 'Replace Slip',
            ];
        }

        return [
            'badge' => 'Pending Payment',
            'title' => 'Please complete your payment.',
            'message' => 'Transfer the amount below and upload your payment slip to continue with your order.',
            'tone' => 'warning',
            'show_upload_form' => true,
            'show_payment_guide' => true,
            'show_bank_accounts' => true,
            'upload_label' => 'Upload Payment Slip',
            'upload_button_text' => 'Upload Slip',
        ];
    }
}
