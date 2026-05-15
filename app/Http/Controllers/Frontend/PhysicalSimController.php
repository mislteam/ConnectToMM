<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RoamPhysical;
use App\Models\RoamPhysicalSku;
use App\Models\RoamOrder;
use Illuminate\Http\Request;
use App\Models\PriceList;
use App\Models\GeneralSetting;
use App\Services\Roam\RoamOrderService;

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
        session(['iccid_no' => $request->iccid_number]);
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

        $selectedDpInfo = $pricelists->pluck('dp_info')
            ->filter()
            ->unique()
            ->first();

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
}
