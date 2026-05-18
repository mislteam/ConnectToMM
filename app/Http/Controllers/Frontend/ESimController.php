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
        if ($request->has('list_view') && session('sim_type') !== 'recharge_esim') {
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
            'original_selected_price' => 'required',
            'api_code' => 'nullable|string'
        ]);

        $sku = RoamSku::where('sku_id', $request->skuid)->first();
        // dd(session()->all());
        $iccid_no = session()->get('iccid_no');
        $iccid_exist = session()->get('iccid_exist');
        $service_type = $this->resolveServiceType($sim_type);
        $order_type = $this->resolveOrderType($sim_type);
        $apiCode = $this->normalizeApiCode($request->input('api_code'));
        $simTypeLabel = $this->buildSimTypeLabel($service_type, $order_type);

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
                'sim_type_label' => $simTypeLabel,
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

        $serviceItems = $cartItems
            ->filter(fn($item) => $this->getCartServiceType($item) === 'esim')
            ->values();

        if ($serviceItems->isEmpty()) {
            return redirect()->back()->with('error', 'No eSIM items found in cart!');
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
            'iccid_label' => $this->buildIccidLabel($primaryItem, (string) $sku->country_name),
            'sim_type_label' => $this->buildSimTypeLabel(
                (string) ($primaryItem['service_type'] ?? ''),
                (string) ($primaryItem['order_type'] ?? '')
            ),
        ]);
    }

    public function cartPage()
    {
        $cart = session()->get('roam_order_cart', []);

        if (is_array($cart) && !empty($cart)) {
            $normalizedCart = array_map(function (array $item) {
                $serviceType = (string) ($item['service_type'] ?? $this->resolveServiceType($item['sim_type'] ?? session('sim_type')));
                $orderType = (string) ($item['order_type'] ?? $this->resolveOrderType($item['sim_type'] ?? session('sim_type')));

                $item['sim_type_label'] = $item['sim_type_label'] ?? $this->buildSimTypeLabel($serviceType, $orderType);

                return $item;
            }, $cart);

            session()->put('roam_order_cart', $normalizedCart);
        }

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

    private function requiresIccid(array $cartItem): bool
    {
        $orderType = strtolower((string) ($cartItem['order_type'] ?? $this->resolveOrderType($cartItem['sim_type'] ?? session('sim_type'))));
        $simType = strtolower((string) ($cartItem['sim_type'] ?? session('sim_type') ?? ''));

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

    private function buildIccidLabel(array $cartItem, string $countryName): string
    {
        $orderType = strtolower((string) ($cartItem['order_type'] ?? ''));
        $serviceType = strtolower((string) ($cartItem['service_type'] ?? ''));

        if ($orderType === 'recharge' && $serviceType === 'physical') {
            return 'ICCID No For ' . $countryName . ' Recharge Physical';
        }

        if ($orderType === 'recharge') {
            return 'ICCID No For ' . $countryName . ' Recharge';
        }

        return 'ICCID No';
    }

    private function buildSimTypeLabel(string $serviceType, string $orderType): string
    {
        $serviceType = strtolower(trim($serviceType));
        $orderType = strtolower(trim($orderType));

        $prefix = $orderType === 'recharge' ? 'Recharge' : 'New';
        $suffix = $serviceType === 'physical' ? 'Physical' : 'Esim';

        return trim($prefix . ' ' . $suffix);
    }
}
