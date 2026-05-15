<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RoamSku;
use App\Models\Roam;
use App\Models\RoamPhysicalSku;
use App\Models\PriceList;
use App\Models\RoamOrder;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Services\Roam\RoamOrderService;

class ESimController extends Controller
{
    public function roam()
    {
        $countrys = Roam::pluck('support_country')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        $priceList = PriceList::where('dp_status', 0)
            ->whereNull('dp_info')->get();

        $skupackages = RoamSku::where('status', 1)
            ->get()
            ->filter(function ($sku) use ($priceList) {

                $roam = Roam::where('sku_id', $sku->sku_id)->first();
                if (!$roam || empty($roam->packages)) return false;

                $priceRows = $priceList->where('plan', $sku->sku_id);
                $priceCodes = $priceRows->pluck('product_code')->toArray();

                return collect($roam->packages)
                    ->where('status', 1)
                    ->contains(function ($pkg) use ($sku, $priceCodes, $priceRows) {
                        $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                        $legacyCode = $pkg['priceid'] ?? null;
                        $codeMatches = ($apiCode !== null && in_array($apiCode, $priceCodes, true)) ||
                            ($legacyCode !== null && in_array($legacyCode, $priceCodes, true));

                        if (!$codeMatches) {
                            return false;
                        }

                        $priceRow = $this->resolvePriceListRow(
                            (string) $sku->sku_id,
                            $apiCode !== null ? (string) $apiCode : null,
                            $legacyCode !== null ? (int) $legacyCode : null
                        );

                        return $priceRow && (float) $priceRow->exchange_rate > 0;
                    });
            })
            ->values();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view('frontend.esim.roam', compact(
            'logo',
            'title',
            'countrys',
            'skupackages',
            'priceList'
        ));
    }

    //for roam
    public function roamSearch(Request $request)
    {
        session(['sim_type' => $request->type ?? null]);
        if ($request->iccid_exist === "1") {
            session([
                'iccid_exist' => true,
                'iccid_no' => $request->iccid_number ?? null
            ]);
        } else {
            session()->forget([
                'iccid_exist',
                'iccid_no'
            ]);
        }
        $validated = $request->validate([
            'countryname'   => 'required|array',
            'countryname.*' => 'string'
        ]);


        $skus = Roam::where(function ($query) use ($validated) {
            foreach ($validated['countryname'] as $country) {
                $query->orWhereJsonContains('support_country', $country);
            }
        })->pluck('sku_id');


        if ($skus->isEmpty()) {
            return redirect()->back()->with('error', 'No packages found for the selected countries.');
        }


        $packages = RoamSku::whereIn('sku_id', $skus)
            ->where('status', 1)
            ->whereIn('sku_id', function ($subquery) {
                $subquery->select('plan')
                    ->from('price_lists')
                    ->where('dp_status', 0)
                    ->whereNotNull('plan');
            })
            ->get()
            ->filter(function ($sku) {
                $roam = Roam::where('sku_id', $sku->sku_id)->first();
                if (!$roam || empty($roam->packages)) {
                    return false;
                }

                return collect($roam->packages)
                    ->where('status', 1)
                    ->contains(function ($pkg) use ($sku) {
                        $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                        $legacyCode = $pkg['priceid'] ?? null;

                        $priceRow = $this->resolvePriceListRow(
                            (string) $sku->sku_id,
                            $apiCode !== null ? (string) $apiCode : null,
                            $legacyCode !== null ? (int) $legacyCode : null
                        );

                        return $priceRow && (float) $priceRow->exchange_rate > 0;
                    });
            })
            ->values();

        // dd($packages);
        $priceList = PriceList::all();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view('frontend.esim.roam-package', compact('logo', 'title', 'packages', 'skus', 'priceList'));
    }

