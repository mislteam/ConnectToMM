<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RoamPhysical;
use App\Models\RoamPhysicalSku;
use Illuminate\Http\Request;
use App\Models\PriceList;
use App\Models\GeneralSetting;
use App\Models\RoamSku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PhysicalSimController extends Controller
{
    public function roamPhysical(Request $request)
    {
        $selectedOrderType = (string) $request->query('type', session('sim_type', 'recharge_physical'));
        if (!in_array($selectedOrderType, ['new_physical', 'recharge_physical'], true)) {
            $selectedOrderType = 'recharge_physical';
        }

        $selectedDpId = (int) $request->query('dp_id', 9);
        if (!in_array($selectedDpId, [9, 21], true)) {
            $selectedDpId = 9;
        }

        $activeSkus = RoamPhysicalSku::where('status', 1)->get();

        $countrys = $activeSkus
            ->where('dp_id', $selectedDpId)
            ->pluck('country_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $globalCountries = $activeSkus
            ->where('dp_id', 9)
            ->pluck('country_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $asiaCountries = $activeSkus
            ->where('dp_id', 21)
            ->pluck('country_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $priceList = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->get();

        $priceListByPlan = $priceList->groupBy('plan');
        $roamBySku = RoamPhysical::whereIn('sku_id', $activeSkus->pluck('sku_id')->all())
            ->get()
            ->keyBy(fn($row) => $row->sku_id . ':' . (int) ($row->dp_id ?? 0));
        $packageCards = $this->buildPhysicalPackageCards($activeSkus, $priceListByPlan, $roamBySku);

        $globalPackageCards = $packageCards->where('dp_id', 9)->values();
        $asiaPackageCards = $packageCards->where('dp_id', 21)->values();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        $orderTabs = getOrderTypes('roam_order_types', 'physical');
        $selectedOrderType = collect($orderTabs)->keys()->first();

        return view(
            'frontend.physical.roam-physical',
            compact(
                'logo',
                'title',
                'countrys',
                'globalCountries',
                'asiaCountries',
                'globalPackageCards',
                'asiaPackageCards',
                'selectedDpId',
                'orderTabs',
                'selectedOrderType'
            )
        );
    }

    public function roamPhysicalSearch(Request $request)
    {
        $simType = (string) $request->input('type', session('sim_type', 'recharge_physical'));
        if (!in_array($simType, ['new_physical', 'recharge_physical'], true)) {
            $simType = 'recharge_physical';
        }

        session([
            'sim_type' => $simType,
            'iccid_exist' => true,
            'iccid_no' => $request->iccid_number ?? null
        ]);

        $validated = $request->validate([
            'dp_id' => ['required', 'integer', 'in:9,21'],
            'countryname'   => 'required|array',
            'countryname.*' => 'string'
        ]);

        $skus = RoamPhysicalSku::where('dp_id', $validated['dp_id'])
            ->whereIn('country_name', $validated['countryname'])
            ->pluck('sku_id');

        if ($skus->isEmpty()) {
            return redirect()->back()->with('error', 'No packages found for the selected countries.');
        }

        $packages = RoamPhysicalSku::whereIn('sku_id', $skus)
            ->where('status', 1)
            ->whereIn('sku_id', function ($subquery) {
                $subquery->select('plan')
                    ->from('price_lists')
                    ->where('dp_status', 1)
                    ->whereNotNull('plan');
            })
            ->get()
            ->filter(function ($sku) {
                return $this->getLowestPhysicalPrice($sku->sku_id) !== null;
            })
            ->values();

        $priceList = PriceList::all();
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();


        return view('frontend.physical.roam-physical-package', compact('logo', 'title', 'packages', 'skus', 'priceList'));
    }

    public function roamPhysicalView($skuid, Request $request)
    {
        $selectedDpInfo = (int) $request->query('dp_id', session('physical_dp_id', 0));
        if ($request->list_view) {
            $simType = (string) $request->query('sim_type', session('sim_type', 'recharge_physical'));
            session([
                'iccid_exist' => true,
                'iccid_no' => null,
                'sim_type' => in_array($simType, ['new_physical', 'recharge_physical'], true)
                    ? $simType
                    : 'recharge_physical'
            ]);
        }
        $skuQuery = RoamPhysicalSku::where('sku_id', $skuid);
        if (in_array($selectedDpInfo, [9, 21], true)) {
            $skuQuery->where('dp_id', $selectedDpInfo);
        }

        $sku = $skuQuery->firstOrFail();
        $selectedDpInfo = (int) ($sku->dp_id ?? $selectedDpInfo ?? 0);
        session(['physical_dp_id' => $selectedDpInfo]);

        $roam = RoamPhysical::where('sku_id', $skuid)
            ->when($selectedDpInfo, function ($query) use ($selectedDpInfo) {
                $query->where('dp_id', $selectedDpInfo);
            })
            ->firstOrFail();
        $packages = $roam->packages;
        //$activePackages = collect($packages)->where('status', 1)->values();

        $pricelists = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->where('plan', $skuid)
            ->when($selectedDpInfo, function ($query) use ($selectedDpInfo) {
                $query->where('dp_info', $selectedDpInfo);
            })
            ->get();

        if ($pricelists->isEmpty()) {
            $pricelists = PriceList::where('dp_status', 1)
                ->whereNotNull('dp_info')
                ->where('plan', $skuid)
                ->get();
        }

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
                return $this->getPackagePlanPrice($skuid, $pkg) !== null;
            })
            ->values();

        $validPackages = $activePackages;

        $hasValidPlans = $activePackages->isNotEmpty();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $randomSkus = RoamPhysicalSku::where('status', 1)
            ->where('sku_id', '!=', $skuid)
            ->whereIn('sku_id', function ($query) use ($selectedDpInfo) {
                $query->select('plan')
                    ->from('price_lists')
                    ->where('dp_status', 1)
                    ->whereNotNull('dp_info')
                    ->when($selectedDpInfo, function ($subquery) use ($selectedDpInfo) {
                        $subquery->where('dp_info', $selectedDpInfo);
                    });
            })
            ->inRandomOrder()
            ->take(12)
            ->get()
            ->filter(function ($sku) use ($selectedDpInfo) {
                return $this->getLowestPhysicalPrice($sku->sku_id, null, $selectedDpInfo) !== null;
            })
            ->take(3)
            ->values();
        $canAdjustQuantity = $this->canAdjustQuantity([
            'sim_type' => session('sim_type'),
            'service_type' => 'physical',
            'order_type' => $this->resolveOrderType(session('sim_type')),
        ]);

        return view('frontend.physical.roam-physical-package-view', compact(
            'logo',
            'title',
            'sku',
            'roam',
            'activePackages',
            'validPackages',
            'pricelists',
            'hasValidPlans',
            'randomSkus',
            'selectedDpInfo',
            'canAdjustQuantity'
        ));
    }

    private function getLowestPhysicalPrice(string|int $skuId, $priceList = null, ?int $dpInfo = null): ?float
    {
        $priceList = $priceList ?? PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->when($dpInfo, function ($query) use ($dpInfo) {
                $query->where('dp_info', $dpInfo);
            })
            ->where('plan', $skuId)
            ->get();

        if ($priceList->isEmpty() && $dpInfo) {
            $priceList = PriceList::where('dp_status', 1)
                ->whereNotNull('dp_info')
                ->where('plan', $skuId)
                ->get();
        }

        $priceMap = $priceList->pluck('exchange_rate', 'product_code');

        $roam = RoamPhysical::where('sku_id', $skuId)
            ->when($dpInfo, function ($query) use ($dpInfo) {
                $query->where('dp_id', $dpInfo);
            })
            ->first();
        if (!$roam || empty($roam->packages)) {
            return null;
        }

        $lowestPrice = collect($roam->packages)
            ->filter(fn($pkg) => ($pkg['status'] ?? 0) == 1)
            ->map(function ($pkg) use ($priceMap) {
                $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                $legacyCode = $pkg['priceid'] ?? null;

                $rate = ($apiCode !== null && isset($priceMap[$apiCode]))
                    ? $priceMap[$apiCode]
                    : (($legacyCode !== null && isset($priceMap[$legacyCode])) ? $priceMap[$legacyCode] : null);

                if ($rate === null || (float) $rate <= 0) {
                    return null;
                }

                $portalPrice = (float) ($pkg['price'] ?? 0) + (float) ($pkg['openCardFee'] ?? 0);
                if ($portalPrice <= 0) {
                    return null;
                }

                return $portalPrice * (float) $rate;
            })
            ->filter(fn($price) => $price > 0)
            ->min();

        return $lowestPrice !== null ? (float) $lowestPrice : null;
    }

    private function buildPhysicalPackageCards(Collection $skus, Collection $priceListByPlan, ?Collection $roamBySku = null): Collection
    {
        return $skus
            ->map(function ($sku) use ($priceListByPlan, $roamBySku) {
                $roamKey = $sku->sku_id . ':' . (int) ($sku->dp_id ?? 0);
                $roam = $roamBySku?->get($roamKey) ?? RoamPhysical::where('sku_id', $sku->sku_id)
                    ->when((int) ($sku->dp_id ?? 0), function ($query, $dpId) {
                        $query->where('dp_id', $dpId);
                    })
                    ->first();
                if (!$roam || empty($roam->packages)) {
                    return null;
                }

                $priceRows = $priceListByPlan->get($sku->sku_id, collect());
                $priceMap = $priceRows->pluck('exchange_rate', 'product_code');

                $lowestPrice = collect($roam->packages)
                    ->filter(fn($pkg) => ($pkg['status'] ?? 0) == 1)
                    ->map(function ($pkg) use ($priceMap) {
                        $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                        $legacyCode = $pkg['priceid'] ?? null;

                        $rate = ($apiCode !== null && isset($priceMap[$apiCode]))
                            ? $priceMap[$apiCode]
                            : (($legacyCode !== null && isset($priceMap[$legacyCode])) ? $priceMap[$legacyCode] : null);

                        if ($rate === null || (float) $rate <= 0) {
                            return null;
                        }

                        $portalPrice = (float) ($pkg['price'] ?? 0) + (float) ($pkg['openCardFee'] ?? 0);
                        if ($portalPrice <= 0) {
                            return null;
                        }

                        return $portalPrice * (float) $rate;
                    })
                    ->filter(fn($price) => $price > 0)
                    ->min();

                if ($lowestPrice === null) {
                    return null;
                }

                return [
                    'package' => $sku,
                    'roam' => $roam,
                    'lowest_price' => (float) $lowestPrice,
                    'dp_id' => (int) ($sku->dp_id ?? 0),
                ];
            })
            ->filter()
            ->values();
    }

    private function getPackagePlanPrice(string|int $skuId, array $pkg): ?float
    {
        $priceList = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->where('plan', $skuId)
            ->get();

        if ($priceList->isEmpty()) {
            $priceList = PriceList::where('dp_status', 1)
                ->where('plan', $skuId)
                ->get();
        }

        $priceMap = $priceList->pluck('exchange_rate', 'product_code');
        $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
        $legacyCode = $pkg['priceid'] ?? null;

        $rate = ($apiCode !== null && isset($priceMap[$apiCode]))
            ? $priceMap[$apiCode]
            : (($legacyCode !== null && isset($priceMap[$legacyCode])) ? $priceMap[$legacyCode] : null);

        if ($rate === null || (float) $rate <= 0) {
            return null;
        }

        $portalPrice = (float) ($pkg['price'] ?? 0) + (float) ($pkg['openCardFee'] ?? 0);
        if ($portalPrice <= 0) {
            return null;
        }

        return $portalPrice * (float) $rate;
    }

    public function cart(Request $request)
    {
        // session()->forget('roam_order_cart');
        $sim_type = (string) $request->input('sim_type', session('sim_type', 'recharge_physical'));
        if (!in_array($sim_type, ['new_physical', 'recharge_physical'], true)) {
            $sim_type = 'recharge_physical';
        }
        session(['sim_type' => $sim_type]);
        $request->validate([
            'skuid' => 'required',
            'tType' => 'nullable|string|in:Daily,Unlimited,Total',
            'sday' => 'required',
            'sdata' => 'required',
            'display_price' => 'required',
            'qty' => 'required',
            'original_selected_price' => 'required',
            // Roam ordering requires api_code (product_code). Without it we can't create an upstream order later.
            'api_code' => 'required|string|min:1',
            'dp_id' => 'nullable|integer|in:9,21'
        ]);
        $selectedDpInfo = (int) $request->input('dp_id', session('physical_dp_id', 0));
        $skuQuery = RoamPhysicalSku::where('sku_id', $request->skuid);
        if (in_array($selectedDpInfo, [9, 21], true)) {
            $skuQuery->where('dp_id', $selectedDpInfo);
        }
        $sku = $skuQuery->first();
        if (!$sku) {
            return redirect()->back()->with('error', 'Selected package is invalid!');
        }
        session(['physical_dp_id' => (int) ($sku->dp_id ?? $selectedDpInfo ?? 0)]);
        $iccid_no = session()->get('iccid_no');
        $iccid_exist = session()->get('iccid_exist');
        $service_type = 'physical';
        $order_type = $this->resolveOrderType($sim_type);
        $apiCode = $this->normalizeApiCode($request->input('api_code'));
        if ($apiCode === null || trim($apiCode) === '') {
            return redirect()->back()->with('error', 'Selected package is missing api_code. Please refresh and select again.');
        }
        $planType = $this->normalizePlanType($request->input('tType'));
        $dpInfo = (int) ($sku->dp_id ?? 0);
        $dpLabel = $this->buildDpLabel($dpInfo);
        $canAdjustQuantity = $this->canAdjustQuantity([
            'sim_type' => $sim_type,
            'service_type' => $service_type,
            'order_type' => $order_type,
        ]);
        $unitPrice = (float) $request->original_selected_price;
        $requestedQty = max(1, (int) $request->qty);
        $qty = $canAdjustQuantity ? $requestedQty : 1;
        $price = $unitPrice * $qty;

        $roamCart = session()->get('roam_order_cart', []);
        $found = false;
        foreach ($roamCart as &$item) {
            if (
                $item['country_name'] == $sku->country_name
                && ($item['plan_type'] ?? null) === $planType
                && $item['service_day'] == $request->sday
                && $item['service_data'] == $request->sdata
                && $this->normalizeApiCode($item['api_code'] ?? null) === $apiCode
                && $item['iccid_no'] == $iccid_no
                && $item['iccid_exist'] == $iccid_exist
                && ($item['sim_type'] ?? null) == $sim_type
                && ($item['service_type'] ?? null) == $service_type
                && ($item['order_type'] ?? null) == $order_type
                && $this->resolveCartItemDpInfo($item) === $dpInfo
            ) {
                $itemUnitPrice = (float) ($item['ori_price'] ?? $unitPrice);

                if ($canAdjustQuantity) {
                    $item['qty'] = (int) ($item['qty'] ?? 0) + $requestedQty;
                } else {
                    $item['qty'] = 1;
                }

                $item['ori_price'] = $itemUnitPrice;
                $item['price'] = $itemUnitPrice * (int) $item['qty'];
                $item['dp_info'] = $dpInfo;
                $item['dp_label'] = $dpLabel;
                $item['plan_type'] = $planType;
                $item['plan_type_label'] = $this->buildPlanTypeLabel($planType);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $roamCart[] = [
                'sku' => $sku->id,
                'sku_id' => $sku->id,
                'sim_type' => $sim_type,
                'service_type' => $service_type,
                'order_type' => $order_type,
                'dp_info' => $dpInfo,
                'dp_label' => $dpLabel,
                'api_code' => $apiCode,
                'plan_type' => $planType,
                'plan_type_label' => $this->buildPlanTypeLabel($planType),
                'country_name' => $sku->country_name,
                'service_day' => $request->sday,
                'service_data' => $request->sdata,
                'qty' => $qty,
                'price' => $price,
                'iccid_no' => $iccid_no,
                'iccid_exist' => $iccid_exist,
                'ori_price' => $unitPrice,
            ];
        }

        session(['roam_order_cart' => $roamCart]);
        return redirect()->route('roam.physical.cartpage');
    }

    // joytel checkout
    public function checkout(Request $request)
    {
        $cart = session('roam_order_cart', []);
        $cartItems = (is_array($cart) && (array_key_exists('sku_id', $cart) || array_key_exists('sku', $cart)))
            ? collect([$cart])
            : collect($cart)->values();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Cart is Empty!');
        }

        Auth::shouldUse('customers');
        $customer = auth()->user();

        $serviceItems = $cartItems->values();

        if ($serviceItems->isEmpty()) {
            return redirect()->back()->with('error', 'No SIM items found in cart!');
        }

        $itemsToUse = $serviceItems->map(function ($item) {
            $serviceType = (string) ($item['service_type'] ?? $this->resolveServiceType($item['sim_type'] ?? session('sim_type')));
            $orderType = (string) ($item['order_type'] ?? $this->resolveOrderType($item['sim_type'] ?? session('sim_type')));
            $canAdjustQuantity = $this->canAdjustQuantity([
                'sim_type' => $item['sim_type'] ?? session('sim_type'),
                'service_type' => $serviceType,
                'order_type' => $orderType,
            ]);
            $planType = $this->normalizePlanType($item['plan_type'] ?? null);

            if (!$canAdjustQuantity) {
                $unitPrice = (float) ($item['ori_price'] ?? $item['price'] ?? 0);
                $item['qty'] = 1;
                $item['price'] = $unitPrice;
                $item['ori_price'] = $unitPrice;
            }

            $item['sim_type_label'] = $this->buildSimTypeLabel($serviceType, $orderType);
            $item['plan_type'] = $planType;
            $item['plan_type_label'] = $item['plan_type_label'] ?? $this->buildPlanTypeLabel($planType);
            $item['iccid_count'] = $this->getIccidCount($item);
            $item['dp_info'] = $this->resolveCartItemDpInfo($item);
            $item['dp_label'] = $item['dp_label'] ?? $this->buildDpLabel($item['dp_info']);
            $item['iccid_label'] = $this->buildIccidLabel(
                $item,
                (string) ($item['country_name'] ?? ''),
                $item['dp_info']
            );

            return $item;
        })->values();

        $primaryItem = $itemsToUse->first();
        $skuId = $primaryItem['sku_id'] ?? $primaryItem['sku'] ?? null;

        if (!$skuId) {
            return redirect()->back()->with('error', 'Cart data is invalid!');
        }

        $sku = RoamPhysicalSku::findOrFail($skuId);
        $itemsToUse = $itemsToUse->map(function ($item) {
            $item['dp_info'] = $this->resolveCartItemDpInfo($item);
            $item['dp_label'] = $item['dp_label'] ?? $this->buildDpLabel($item['dp_info']);
            $serviceType = $this->getCartServiceType($item);
            $orderType = (string) ($item['order_type'] ?? $this->resolveOrderType($item['sim_type'] ?? session('sim_type')));
            $planType = $this->normalizePlanType($item['plan_type'] ?? null);
            $item['sim_type_label'] = $this->buildSimTypeLabel($serviceType, $orderType);
            $item['plan_type'] = $planType;
            $item['plan_type_label'] = $item['plan_type_label'] ?? $this->buildPlanTypeLabel($planType);
            $item['iccid_label'] = $this->buildIccidLabel(
                $item,
                (string) ($item['country_name'] ?? ''),
                $item['dp_info']
            );

            return $item;
        })->values();
        $primaryItem = $itemsToUse->first();
        $price = (float) ($primaryItem['price'] ?? 0);
        $iccidCount = $this->getIccidCount($primaryItem);
        $iccidNumbers = array_values(array_pad(
            (array) old('iccid_numbers', session('iccid_numbers', [])),
            $iccidCount,
            ''
        ));

        $request->merge([
            'price' => $price,
        ]);

        return view('frontend.physical.checkout', [
            'sku' => $sku,
            'cart' => $primaryItem,
            'cartItems' => $cartItems->values()->all(),
            'selectedCartItems' => $itemsToUse->all(),
            'service_day' => $primaryItem['service_day'] ?? null,
            'service_data' => $primaryItem['service_data'] ?? null,
            'qty' => $primaryItem['qty'] ?? 1,
            'price' => $price,
            'customer' => $customer,
            'subtotal' => $serviceItems->sum(fn($item) => (float) ($item['price'] ?? 0)),
            'requires_iccid' => $this->requiresIccid($primaryItem),
            'iccid_label' => $this->buildIccidLabel(
                $primaryItem,
                (string) $sku->country_name,
                (int) ($primaryItem['dp_info'] ?? 0)
            ),
            'iccid_count' => $iccidCount,
            'iccid_numbers' => $iccidNumbers,
        ]);
    }

    public function updateCartQuantity(Request $request, $key)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = session()->get('roam_order_cart', []);

        if (!isset($cart[$key])) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.',
            ], 404);
        }

        if (!$this->canAdjustQuantity($cart[$key])) {
            $unitPrice = (float) ($cart[$key]['ori_price'] ?? $cart[$key]['price'] ?? 0);
            $cart[$key]['qty'] = 1;
            $cart[$key]['price'] = $unitPrice;

            session()->put('roam_order_cart', $cart);

            return response()->json([
                'success' => true,
                'qty' => 1,
                'total' => $cart[$key]['price'],
            ]);
        }

        $cart[$key]['qty'] = (int) $validated['qty'];

        $unitPrice = (float) ($cart[$key]['ori_price'] ?? $cart[$key]['price'] ?? 0);
        $cart[$key]['price'] = $unitPrice * (int) $validated['qty'];

        session()->put('roam_order_cart', $cart);

        return response()->json([
            'success' => true,
            'qty' => (int) $validated['qty'],
            'total' => $cart[$key]['price'],
        ]);
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

    public function cartPage()
    {
        $cart = session()->get('roam_order_cart', []);

        if (is_array($cart) && !empty($cart)) {
            $normalizedCart = array_map(function (array $item) {
                $serviceType = $this->getCartServiceType($item);
                $orderType = (string) ($item['order_type'] ?? $this->resolveOrderType($item['sim_type'] ?? session('sim_type')));
                $canAdjustQuantity = $this->canAdjustQuantity([
                    'sim_type' => $item['sim_type'] ?? session('sim_type'),
                    'service_type' => $serviceType,
                    'order_type' => $orderType,
                ]);
                $planType = $this->normalizePlanType($item['plan_type'] ?? ($item['tType'] ?? null));

                $item['sim_type_label'] = $this->buildSimTypeLabel($serviceType, $orderType);
                $item['plan_type'] = $planType;
                $item['plan_type_label'] = $item['plan_type_label'] ?? $this->buildPlanTypeLabel($planType);
                $item['iccid_count'] = $this->getIccidCount($item);
                $item['dp_info'] = (int) ($item['dp_info'] ?? 0);
                $item['dp_label'] = $item['dp_label'] ?? $this->buildDpLabel((int) ($item['dp_info'] ?? 0));
                $item['can_adjust_quantity'] = $canAdjustQuantity;

                if (!$canAdjustQuantity) {
                    $unitPrice = (float) ($item['ori_price'] ?? $item['price'] ?? 0);
                    $item['qty'] = 1;
                    $item['price'] = $unitPrice;
                    $item['ori_price'] = $unitPrice;
                }

                return $item;
            }, $cart);

            session()->put('roam_order_cart', $normalizedCart);
        }

        return view('frontend.physical.cart');
    }

    private function resolveServiceType(?string $simType): string
    {
        $simType = strtolower((string) $simType);

        return str_contains($simType, 'physical') ? 'physical' : 'esim';
    }

    private function resolveOrderType(?string $simType): string
    {
        $simType = strtolower((string) $simType);

        return str_contains($simType, 'new') ? 'new' : 'recharge';
    }

    private function requiresIccid(array $cartItem): bool
    {
        $orderType = strtolower((string) ($cartItem['order_type'] ?? $this->resolveOrderType($cartItem['sim_type'] ?? session('sim_type'))));
        $simType = strtolower((string) ($cartItem['sim_type'] ?? ''));
        $serviceType = $this->getCartServiceType($cartItem);

        if ($serviceType === 'esim' && $orderType !== 'recharge' && !str_contains($simType, 'recharge')) {
            return false;
        }

        return (bool) ($cartItem['iccid_exist'] ?? false) || $orderType === 'recharge' || str_contains($simType, 'recharge');
    }

    private function getCartServiceType(array $cartItem): string
    {
        $serviceType = strtolower((string) ($cartItem['service_type'] ?? ''));
        if ($serviceType !== '') {
            return $serviceType;
        }

        $simType = strtolower((string) ($cartItem['sim_type'] ?? ''));

        return str_contains($simType, 'physical') ? 'physical' : 'esim';
    }

    private function normalizeApiCode(?string $apiCode): ?string
    {
        $apiCode = trim((string) $apiCode);

        return $apiCode !== '' ? $apiCode : null;
    }

    private function normalizePlanType(?string $planType): string
    {
        $planType = strtolower(trim((string) $planType));
        if ($planType === 'unlimited') {
            return 'Unlimited';
        }
        if ($planType === 'total') {
            return 'Total';
        }

        return 'Daily';
    }

    private function buildPlanTypeLabel(string $planType): string
    {
        return trim($planType) . ' Plan';
    }

    private function resolveCartItemDpInfo(array $cartItem): int
    {
        $skuId = $cartItem['sku_id'] ?? $cartItem['sku'] ?? null;
        if ($skuId) {
            $sku = RoamPhysicalSku::find($skuId);
            $skuDpInfo = (int) ($sku->dp_id ?? 0);

            if ($skuDpInfo > 0) {
                return $skuDpInfo;
            }
        }

        $dpInfo = (int) ($cartItem['dp_info'] ?? 0);
        if ($dpInfo > 0) {
            return $dpInfo;
        }

        return 0;
    }

    private function buildIccidLabel(array $cartItem, string $countryName, ?int $dpInfo = null): string
    {
        $orderType = strtolower((string) ($cartItem['order_type'] ?? ''));
        $serviceType = strtolower((string) (
            $cartItem['service_type']
            ?? $this->resolveServiceType($cartItem['sim_type'] ?? session('sim_type'))
        ));
        $dpInfo = (int) ($dpInfo ?? $cartItem['dp_info'] ?? 0);
        $planName = (string) ($cartItem['dp_label'] ?? $this->buildDpLabel($dpInfo));

        if ($orderType === 'recharge') {
            if ($serviceType === 'esim') {
                $planName = 'FiROAM Esim';
            }

            return '( Recharge ' . $planName . ' - ' . $countryName . ' ) ICCID No';
        }

        return 'ICCID No';
    }

    private function buildDpLabel(int $dpInfo): string
    {
        return $dpInfo === 21 ? 'FiROAM Asia' : 'FiROAM Global';
    }

    private function buildSimTypeLabel(string $serviceType, string $orderType): string
    {
        $serviceType = strtolower(trim($serviceType));
        $orderType = strtolower(trim($orderType));

        $prefix = $orderType === 'recharge' ? 'Recharge' : 'New';
        $suffix = $serviceType === 'physical' ? 'Physical' : 'Esim';

        return trim($prefix . ' ' . $suffix);
    }

    private function canAdjustQuantity(array $cartItem): bool
    {
        $serviceType = strtolower((string) ($cartItem['service_type'] ?? $this->resolveServiceType($cartItem['sim_type'] ?? session('sim_type'))));
        $orderType = strtolower((string) ($cartItem['order_type'] ?? $this->resolveOrderType($cartItem['sim_type'] ?? session('sim_type'))));

        return $serviceType === 'esim' && $orderType === 'new';
    }

    private function getIccidCount(array $cartItem): int
    {
        if (!$this->requiresIccid($cartItem)) {
            return 0;
        }

        return 1;
    }
}
