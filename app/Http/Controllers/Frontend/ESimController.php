<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RoamSku;
use App\Models\Roam;
use App\Models\PriceList;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;

class ESimController extends Controller
{
    public function roam()
    {
        $selectedSimType = $this->normalizeSimType(request()->query('type', session('sim_type', 'new_esim')));
        if (!in_array($selectedSimType, ['new_esim', 'recharge_esim'], true)) {
            $selectedSimType = 'new_esim';
        }

        $orderTabs = [
            'new_esim' => ['label' => 'New eSIM'],
            'recharge_esim' => ['label' => 'Recharge'],
        ];

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

        $packageCards = $this->buildEsimPackageCards($skupackages, $priceList);

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view('frontend.esim.roam', compact(
            'logo',
            'title',
            'countrys',
            'orderTabs',
            'selectedSimType',
            'skupackages',
            'packageCards',
            'priceList'
        ));
    }

    //for roam
    public function roamSearch(Request $request)
    {
        $simType = $this->normalizeSimType($request->input('type', session('sim_type', 'new_esim')));
        if (!in_array($simType, ['new_esim', 'recharge_esim'], true)) {
            $simType = 'new_esim';
        }

        session(['sim_type' => $simType]);
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
        $packageCards = $this->buildEsimPackageCards($packages, $priceList);

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view('frontend.esim.roam-package', compact('logo', 'title', 'packages', 'skus', 'priceList', 'packageCards'));
    }

    public function roamView($skuid, Request $request)
    {
        $simType = $this->normalizeSimType($request->input('sim_type', session('sim_type', 'new_esim')));

        session(['sim_type' => $simType]);
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
        $canAdjustQuantity = $this->canAdjustQuantity([
            'sim_type' => $simType,
            'service_type' => 'esim',
            'order_type' => $this->resolveOrderType($simType),
        ]);

        return view('frontend.esim.roam-package-view', compact(
            'logo',
            'title',
            'sku',
            'roam',
            'activePackages',
            'validPackages',
            'pricelists',
            'hasValidPlans',
            'randomSkus',
            'canAdjustQuantity',
            'simType'
        ));
    }

    public function cart(Request $request)
    {
        // session()->forget('roam_order_cart');
        // dd('hi');
        $sim_type = $this->normalizeSimType($request->input('sim_type', session()->get('sim_type')));
        session(['sim_type' => $sim_type]);
        $request->validate([
            'skuid' => 'required',
            'tType' => 'nullable|string|in:Daily,Unlimited,Total',
            'sday' => 'required',
            'sdata' => 'required',
            'display_price' => 'required',
            'qty' => 'required',
            'original_selected_price' => 'required',
            'api_code' => 'nullable|string',
            'plan_name' => 'nullable|string'
        ]);

        $sku = RoamSku::where('sku_id', $request->skuid)->first();
        // dd(session()->all());
        $service_type = $this->resolveServiceType($sim_type);
        $order_type = $this->resolveOrderType($sim_type);
        $requiresIccid = $order_type === 'recharge' || str_contains(strtolower((string) $sim_type), 'recharge');
        $iccid_no = $requiresIccid ? session()->get('iccid_no') : null;
        $iccid_exist = $requiresIccid ? (bool) session()->get('iccid_exist') : false;

        if (!$requiresIccid) {
            session()->forget([
                'iccid_exist',
                'iccid_no',
            ]);
        }
        $apiCode = $this->normalizeApiCode($request->input('api_code'));
        $planType = $this->normalizePlanType($request->input('tType'));
        $planName = trim((string) $request->input('plan_name', ''));
        $simTypeLabel = $this->buildSimTypeLabel($service_type, $order_type);
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
            ) {
                $itemUnitPrice = (float) ($item['ori_price'] ?? $unitPrice);

                if ($canAdjustQuantity) {
                    $item['qty'] = (int) ($item['qty'] ?? 0) + $requestedQty;
                } else {
                    $item['qty'] = 1;
                }

                $item['ori_price'] = $itemUnitPrice;
                $item['price'] = $itemUnitPrice * (int) $item['qty'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $roamCart[] = [
                'sku' => $sku->id,
                'sku_id' => $sku->id,
                'sim_type' => $sim_type,
                'sim_type_label' => $simTypeLabel,
                'service_type' => $service_type,
                'order_type' => $order_type,
                'api_code' => $apiCode,
                'plan_type' => $planType,
                'plan_type_label' => $this->buildPlanTypeLabel($planType),
                'plan_name' => $planName !== '' ? $planName : null,
                'country_name' => $sku->country_name,
                'service_day' => $request->sday,
                'service_data' => $request->sdata,
                'qty' => $qty,
                'price' => $price,
                'iccid_no' => $iccid_no,
                'iccid_exist' => $iccid_exist,
                'ori_price' => $unitPrice
            ];
        }

        session(['roam_order_cart' => $roamCart]);

        return redirect()->route('roam.esim.cartpage');
    }

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
            $serviceType = (string) ($item['service_type'] ?? $this->resolveServiceType($item['sim_type'] ?? 'new_esim'));
            $orderType = (string) ($item['order_type'] ?? $this->resolveOrderType($item['sim_type'] ?? 'new_esim'));
            $canAdjustQuantity = $this->canAdjustQuantity([
                'sim_type' => $item['sim_type'] ?? 'new_esim',
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

            $item['sim_type_label'] = $item['sim_type_label'] ?? $this->buildSimTypeLabel($serviceType, $orderType);
            $item['plan_type'] = $planType;
            $item['plan_type_label'] = $item['plan_type_label'] ?? $this->buildPlanTypeLabel($planType);
            $item['iccid_count'] = $this->getIccidCount($item);
            $item['iccid_label'] = $this->buildIccidLabel(
                $item,
                (string) ($item['country_name'] ?? ''),
                (int) ($item['dp_info'] ?? 0)
            );

            return $item;
        })->values();