    public function roamView($skuid, Request $request)
    {
        if ($request->has('list_view')) {
            session()->forget(['iccid_exist', 'iccid_no']);
            session(['sim_type' => 'new_esim']);
        }
        $roam = Roam::where('sku_id', $skuid)->first();

        $packages = $roam->packages;
        //$activePackages = collect($packages)->where('status', 1)->values();

        $sku = RoamSku::where('sku_id', $skuid)->first();

        //$pricelists = PriceList::where('dp_status', 0)->get();
        $pricelists = PriceList::where('dp_status', 0)
            ->whereNull('dp_info')
            ->where('plan', $skuid)
            ->get();

        $priceListCodes = $pricelists->pluck('product_code')->toArray();

        $activePackages = collect($packages)
            ->where('status', 1)
            ->filter(function ($pkg) use ($priceListCodes) {
                $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                $legacyCode = $pkg['priceid'] ?? null;

                return (
                    ($apiCode !== null && in_array($apiCode, $priceListCodes)) ||
                    ($legacyCode !== null && in_array($legacyCode, $priceListCodes))
                );
            })
            ->filter(function ($pkg) use ($skuid) {
                $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                $legacyCode = $pkg['priceid'] ?? null;

                $priceList = $this->resolvePriceListRow(
                    (string) $skuid,
                    $apiCode !== null ? (string) $apiCode : null,
                    $legacyCode !== null ? (int) $legacyCode : null
                );

                return $priceList && (float) $priceList->exchange_rate > 0;
            })
            ->values();

        $validPackages = $activePackages;

        $hasValidPlans = $validPackages->isNotEmpty();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $randomSkus = RoamSku::where('status', 1)
            ->where('sku_id', '!=', $skuid)
            ->whereIn('sku_id', function ($query) {
                $query->select('plan')
                    ->from('price_lists')
                    ->where('dp_status', 0)
                    ->whereNull('dp_info');
            })
            ->inRandomOrder()
            ->take(12)
            ->get()
            ->filter(function ($sku) {
                $roam = Roam::where('sku_id', $sku->sku_id)->first();
                if (!$roam || empty($roam->packages)) {
                    return false;
                }

                return collect($roam->packages)
                    ->where('status', 1)
                    ->contains(function ($pkg) use ($sku) {
                        $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                        $legacyCode = $pkg['priceid'] ?? null;

                        $priceRow = $this->resolvePriceListRow(
                            (string) $sku->sku_id,
                            $apiCode !== null ? (string) $apiCode : null,
                            $legacyCode !== null ? (int) $legacyCode : null
                        );

                        return $priceRow && (float) $priceRow->exchange_rate > 0;
                    });
            })
            ->take(3)
            ->values();

        return view('frontend.esim.roam-package-view', compact(
            'logo',
            'title',
            'sku',
            'roam',
            'activePackages',
            'validPackages',
            'pricelists',
            'hasValidPlans',
            'randomSkus'
        ));
    }

