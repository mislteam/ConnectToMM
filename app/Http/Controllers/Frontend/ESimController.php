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
                        return in_array($pkg['priceid'], $priceCodes);
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


    // public function roamView($skuid,$skus)
    // {

    //     $roam=Roam::where('sku_id',$skuid)->first();

    //     $packages =$roam->packages;
    //     $activePackages = collect($packages)->where('status', 1)->values()->all();
    //     // dd($activePackages);
    //     $sku=RoamSku::where('sku_id',$skuid)->first();

    //     $skus = json_decode($skus, true);
    //     $skus = array_filter($skus, fn($s) => $s != $skuid);
    //     $skupackages = RoamSku::whereIn('sku_id', $skus)->where('status',1)->get();

    //     $currencies = Currency::all(); 
    //     $pricelists = PriceList::all(); 
    //     $usd_exchange_rate = Currency::where('name', 'usd')->value('value');
    //     $profit = Currency::where('name', 'profit')->value('value');



    //     // Example: take the first active package price
    //     // $basePrice = $activePackages[0]['price'] ?? 0;

    //     // $currency = $currencies->first(function ($c) use ($basePrice) {
    //     //     return $basePrice >= $c->mini_amount && $basePrice <= $c->max_amount;
    //     // });

    //     // $mmk_price = $currency ? $basePrice * $currency->mmk : 0;


    //     return view('frontend.esim.roam-package-view',compact('usd_exchange_rate','profit','skupackages','skus','activePackages','sku','roam','pricelists','currencies'));
    // }

    public function roamView($skuid)
    {
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
                return in_array($pkg['priceid'], $priceListCodes);
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
}
