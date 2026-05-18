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

class PhysicalSimController extends Controller
{
    public function roamPhysical(Request $request)
    {
        $selectedDpId = (int) $request->query('dp_id', 9);
        if (!in_array($selectedDpId, [9, 21], true)) {
            $selectedDpId = 9;
        }

        $countrys = RoamPhysicalSku::where('dp_id', $selectedDpId)
            ->where('status', 1)
            ->pluck('country_name')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        $priceList = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->get();

        $skupackages = RoamPhysicalSku::where('status', 1)
            ->get()
            ->filter(function ($sku) use ($priceList) {

                $roam = RoamPhysical::where('sku_id', $sku->sku_id)->first();
                if (!$roam || empty($roam->packages)) return false;

                // price list codes for this sku
                $priceCodes = $priceList
                    ->where('plan', $sku->sku_id)
                    ->pluck('product_code')
                    ->toArray();

                // check valid package
                $validPackage = collect($roam->packages)
                    ->where('status', 1)
                    ->first(function ($pkg) use ($priceCodes) {
                        $apiCode = $pkg['apiCode'] ?? $pkg['api_code'] ?? null;
                        $legacyCode = $pkg['priceid'] ?? null;

                        return (
                            ($apiCode !== null && in_array($apiCode, $priceCodes)) ||
                            ($legacyCode !== null && in_array($legacyCode, $priceCodes))
                        );
                    });

                if (empty($validPackage)) {
                    return false;
                }

                return $this->getLowestPhysicalPrice($sku->sku_id, $priceList) !== null;
            })
            ->values();

        $globalSkupackages = $skupackages->where('dp_id', 9)->values();
        $asiaSkupackages = $skupackages->where('dp_id', 21)->values();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view(
            'frontend.physical.roam-physical',
            compact(
                'logo',
                'title',
                'countrys',
                'skupackages',
                'globalSkupackages',
                'asiaSkupackages',
                'priceList',
                'selectedDpId'
            )
        );
    }

    public function roamPhysicalSearch(Request $request)
    {
        session([
            'sim_type' => $request->type ?? null,
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
        if ($request->list_view) {
            session([
                'iccid_exist' => true,
                'iccid_no' => null,
                'sim_type' => 'recharge_physical'
            ]);
        }
        $sku = RoamPhysicalSku::where('sku_id', $skuid)->firstOrFail();
        $selectedDpInfo = (int) ($sku->dp_id ?? 0);

        $roam = RoamPhysical::where('sku_id', $skuid)->firstOrFail();
        $packages = $roam->packages;
        //$activePackages = collect($packages)->where('status', 1)->values();

        $pricelists = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->where('plan', $skuid)
            ->when($selectedDpInfo, function ($query) use ($selectedDpInfo) {
                $query->where('dp_info', $selectedDpInfo);
            })
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
            'selectedDpInfo'
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

        $priceMap = $priceList->pluck('exchange_rate', 'product_code');

        $roam = RoamPhysical::where('sku_id', $skuId)->first();
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

    private function getPackagePlanPrice(string|int $skuId, array $pkg): ?float
    {
        $priceList = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
            ->where('plan', $skuId)
            ->get();

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
        $sim_type = session()->get('sim_type');
        $request->validate([
            'skuid' => 'required',
            'sday' => 'required',
            'sdata' => 'required',
            'display_price' => 'required',
            'qty' => 'required',
            'original_selected_price' => 'required',
            'api_code' => 'nullable|string'
        ]);
        $sku = RoamSku::where('sku_id', $request->skuid)->first();
        $iccid_no = session()->get('iccid_no');
        $iccid_exist = session()->get('iccid_exist');
        $service_type = $this->resolveServiceType($sim_type);
        $order_type = $this->resolveOrderType($sim_type);
        $apiCode = $this->normalizeApiCode($request->input('api_code'));

        $roamCart = session()->get('roam_order_cart', []);
        $found = false;
        foreach ($roamCart as &$item) {
            if (
                $item['country_name'] == $sku->country_name
                && $item['service_day'] == $request->sday
                && $item['service_data'] == $request->sdata
                && $this->normalizeApiCode($item['api_code'] ?? null) === $apiCode
                && $item['iccid_no'] == $iccid_no
                && $item['iccid_exist'] == $iccid_exist
                && ($item['sim_type'] ?? null) == $sim_type
                && ($item['service_type'] ?? null) == $service_type
                && ($item['order_type'] ?? null) == $order_type
            ) {
                $item['qty'] += $request->qty;
                $item['price'] += $request->display_price;
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
                'api_code' => $apiCode,
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

        $serviceItems = $cartItems
            ->filter(fn($item) => $this->getCartServiceType($item) === 'physical')
            ->values();

        if ($serviceItems->isEmpty()) {
            return redirect()->back()->with('error', 'No physical SIM items found in cart!');
        }

        $simType = session('sim_type');
        $typedItems = $simType
            ? $serviceItems->filter(fn($item) => (string) ($item['sim_type'] ?? '') === (string) $simType)->values()
            : collect();
        $itemsToUse = $typedItems->isNotEmpty() ? $typedItems : $serviceItems;
        $primaryItem = $itemsToUse->last() ?: $itemsToUse->first();
        $skuId = $primaryItem['sku_id'] ?? $primaryItem['sku'] ?? null;

        if (!$skuId) {
            return redirect()->back()->with('error', 'Cart data is invalid!');
        }

        $sku = RoamSku::findOrFail($skuId);
        $price = (float) ($primaryItem['price'] ?? 0);

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
            'iccid_label' => $this->buildIccidLabel($primaryItem, (string) $sku->country_name),
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
        return view('frontend.physical.cart');
    }

    private function resolveServiceType(?string $simType): string
    {
        $simType = strtolower((string) $simType);

        return str_contains($simType, 'physical') ? 'physical' : 'esim';
    }

    private function resolveOrderType(?string $simType): string
    {
        return 'recharge';
    }

    private function requiresIccid(array $cartItem): bool
    {
        return true;
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

    private function buildIccidLabel(array $cartItem, string $countryName): string
    {
        $serviceType = strtolower((string) ($cartItem['service_type'] ?? ''));

        if ($serviceType === 'physical') {
            return 'ICCID No For ' . $countryName . ' Recharge Physical';
        }

        return 'ICCID No For ' . $countryName . ' Recharge';
    }
}