    public function cart(Request $request)
    {
        // session()->forget('roam_order_cart');
        // dd('hi');
        $sim_type = session()->get('sim_type');
        $request->validate([
            'skuid' => 'required',
            'sday' => 'required',
            'sdata' => 'required',
            'display_price' => 'required',
            'qty' => 'required',
            'original_selected_price' => 'required'
        ]);

        $sku = RoamSku::where('sku_id', $request->skuid)->first();
        // dd(session()->all());
        $iccid_no = session()->get('iccid_no');
        $iccid_exist = session()->get('iccid_exist');

        $roamCart = session()->get('roam_order_cart', []);
        $found = false;
        foreach ($roamCart as &$item) {
            if ($item['country_name'] == $sku->country_name && $item['service_day'] == $request->sday && $item['service_data'] == $request->sdata && $item['iccid_no'] == $iccid_no && $item['iccid_exist'] == $iccid_exist && $item['sim_type'] == $sim_type) {
                $item['qty'] += $request->qty;
                $item['price'] += $request->display_price;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $roamCart[] = [
                'sku_id' => $sku->id,
                'sim_type' => $sim_type,
                'country_name' => $sku->country_name,
                'service_day' => $request->sday,
                'service_data' => $request->sdata,
                'qty' => $request->qty,
                'price' => $request->display_price,
                'iccid_no' => $iccid_no,
                'iccid_exist' => $iccid_exist,
                'ori_price' => $request->original_selected_price
            ];
        }

        session(['roam_order_cart' => $roamCart]);

        return redirect()->route('roam.esim.cartpage');
    }

    // joytel checkout
    public function checkout()
    {
        $cart = session('roam_cart');
        if (!$cart) {
            return redirect()->back()->with('error', 'Cart is Empty!');
        }

        if ($this->isPhysicalCart($cart)) {
            return app(PhysicalSimController::class)->checkout();
        }

        $customer = auth('customers')->user();
        $sku = RoamSku::findOrFail($cart['sku']);
        return view('frontend.esim.checkout', [
            'sku' => $sku,
            'cart' => $cart,
            'service_day' => $cart['service_day'],
            'service_data' => $cart['service_data'],
            'qty' => $cart['qty'],
            'price' => $cart['price'],
            'customer' => $customer,
        ]);
    }

    public function placeOrder(Request $request, RoamOrderService $service)
    {
        $cart = session('roam_cart');
        if (!$cart) {
            return redirect()->route('roam.checkout')->with('error', 'Cart is Empty!');
        }

        if ($this->isPhysicalCart($cart)) {
            return app(PhysicalSimController::class)->placeOrder($request, $service);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'accept_policy' => ['accepted'],
        ]);

        $customer = auth('customers')->user();
        abort_unless($customer instanceof Customer, 403, 'You must be logged in to place a Roam order.');

        $payload = $this->buildPlaceOrderPayload($cart, $customer, $service, $validated['payment_method']);
        $order = $service->placeOrder($payload, $customer);

        session()->forget('roam_cart');
        session()->put('roam_last_order', [
            'roam_order_num' => $order->roam_order_num,
            'outer_order_id' => $order->outer_order_id,
        ]);

        return redirect()
            ->route('roam.order.success', $order->roam_order_num)
            ->with('success', 'Roam order placed successfully.');
    }

    public function success(string $roamOrderNum)
    {
        $roamOrder = RoamOrder::with(['customer', 'items'])
            ->where('roam_order_num', $roamOrderNum)
            ->firstOrFail();

        abort_unless((int) $roamOrder->customer_id === (int) auth('customers')->id(), 403);

        if ($this->isPhysicalOrder($roamOrder)) {
            return app(PhysicalSimController::class)->success($roamOrderNum);
        }

        return view('frontend.esim.order-success', $this->buildOrderStatusViewData($roamOrder));
    }

    public function track(string $roamOrderNum, Request $request, RoamOrderService $service)
    {
        $roamOrder = RoamOrder::with(['customer', 'items'])
            ->where('roam_order_num', $roamOrderNum)
            ->firstOrFail();

        abort_unless((int) $roamOrder->customer_id === (int) auth('customers')->id(), 403);

        if ($this->isPhysicalOrder($roamOrder)) {
            return app(PhysicalSimController::class)->track($roamOrderNum, $request, $service);
        }

        if ($request->boolean('refresh')) {
            $roamOrder = $service->syncByOrderNum($roamOrder->roam_order_num);
        }

        return view('frontend.esim.order-status', $this->buildOrderStatusViewData($roamOrder));
    }

