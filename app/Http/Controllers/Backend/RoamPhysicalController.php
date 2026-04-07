<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\RoamPhysicalSku;
use App\Models\RoamApi;
use App\Models\Currency;
use App\Models\RoamPhysical;
use App\Models\GeneralSetting;
use App\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoamPhysicalController extends Controller
{
    public function Physicalindex()
    {
        $globalPackages = RoamPhysicalSku::where('status', 1)
            ->where('dp_id', 9)
            ->get();
        $asiaPackages = RoamPhysicalSku::where('status', 1)
            ->where('dp_id', 21)
            ->get();
        $packages = RoamPhysicalSku::where('status', 1)->get();

        $usd_exchange_rate = Currency::where('name', 'usd')->value('value');
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamphysical.packages.physicalsim', compact('logo', 'title', 'packages', 'globalPackages', 'asiaPackages','usd_exchange_rate'));
    }

    public function RoamphysicalEdit($skuid)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $roam = RoamPhysical::where('sku_id', $skuid)->firstOrFail();
        //dd($roam);
        $name = RoamPhysicalSku::where('sku_id', $skuid)->firstOrFail();
        return view('admin.roamphysical.packages.edit-roamphysical', compact('roam', 'title', 'logo', 'name'));
    }


    public function Skuindex()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $roamGlobal = RoamPhysicalSku::where('dp_id', 9)->get();
        $roamAsia = RoamPhysicalSku::where('dp_id', 21)->get();
        // $categories=Category::latest()->get();
        return view('admin.roamphysical.skulist', compact('logo', 'title', 'roamGlobal', 'roamAsia'));
    }


    public function syncPhysicalSkusAndPackages()
    {
        $roamapi = RoamApi::first();
        if (!$roamapi) {
            return response()->json(['error' => 'Missing API credentials'], 400);
        }


        $loginParams = [
            'phonenumber' => $roamapi->client_id,
            'password'    => $roamapi->secret_key,
        ];

        $loginParams['sign'] = $this->createSign($loginParams, $roamapi->client_key);

        $loginResponse = Http::timeout(160)->asForm()->post(
            $roamapi->api_url . '/api_order/login',
            $loginParams
        );

        $loginData = $loginResponse->json();

        if (!isset($loginData['data']['token'])) {
            return response()->json(['error' => 'Login failed'], 400);
        }

        $token = $loginData['data']['token'];


        // GET PHYSICAL SIM DP LIST

        $dpSign = $this->createTokenSign($token, $roamapi->client_key);

        $dpResponse = Http::timeout(160)->asForm()->post(
            $roamapi->api_url . '/api_esim/getPhysicalSIMDpInfo',
            [
                'token' => $token,
                'sign'  => $dpSign,
            ]
        );

        $dpData = $dpResponse->json();
        // dd($dpData);

        if (!isset($dpData['data']) || !is_array($dpData['data'])) {
            return response()->json(['error' => 'No DP found'], 400);
        }

        $newSkus = [];
        $newPackages = [];

        //  LOOP DPs → SKUs → PACKAGES

        foreach ($dpData['data'] as $dp) {


            $dpId = $dp['id'];
            $dpName = $dp['realName'];

            // Get SKUs by DP
            $skuParams = [
                'token' => $token,
                'dpId'  => $dpId,
            ];

            $skuSign = $this->createSign($skuParams, $roamapi->client_key);

            $skuResponse = Http::timeout(160)->asForm()->post(
                $roamapi->api_url . '/api_esim/getDpSupportSkuInfo',
                array_merge($skuParams, ['sign' => $skuSign])
            );

            $skuData = $skuResponse->json();
            // dd($dpId , $skuData);

            if (!isset($skuData['data']) || !is_array($skuData['data'])) {
                continue;
            }

            foreach ($skuData['data'] as $sku) {

                // Save SKU 
                $existingSku = RoamPhysicalSku::where('sku_id', $sku['skuid'])->where('dp_id', $dpId)->first();

                $skuRecord = RoamPhysicalSku::updateOrCreate(
                    [
                        'sku_id' => $sku['skuid'],
                        'dp_id'  => $dpId,
                    ],
                    [
                        'country_name' => $sku['display'] ?? 'N/A',
                        'country_code' => $sku['countryCode'] ?? 'N/A',
                        'status'       => $existingSku ? $existingSku->status : 1,
                    ]
                );
                $skuRecord->dp_name = $dpName;
                //    dd($skuRecord->dp_name);

                if (!$existingSku) {
                    $newSkus[] = $skuRecord;
                }

                //    Get Packages
                $pkgParams = [
                    'token' => $token,
                    'dpId'  => $dpId,
                    'skuid' => $sku['skuid'],
                ];

                $pkgSign = $this->createSign($pkgParams, $roamapi->client_key);

                $pkgResponse = Http::timeout(120)
                    ->retry(3, 2000)
                    ->asForm()
                    ->post(
                        $roamapi->api_url . '/api_esim/getDpSkuSupportPackageInfo',
                        array_merge($pkgParams, ['sign' => $pkgSign])
                    );

                $pkgData = $pkgResponse->json();
                // dd($pkgData);
                $pkgList = $pkgData['data']['esimPackageVoList'] ?? [];
                // dd($pkgList);

                if (empty($pkgList)) {
                    continue;
                }

                //    Merge Packages
                $old = RoamPhysical::where('sku_id', $sku['skuid'])->where('dp_id', $dpId)->first();
                $oldPackages = $old ? $old->packages : [];

                $existingPids = collect($oldPackages)->pluck('pid')->all();
                $finalPackages = $oldPackages;

                foreach ($pkgList as $pkg) {
                    if (in_array($pkg['pid'], $existingPids)) {
                        continue;
                    }

                    $pkg['status'] = 1;
                    $pkg['is_new'] = true;

                    // $pkg['dp_id']  = $dpId;
                    $pkg['dp_name']  = $dpName;

                    $finalPackages[] = $pkg;
                    $newPackages[]  = $pkg;
                }
                // dd($pkg);

                // If no packages exist → do not save anything
                if (empty($finalPackages)) {
                    return redirect()
                        ->route('physical.updateData')
                        ->with('success', 'SKUs and packages synced successfully.')
                        ->with('newSkus', $newSkus)
                        ->with('newPackages', $newPackages);
                }

                RoamPhysical::updateOrCreate(
                    [
                        'sku_id' => $sku['skuid'],
                        'dp_id'  => $dpId,
                    ],
                    [
                        'packages'        => $finalPackages,
                        'support_country' => $pkgData['data']['supportCountry'] ?? [],
                        'image'           => $pkgData['data']['imageUrl'] ?? null,
                    ]
                );
            }
        }

        return redirect()
            ->route('physical.updateData')
            ->with('success', 'Physical SIM SKUs & packages synced successfully')
            ->with('newSkus', $newSkus)
            ->with('newPackages', $newPackages);
    }

    private function createSign(array $data, string $clientKey): string
    {
        unset($data['sign']);
        ksort($data);

        $plainText = '';
        foreach ($data as $key => $value) {
            $plainText .= $key . '=' . $value;
        }

        return md5($plainText . $clientKey);
    }


    private function createTokenSign(string $token, string $clientKey): string
    {
        return md5("token={$token}" . $clientKey);
    }



    // chaeck status
    public function toggleStatus($skuid)
    {
        $sku = RoamPhysicalSku::where('sku_id', $skuid)->first();
        $sku->status = $sku->status ? 0 : 1;
        $sku->save();

        return response()->json([
            'status' => true,
            'new_status' => $sku->status
        ]);
    }




    //for roam physical update

    public function update(Request $request, $id)
    {

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:0,1'
        ]);
        $roamphysical = RoamPhysical::findOrFail($id);


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/upload/roamphysical', $filename);


            if ($roamphysical->image && Storage::exists('public/upload/roamphysical/' . $roamphysical->image)) {
                Storage::delete('public/upload/roamphysical/' . $roamphysical->image);
            }

            $roamphysical->image = $filename;
        }

        $roamphysical->save();


        $roamphysicalSku = RoamPhysicalSku::where('sku_id', $roamphysical->sku_id)->first();
        if ($roamphysicalSku) {
            $roamphysicalSku->status = $request->status;
            $roamphysicalSku->save();
        }

        return redirect()->route('roamphysical.Index')->with('success', 'Roam data updated successfully!');
    }





    // For manage status

    public function updatePackageStatus(Request $request)
    {
        $roamphysical = RoamPhysical::where('sku_id', $request->sku_id)->first();

        if (!$roamphysical) {
            return back()->with('error', 'Roam package not found.');
        }

        $packages = $roamphysical->packages; // JSON column cast to array
        $index = $request->index;

        if (isset($packages[$index])) {

            $packages[$index]['status'] = $request->input('status', 0);


            $roamphysical->packages = $packages;
            $roamphysical->save();
        }

        return redirect()
            ->route('roamphysical.Index')
            ->with('success', 'Package status updated.');
    }

    // For manage price
    // public function pricestore(Request $request)
    // {
    //     dd($request->all());
    //     if ($request->has('plans')) {
    //         foreach ($request->plans as $plan) {
    //             $priceid = $plan['priceid'] ?? null;
    //             $price = $plan['price'] ?? null;
    //             $original = $plan['original'] ?? null;

    //             if (!$priceid) {
    //                 continue;
    //             }

    //             if (empty($price) || $price == $original) {
    //                 continue;
    //             }

    //             \App\Models\PriceList::updateOrCreate(
    //                 ['product_code' => $priceid],
    //                 [
    //                     'price' => $price,
    //                     'increment' => null,
    //                 ]
    //             );
    //         }
    //     }

    //     return back()->with('success', 'Prices saved successfully!');
    // }

    public function updatePhysicalExchangeRate(Request $request)
    {
        //dd($request->all());
        if ($request->has('plans')) {

            foreach ($request->plans as $plan) {
                $priceid = $plan['priceid'] ?? null;
                $sellingRate = $plan['selling_rate'] ?? null;
                $profit      = $plan['profit'] ?? 0;
                $dpName = $plan['dp_name'] ?? null;


                $skuId = $plan['sku_id'] ?? null;

                // skip invalid
                if (!$priceid || !$sellingRate || $sellingRate == 0) {
                    continue;
                }

                // default
                $dpStatus = 0;
                $dpInfo = null;

                // map dp_name
                if ($dpName === 'FiROAM GLOBAL') {
                    $dpStatus = 1;
                    $dpInfo = 9;
                } elseif ($dpName === 'FiROAM ASIA') {
                    $dpStatus = 1;
                    $dpInfo = 21;
                }

                PriceList::updateOrCreate(
                    [
                        'product_code' => $priceid,
                        'dp_status' => $dpStatus,
                        'dp_info' => $dpInfo,
                        'plan' => $skuId
                    ],
                    [
                         'exchange_rate' => $sellingRate,
                         'profit'        => $profit,
                    ]
                );
            }
        }
        return back()->with('success', 'Exchange rates saved successfully!');
    }

    public function UpdateData()
    {

        $newSkus = session('newSkus', []);
        $newPackages = session('newPackages', []);
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamphysical.update-data', compact('logo', 'title', 'newSkus', 'newPackages'));
    }
}
