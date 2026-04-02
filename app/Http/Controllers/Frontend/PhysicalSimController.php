<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RoamPhysical;
use App\Models\RoamPhysicalSku;
use Illuminate\Http\Request;
use App\Models\PriceList;
use App\Models\GeneralSetting;

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
                        return in_array($pkg['priceid'], $priceCodes);
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

    public function roamPhysicalView($skuid)
    {
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
                return in_array($pkg['priceid'], $priceListCodes);
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
}
