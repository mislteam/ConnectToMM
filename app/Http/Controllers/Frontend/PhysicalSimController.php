<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RoamPhysical;
use App\Models\RoamPhysicalSku;
use Illuminate\Http\Request;
use App\Models\PriceList;
use App\Models\GeneralSetting;
use App\Models\RoamSku;

class PhysicalSimController extends Controller
{
    public function roamPhysical()
    {
        $countrys = RoamPhysicalSku::pluck('country_name')
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

                return !empty($validPackage);
            })
            ->values();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();

        return view('frontend.physical.roam-physical', compact('logo', 'title', 'countrys', 'skupackages', 'priceList'));
    }

    public function roamPhysicalSearch(Request $request)
    {
        session([
            'sim_type' => $request->type ?? null,
            'iccid_exist' => true,
            'iccid_no' => $request->iccid_number ?? null
        ]);

        $validated = $request->validate([
            'countryname'   => 'required|array',
            'countryname.*' => 'string'
        ]);

        $skus = RoamPhysicalSku::whereIn('country_name', $validated['countryname'])
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
            ->get();

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
                'iccid_no' => null
            ]);
        }
        $roam = RoamPhysical::where('sku_id', $skuid)->first();

        $packages = $roam->packages;
        //$activePackages = collect($packages)->where('status', 1)->values();

        $sku = RoamPhysicalSku::where('sku_id', $skuid)->first();

        $pricelists = PriceList::where('dp_status', 1)
            ->whereNotNull('dp_info')
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
            ->values();

        $validPackages = $activePackages;

        $hasValidPlans = $activePackages->isNotEmpty();

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $randomSkus = RoamPhysicalSku::where('status', 1)
            ->where('sku_id', '!=', $skuid)
            ->whereIn('sku_id', function ($query) {
                $query->select('plan')
                    ->from('price_lists')
                    ->where('dp_status', 1)
                    ->whereNotNull('dp_info');
            })
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('frontend.physical.roam-physical-package-view', compact(
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
        return view('frontend.physical.cart');
    }

    // joytel checkout
    public function checkout()
    {
        $cart = session('roam_cart');
        if (!$cart) {
            return redirect()->back()->with('error', 'Cart is Empty!');
        }
        $sku = RoamSku::findOrFail($cart['sku']);
        return view('frontend.physical.checkout', [
            'sku' => $sku,
            'service_day' => $cart['service_day'],
            'service_data' => $cart['service_data'],
            'qty' => $cart['qty'],
            'price' => $cart['price']
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
}