        $primaryItem = $itemsToUse->first();
        $skuId = $primaryItem['sku_id'] ?? $primaryItem['sku'] ?? null;

        if (!$skuId) {
            return redirect()->back()->with('error', 'Cart data is invalid!');
        }

        $sku = RoamSku::findOrFail($skuId);
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

        return view('frontend.esim.checkout', [
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
            'sim_type_label' => $this->buildSimTypeLabel(
                (string) ($primaryItem['service_type'] ?? ''),
                (string) ($primaryItem['order_type'] ?? '')
            ),
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

    public function cartPage()
    {
        $cart = session()->get('roam_order_cart', []);
        $normalizedCart = $cart;

        if (is_array($cart) && !empty($cart)) {
            $normalizedCart = array_map(function (array $item) {
                $serviceType = (string) ($item['service_type'] ?? $this->resolveServiceType($item['sim_type'] ?? 'new_esim'));
                $orderType = (string) ($item['order_type'] ?? $this->resolveOrderType($item['sim_type'] ?? 'new_esim'));
                $canAdjustQuantity = $this->canAdjustQuantity([
                    'sim_type' => $item['sim_type'] ?? 'new_esim',
                    'service_type' => $serviceType,
                    'order_type' => $orderType,
                ]);
                $planType = $this->normalizePlanType($item['plan_type'] ?? ($item['tType'] ?? null));

                $item['service_type'] = $serviceType;
                $item['order_type'] = $orderType;
                $item['sim_type_label'] = $item['sim_type_label'] ?? $this->buildSimTypeLabel($serviceType, $orderType);
                $item['plan_type'] = $planType;
                $item['plan_type_label'] = $item['plan_type_label'] ?? $this->buildPlanTypeLabel($planType);
                $item['iccid_count'] = $this->getIccidCount($item);
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

        return view('frontend.esim.cart', [
            'cartItems' => $normalizedCart,
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

    private function resolveServiceType(?string $simType): string
    {
        $simType = strtolower((string) $simType);

        return str_contains($simType, 'physical') ? 'physical' : 'esim';
    }

    private function resolveOrderType(?string $simType): string
    {
        $simType = strtolower((string) $simType);

        return str_contains($simType, 'recharge') ? 'recharge' : 'new';
    }

    private function canAdjustQuantity(array $cartItem): bool
    {
        $serviceType = strtolower((string) ($cartItem['service_type'] ?? $this->resolveServiceType($cartItem['sim_type'] ?? 'new_esim')));
        $orderType = strtolower((string) ($cartItem['order_type'] ?? $this->resolveOrderType($cartItem['sim_type'] ?? 'new_esim')));

        return $serviceType === 'esim' && $orderType === 'new';
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

    private function buildEsimPackageCards($packages, $priceList)
    {
        return collect($packages)
            ->map(function ($package) use ($priceList) {
                $roam = Roam::where('sku_id', $package->sku_id)->first();
                if (!$roam || empty($roam->packages)) {
                    return null;
                }

                $priceMap = $priceList
                    ->where('plan', $package->sku_id)
                    ->pluck('exchange_rate', 'product_code');

                $lowestPrice = collect($roam->packages)
                    ->filter(fn($pkg) => ($pkg['status'] ?? 0) == 1)
                    ->map(function ($pkg) use ($priceMap) {
                        $apiCode = $pkg['apiCode'] ?? ($pkg['api_code'] ?? null);
                        $legacyCode = $pkg['priceid'] ?? null;

                        $rate = ($apiCode !== null && isset($priceMap[$apiCode]))
                            ? $priceMap[$apiCode]
                            : (($legacyCode !== null && isset($priceMap[$legacyCode])) ? $priceMap[$legacyCode] : null);

                        if ($rate === null) {
                            return null;
                        }

                        $portalPrice = (float) ($pkg['price'] ?? 0) + (float) ($pkg['openCardFee'] ?? 0);

                        return $portalPrice * (float) $rate;
                    })
                    ->filter()
                    ->min();

                if (!$lowestPrice) {
                    return null;
                }

                return [
                    'package' => $package,
                    'roam' => $roam,
                    'lowest_price' => (float) $lowestPrice,
                ];
            })
            ->filter()
            ->values();
    }

    private function requiresIccid(array $cartItem): bool
    {
        $orderType = strtolower((string) ($cartItem['order_type'] ?? $this->resolveOrderType($cartItem['sim_type'] ?? 'new_esim')));
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

    private function buildIccidLabel(array $cartItem, string $countryName, ?int $dpInfo = null): string
    {
        $orderType = strtolower((string) ($cartItem['order_type'] ?? ''));
        $serviceType = strtolower((string) (
            $cartItem['service_type']
            ?? $this->resolveServiceType($cartItem['sim_type'] ?? 'new_esim')
        ));
        $dpInfo = (int) ($dpInfo ?? $cartItem['dp_info'] ?? 0);

        if ($orderType === 'recharge') {
            if ($serviceType === 'physical') {
                $labelPlan = $dpInfo === 21 ? 'FiROAM Asia' : 'FiROAM Global';
            } else {
                $planName = strtolower(trim((string) ($cartItem['plan_name'] ?? '')));
                $labelPlan = 'FiROAM Esim';

                if (str_contains($planName, 'asia')) {
                    $labelPlan = 'FiROAM Asia';
                } elseif (str_contains($planName, 'global')) {
                    $labelPlan = 'FiROAM Global';
                } elseif (str_contains($planName, 'esim')) {
                    $labelPlan = 'FiROAM Esim';
                }
            }

            return '( Recharge ' . $labelPlan . ' - ' . $countryName . ' ) ICCID No';
        }

        return 'ICCID No';
    }

    private function getIccidCount(array $cartItem): int
    {
        if (!$this->requiresIccid($cartItem)) {
            return 0;
        }

        return 1;
    }

    private function buildSimTypeLabel(string $serviceType, string $orderType): string
    {
        $serviceType = strtolower(trim($serviceType));
        $orderType = strtolower(trim($orderType));

        $prefix = $orderType === 'recharge' ? 'Recharge' : 'New';
        $suffix = $serviceType === 'physical' ? 'Physical' : 'Esim';

        return trim($prefix . ' ' . $suffix);
    }

    private function normalizeSimType(?string $simType): string
    {
        $simType = strtolower(trim((string) $simType));

        if ($simType === '' || !in_array($simType, ['new_esim', 'recharge_esim', 'new_physical', 'recharge_physical'], true)) {
            return 'new_esim';
        }

        return $simType;
    }
}
