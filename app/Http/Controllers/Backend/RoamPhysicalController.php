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
use Illuminate\Support\Facades\Log;
use Throwable;

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
        return view('admin.roamphysical.packages.physicalsim', compact('logo', 'title', 'packages', 'globalPackages', 'asiaPackages', 'usd_exchange_rate'));
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
        try {
            @set_time_limit(0);

            $roamapi = RoamApi::first();
            if (!$roamapi) {
                return redirect()->route('physical.updateData')->with('error', 'Missing API credentials.');
            }

            $loginParams = [
                'phonenumber' => $roamapi->client_id,
                'password'    => $roamapi->secret_key,
            ];
            $loginParams['sign'] = $this->createSign($loginParams, $roamapi->client_key);

            $loginResponse = Http::timeout(40)
                ->retry(2, 1200)
                ->asForm()
                ->post($roamapi->api_url . '/api_order/login', $loginParams);

            $loginData = $loginResponse->json();
            Log::debug('Roam physical login response payload', [
                'status' => $loginResponse->status(),
                'payload' => $loginData,
            ]);
            if (!isset($loginData['data']['token'])) {
                return redirect()->route('physical.updateData')->with('error', 'Login failed.');
            }

            $token = $loginData['data']['token'];

            // GET PHYSICAL SIM DP LIST
            $dpSign = $this->createTokenSign($token, $roamapi->client_key);
            $dpResponse = Http::timeout(40)
                ->retry(2, 1200)
                ->asForm()
                ->post(
                    $roamapi->api_url . '/api_esim/getPhysicalSIMDpInfo',
                    [
                        'token' => $token,
                        'sign'  => $dpSign,
                    ]
                );

            $dpData = $dpResponse->json();
            Log::debug('Roam physical dp response payload', [
                'status' => $dpResponse->status(),
                'payload' => $dpData,
            ]);
            if (!isset($dpData['data']) || !is_array($dpData['data'])) {
                return redirect()->route('physical.updateData')->with('error', 'No DP found.');
            }

            $newSkus = [];
            $updatedSkus = [];
            $newPackages = [];
            $updatedPackages = [];

            // LOOP DPs → SKUs → PACKAGES
            foreach ($dpData['data'] as $dp) {
                $dpId = $dp['id'] ?? null;
                $dpName = $dp['realName'] ?? null;
                if (!$dpId) {
                    continue;
                }

                $skuParams = [
                    'token' => $token,
                    'dpId'  => $dpId,
                ];
                $skuSign = $this->createSign($skuParams, $roamapi->client_key);
                $skuResponse = Http::timeout(40)
                    ->retry(2, 1200)
                    ->asForm()
                    ->post(
                        $roamapi->api_url . '/api_esim/getDpSupportSkuInfo',
                        array_merge($skuParams, ['sign' => $skuSign])
                    );

                Log::debug('Roam physical sku response payload', [
                    'dp_id' => $dpId,
                    'status' => $skuResponse->status(),
                    'payload' => $skuResponse->json(),
                ]);
                if (!$skuResponse->successful()) {
                    Log::warning('Roam physical SKU sync request failed', ['dp_id' => $dpId]);
                    continue;
                }

                $skuData = $skuResponse->json();
                if (!isset($skuData['data']) || !is_array($skuData['data'])) {
                    continue;
                }

                foreach ($skuData['data'] as $sku) {
                    $skuId = $sku['skuid'] ?? null;
                    if (!$skuId) {
                        continue;
                    }

                    $existingSku = RoamPhysicalSku::where('sku_id', $skuId)->where('dp_id', $dpId)->first();
                    $beforeSku = $existingSku ? [
                        'dp_name'      => $dpName,
                        'country_name' => $existingSku->country_name,
                        'country_code' => $existingSku->country_code,
                        'status'       => $existingSku->status,
                    ] : null;

                    $afterSku = [
                        'dp_name'      => $dpName,
                        'country_name' => $sku['display'] ?? 'N/A',
                        'country_code' => $sku['countryCode'] ?? 'N/A',
                        'status'       => $existingSku ? $existingSku->status : 1,
                    ];

                    $skuChangedKeys = [];
                    foreach (['country_name', 'country_code', 'status'] as $field) {
                        if (($beforeSku[$field] ?? null) !== ($afterSku[$field] ?? null)) {
                            $skuChangedKeys[] = $field;
                        }
                    }

                    $skuRecord = RoamPhysicalSku::updateOrCreate(
                        [
                            'sku_id' => $skuId,
                            'dp_id'  => $dpId,
                        ],
                        $afterSku
                    );

                    $skuRecord->dp_name = $dpName;
                    if (!$existingSku) {
                        $newSkus[] = $skuRecord;
                    } elseif (!empty($skuChangedKeys)) {
                        $updatedSkus[] = [
                            'sku_id'       => $skuId,
                            'dp_id'        => $dpId,
                            'before'       => $beforeSku,
                            'after'        => $afterSku,
                            'changed_keys' => $skuChangedKeys,
                        ];
                    }

                    $pkgParams = [
                        'token' => $token,
                        'dpId'  => $dpId,
                        'skuid' => $skuId,
                    ];
                    $pkgSign = $this->createSign($pkgParams, $roamapi->client_key);
                    $pkgResponse = Http::timeout(25)
                        ->retry(2, 1000)
                        ->asForm()
                        ->post(
                            $roamapi->api_url . '/api_esim/getDpSkuSupportPackageInfo',
                            array_merge($pkgParams, ['sign' => $pkgSign])
                        );

                    Log::debug('Roam physical package response payload', [
                        'dp_id' => $dpId,
                        'sku_id' => $skuId,
                        'status' => $pkgResponse->status(),
                        'payload' => $pkgResponse->json(),
                    ]);
                    if (!$pkgResponse->successful()) {
                        Log::warning('Roam physical package sync request failed', ['dp_id' => $dpId, 'sku_id' => $skuId]);
                        continue;
                    }

                    $pkgPayload = $pkgResponse->json('data');
                    if (!is_array($pkgPayload)) {
                        continue;
                    }

                    $pkgList = $pkgPayload['esimPackageVoList'] ?? [];
                    if (!is_array($pkgList) || empty($pkgList)) {
                        continue;
                    }

                    $old = RoamPhysical::where('sku_id', $skuId)->where('dp_id', $dpId)->first();
                    $oldPackages = is_array($old?->packages) ? $old->packages : [];

                    $buildPackageKey = static function (array $package): string {
                        foreach (['pid', 'priceid', 'id'] as $field) {
                            if (isset($package[$field]) && $package[$field] !== '') {
                                return $field . ':' . (string) $package[$field];
                            }
                        }

                        return 'hash:' . md5(json_encode([
                            $package['showName'] ?? '',
                            $package['days'] ?? null,
                            $package['flows'] ?? null,
                            $package['unit'] ?? null,
                            $package['price'] ?? null,
                        ]));
                    };

                    $statusByKey = [];
                    $oldPackagesByKey = [];
                    foreach ($oldPackages as $oldPackage) {
                        if (!is_array($oldPackage)) {
                            continue;
                        }
                        $pkgKey = $buildPackageKey($oldPackage);
                        $statusByKey[$pkgKey] = $oldPackage['status'] ?? 1;
                        $oldPackagesByKey[$pkgKey] = $oldPackage;
                    }

                    $finalPackages = [];
                    $seenKeys = [];
                    foreach ($pkgList as $pkg) {
                        if (!is_array($pkg)) {
                            continue;
                        }

                        $pkgKey = $buildPackageKey($pkg);
                        if (isset($seenKeys[$pkgKey])) {
                            continue;
                        }
                        $seenKeys[$pkgKey] = true;

                        $isNew = !array_key_exists($pkgKey, $statusByKey);
                        $beforePackage = $oldPackagesByKey[$pkgKey] ?? null;
                        $pkg['status'] = $statusByKey[$pkgKey] ?? 1;
                        $pkg['dp_name'] = $dpName;
                        $packageChangedKeys = [];
                        if ($beforePackage) {
                            $fieldsToCompare = ['pid', 'priceid', 'showName', 'days', 'flows', 'unit', 'price', 'status'];

                            foreach ($fieldsToCompare as $field) {
                                $beforeVal = $beforePackage[$field] ?? null;
                                $afterVal = $pkg[$field] ?? null;

                                $different = false;

                                switch ($field) {
                                    case 'price':
                                        $beforeNum = is_null($beforeVal) ? null : round((float) $beforeVal, 4);
                                        $afterNum = is_null($afterVal) ? null : round((float) $afterVal, 4);
                                        if ($beforeNum !== $afterNum) $different = true;
                                        break;

                                    case 'days':
                                    case 'flows':
                                    case 'status':
                                        $beforeInt = is_null($beforeVal) ? null : (int) $beforeVal;
                                        $afterInt = is_null($afterVal) ? null : (int) $afterVal;
                                        if ($beforeInt !== $afterInt) $different = true;
                                        break;

                                    case 'showName':
                                        $b = is_null($beforeVal) ? '' : trim((string) $beforeVal);
                                        $a = is_null($afterVal) ? '' : trim((string) $afterVal);
                                        if ($b !== $a) $different = true;
                                        break;

                                    default:
                                        if ((string) ($beforeVal ?? '') !== (string) ($afterVal ?? '')) $different = true;
                                }

                                if ($different) {
                                    $packageChangedKeys[] = $field;
                                }
                            }
                        }

                        if ($isNew) {
                            $pkg['is_new'] = true;
                            $newPackages[] = $pkg;
                        } elseif (!empty($packageChangedKeys)) {
                            $updatedPackages[] = [
                                'sku_id'       => $skuId,
                                'dp_id'        => $dpId,
                                'dp_name'      => $dpName,
                                'pid'          => $pkg['pid'] ?? ($pkg['priceid'] ?? '-'),
                                'before'       => $beforePackage,
                                'after'        => $pkg,
                                'changed_keys' => $packageChangedKeys,
                            ];
                        } else {
                            unset($pkg['is_new']);
                        }

                        $finalPackages[] = $pkg;
                    }

                    if (empty($finalPackages)) {
                        continue;
                    }

                    RoamPhysical::updateOrCreate(
                        [
                            'sku_id' => $skuId,
                            'dp_id'  => $dpId,
                        ],
                        [
                            'packages'        => $finalPackages,
                            'support_country' => $pkgPayload['supportCountry'] ?? [],
                            'image'           => $pkgPayload['imageUrl'] ?? null,
                        ]
                    );
                }
            }

            return redirect()
                ->route('physical.updateData')
                ->with('success', 'Physical SIM SKUs & packages synced successfully')
                ->with('newSkus', $newSkus)
                ->with('updatedSkus', $updatedSkus)
                ->with('newPackages', $newPackages)
                ->with('updatedPackages', $updatedPackages)
                ->with('syncReport', [
                    'synced_at'        => now()->format('Y-m-d H:i:s'),
                    'new_skus'         => count($newSkus),
                    'updated_skus'     => count($updatedSkus),
                    'new_packages'     => count($newPackages),
                    'updated_packages' => count($updatedPackages),
                ]);
        } catch (Throwable $e) {
            Log::error('Roam physical sync failed', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return redirect()
                ->route('physical.updateData')
                ->with('error', 'Sync failed. Please try again.');
        }
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
        $updatedSkus = session('updatedSkus', []);
        $newPackages = session('newPackages', []);
        $updatedPackages = session('updatedPackages', []);
        $syncReport = session('syncReport', []);
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamphysical.update-data', compact('logo', 'title', 'newSkus', 'updatedSkus', 'newPackages', 'updatedPackages', 'syncReport'));
    }
}
