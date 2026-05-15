<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RoamSku;
use App\Models\Roam;
use App\Models\PriceList;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;

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
            ->get();

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
            ->take(3)
            ->get();

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
        $sku = RoamSku::findOrFail($cart['sku']);
        return view('frontend.esim.checkout', [
            'sku' => $sku,
            'service_day' => $cart['service_day'],
            'service_data' => $cart['service_data'],
            'qty' => $cart['qty'],
            'price' => $cart['price']
        ]);
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