    private function buildPlaceOrderPayload(
        array $cart,
        Customer $customer,
        RoamOrderService $service,
        string $paymentMethod
    ): array {
        $priceList = $this->resolvePriceListRow(
            (string) ($cart['sku_id'] ?? ''),
            $cart['api_code'] ?? null,
            $cart['price_id'] ?? null
        );

        if (!$priceList) {
            abort(422, 'Unable to resolve the selected Roam package.');
        }

        $quantity = max(1, (int) ($cart['qty'] ?? 1));
        $unitPrice = (float) ($cart['unit_price'] ?? $cart['price'] ?? 0);
        $totalPrice = (float) ($cart['total_price'] ?? $cart['price'] ?? ($unitPrice * $quantity));
        $daypassDays = (int) ($cart['daypass_days'] ?? $cart['service_day'] ?? 1);

        return [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone ?? null,
            'sku_id' => (string) ($cart['sku_id'] ?? ''),
            'price_id' => (int) $priceList->id,
            'api_code' => (string) $priceList->product_code,
            'service_type' => 'esim',
            'order_type' => 'new',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'daypass_days' => $daypassDays,
            'start_date' => null,
            'end_date' => null,
            'remark' => trim(sprintf(
                '%s | %s',
                (string) ($cart['plan_name'] ?? $cart['service_data'] ?? ''),
                (string) $customer->email
            ), ' |'),
            'outer_order_id' => $service->generateOuterOrderId(),
            'our_status' => RoamOrder::OUR_STATUS_ON_HOLD,
            'payment_method' => $paymentMethod,
            'is_send_email' => true,
            'back_info' => 1,
        ];
    }

    private function buildOrderStatusViewData(RoamOrder $order): array
    {
        $order->loadMissing(['customer', 'items']);
        $rawResponse = (array) ($order->raw_response ?? []);
        $requestPayload = data_get($rawResponse, 'request', []);
        $localRequestPayload = data_get($rawResponse, 'local_request', $requestPayload);
        $responsePayload = data_get($rawResponse, 'response.data', []);

        return [
            'order' => $order,
            'requestPayload' => is_array($requestPayload) ? $requestPayload : [],
            'localRequestPayload' => is_array($localRequestPayload) ? $localRequestPayload : [],
            'responsePayload' => is_array($responsePayload) ? $responsePayload : [],
            'ourStatusLabels' => RoamOrder::OUR_STATUS_LABELS,
            'roamStatusLabels' => RoamOrder::ROAM_STATUS_LABELS,
            'currentOurStatusLabel' => RoamOrder::OUR_STATUS_LABELS[(int) $order->our_status] ?? (string) $order->our_status,
            'currentRoamStatusLabel' => RoamOrder::ROAM_STATUS_LABELS[(int) ($order->roam_status ?? -1)] ?? ($order->roam_status === null ? 'Waiting for upstream update' : (string) $order->roam_status),
        ];
    }

    private function resolvePriceListRow(string $skuId, ?string $apiCode, ?int $priceId): ?PriceList
    {
        $query = PriceList::query()
            ->where('plan', $skuId)
            ->where('dp_status', 0)
            ->whereNull('dp_info');

        if ($priceId) {
            $row = (clone $query)->whereKey($priceId)->first();
            if ($row) {
                return $row;
            }
        }

        if ($apiCode) {
            $row = (clone $query)->where('product_code', $apiCode)->first();
            if ($row) {
                return $row;
            }
        }

        return $query->first();
    }

    private function isPhysicalSku(string|int $skuId): bool
    {
        return RoamPhysicalSku::where('sku_id', $skuId)->exists();
    }

    private function isPhysicalCart(array $cart): bool
    {
        $skuId = $cart['sku_id'] ?? null;
        return $skuId !== null && $this->isPhysicalSku($skuId);
    }

    private function isPhysicalOrder(RoamOrder $order): bool
    {
        return (string) ($order->service_type ?? '') === 'physical';
    }

    public function cartPage()
    {
        return view('frontend.esim.cart');
    }

    public function removeCart($key)
    {
        $cart = session()->get('roam_order_cart', []);

        if (isset($cart[$key])) {

            unset($cart[$key]);

            // re-index array
            $cart = array_values($cart);

            session()->put('roam_order_cart', $cart);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
