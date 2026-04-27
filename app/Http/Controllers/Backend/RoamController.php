<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\RoamSku;
use App\Models\RoamApi;
use App\Models\Roam;
use App\Models\Currency;
use App\Models\PriceList;
use App\Models\GeneralSetting;
use App\Models\RoamPhysicalSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RoamController extends Controller
{
    public function Esimindex()
    {
        $packages = RoamSku::with('roam')
            ->where('status', 1)
            ->orderBy('country_name', 'asc')
            ->get();
        // dd($packages);
        $usd_exchange_rate = Currency::where('name', 'usd')->value('value');
        // dd($usd_exchange_rate);

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamsim.packages.esim', compact('logo', 'title', 'packages', 'usd_exchange_rate'));
    }



    public function RoamesimEdit($skuid)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $roam = Roam::where('sku_id', $skuid)->firstOrFail();
        $name = RoamSku::where('sku_id', $skuid)->firstOrFail();
        return view('admin.roamsim.packages.edit-roamsim', compact('roam', 'title', 'logo', 'name'));
    }


    public function Apiindex()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $api = RoamApi::first();
        // $categories=Category::latest()->get();
        return view('admin.roamsim.api-credential', compact('logo', 'title', 'api'));
    }

    public function Skuindex()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $roamList = RoamSku::all();
        // $categories=Category::latest()->get();
        return view('admin.roamsim.skulist', compact('logo', 'title', 'roamList'));
    }

    //test roam api for Esim

    // public function StoreSkus(Request $request)
    // {
    //      $roamapi = RoamApi::first(); // safely get just one
    //     if ($roamapi) {
    //         $phone = $roamapi->client_id;
    //         $password = $roamapi->secret_key;
    //         $clientKey = $roamapi->client_key;
    //     }else{
    //         return 'fail';
    //     }
    //     $loginParams = [
    //         'phonenumber' => $phone,
    //         'password' => $password,
    //     ];
    //     $loginParams['sign'] = $this->createSign($loginParams, $clientKey);

    //         $loginResponse = Http::asForm()->post($roamapi->api_url.'/api_order/login', $loginParams);
    //         $loginData = $loginResponse->json();

    //     if (!isset($loginData['data']['token'])) {
    //         return response()->json(['error' => 'Failed to login or get token'], 400);
    //     }

    //     $token = $loginData['data']['token'];

    //     // Step 2: Use token to get SKUs
    //     $sign = $this->createTokenSign($token, $clientKey);
    //     $skuResponse = Http::asForm()->post($roamapi->api_url.'/api_esim/getSkus', [
    //         'token' => $token,
    //         'sign' => $sign,
    //     ]);

    //     $skuData = $skuResponse->json();
    //     // dd($skuData);
    //     // Step 3: Store data in database
    //     if (isset($skuData['data']) && is_array($skuData['data'])) {
    //         foreach ($skuData['data'] as $sku) {
    //             $existing = RoamSku::where('sku_id', $sku['skuid'])->first();
    //             RoamSku::updateOrCreate(
    //                 ['sku_id' => $sku['skuid']],
    //                 [
    //                     'country_name' => $sku['display'] ?? 'N/A',
    //                     'country_code' => $sku['countryCode'] ?? 'N/A',
    //                      'status' => $existing ? $existing->status : '1',
    //                 ]
    //             );
    //         }
    //     }

    //    return redirect()->route('roamSkuIndex')->with('success', 'Login successful. SKUs fetched and stored.');
    // }

    // // get packages
    // public function roamPackages()
    // {
    //  $roamapi = RoamApi::first();
    //     if (!$roamapi) return [];

    //     // Step 1: Login
    //     $loginParams = [
    //         'phonenumber' => $roamapi->client_id,
    //         'password' => $roamapi->secret_key,
    //     ];
    //     $loginParams['sign'] = $this->createSign($loginParams, $roamapi->client_key);

    //     $loginResponse = Http::asForm()->post($roamapi->api_url.'/api_order/login', $loginParams);
    //     $loginData = $loginResponse->json();

    //     if (!isset($loginData['data']['token'])) return [];

    //     $token = $loginData['data']['token'];
    //     $skus = RoamSku::all();
    //     $allPackages = [];

    //     foreach ($skus as $sku) {
    //         $sign = $this->createSign(['token' => $token, 'skuid' => $sku->sku_id], $roamapi->client_key);

    //         $response = Http::timeout(20)->asForm()->post($roamapi->api_url . '/api_esim/getPackages', [
    //             'token' => $token,
    //             'skuid' => $sku->sku_id,
    //             'sign' => $sign,
    //         ]);

    //       // 🔹 Process SKUs in chunks of 20 (instead of all at once)
    //     // $skus->chunk(5)->each(function ($skuChunk) use ($roamapi, $token, &$allPackages) {
    //     //     foreach ($skuChunk as $sku) {
    //     //         $sign = $this->createSign(
    //     //             ['token' => $token, 'skuid' => $sku->sku_id],
    //     //             $roamapi->client_key
    //     //         );

    //     //         $response = Http::timeout(20)->asForm()->post(
    //     //             $roamapi->api_url . '/api_esim/getPackages',
    //     //             [
    //     //                 'token' => $token,
    //     //                 'skuid' => $sku->sku_id,
    //     //                 'sign'  => $sign,
    //     //             ]
    //     //         );

    //         $packageData = $response->json();
    //         dd($packageData);
    //         $data = $packageData['data'] ?? null;

    //         // if ($data) {
    //         //     $packages = $data['esimPackageDtoList'] ?? [];

    //         //         foreach ($packages as &$package) {

    //         //             $package['status'] = 1; 
    //         //         }
    //         //         unset($package);
    //         //     Roam::updateOrCreate(
    //         //         ['sku_id' => $data['skuid']],  // unique
    //         //         [
    //         //             'packages' =>  $packages,

    //         //             'support_country' => $data['supportCountry'] ?? [],
    //         //             'image' => $data['imageUrl'] ?? null,
    //         //         ]
    //         //     );

    //         //     $allPackages[$sku->sku_id] = $data;
    //         // }


    //         if ($data) {
    //             $packages = $data['esimPackageDtoList'] ?? [];

    //             // get old packages from DB
    //             $old = Roam::where('sku_id', $data['skuid'])->first();
    //             $oldPackages = $old ? $old->packages : [];

    //             foreach ($packages as &$package) {
    //                 // find matching package from DB by pid (or priceid, whichever is unique)
    //                 $oldStatus = collect($oldPackages)
    //                                 ->firstWhere('pid', $package['pid'])['status'] ?? 1;

    //                 // keep old status, or default to 1 if new
    //                 $package['status'] = $oldStatus;
    //             }
    //             unset($package);

    //             Roam::updateOrCreate(
    //                 ['sku_id' => $data['skuid']],  // unique
    //                 [
    //                     'packages'        => $packages,
    //                     'support_country' => $data['supportCountry'] ?? [],
    //                     'image'           => $data['imageUrl'] ?? null,
    //                 ]
    //             );

    //             $allPackages[$sku->sku_id] = $data;
    //         }
    //     }

    // // });

    //     // return redirect()->route('roamEsimEdit',$skuid);
    //     return redirect()->route('roamEsimIndex');
    // }

    // public function syncSkusAndPackages()
    // {
    //     $roamapi = RoamApi::first();
    //     if (!$roamapi) {
    //         return response()->json(['error' => 'Missing API credentials'], 400);
    //     }

    //     // Step 1: Login
    //     $loginParams = [
    //         'phonenumber' => $roamapi->client_id,
    //         'password'    => $roamapi->secret_key,
    //     ];
    //     $loginParams['sign'] = $this->createSign($loginParams, $roamapi->client_key);

    //     $loginResponse = Http::asForm()->post($roamapi->api_url.'/api_order/login', $loginParams);
    //     $loginData = $loginResponse->json();

    //     if (!isset($loginData['data']['token'])) {
    //         return response()->json(['error' => 'Failed to login or get token'], 400);
    //     }

    //     $token = $loginData['data']['token'];

    //     // Step 2: Fetch SKUs
    //     $sign = $this->createTokenSign($token, $roamapi->client_key);
    //     $skuResponse = Http::asForm()->post($roamapi->api_url.'/api_esim/getSkus', [
    //         'token' => $token,
    //         'sign'  => $sign,
    //     ]);
    //     $skuData = $skuResponse->json();

    //     if (!isset($skuData['data']) || !is_array($skuData['data'])) {
    //         return response()->json(['error' => 'No SKUs found'], 400);
    //     }

    //     // Step 3: Store SKUs and fetch packages
    //    $newSkus = [];
    //     foreach ($skuData['data'] as $sku) {
    //         $existing = RoamSku::where('sku_id', $sku['skuid'])->first();

    //         $record = RoamSku::updateOrCreate(
    //             ['sku_id' => $sku['skuid']],
    //             [
    //                 'country_name' => $sku['display'] ?? 'N/A',
    //                 'country_code' => $sku['countryCode'] ?? 'N/A',
    //                 'status'       => $existing ? $existing->status : '1',
    //             ]
    //         );

    //         // collect only newly inserted ones
    //         if (!$existing) {
    //             $newSkus[] = $record;
    //         }

    //         // dd($newSkus);


    //         // Fetch Packages for each SKU
    //         $packageSign = $this->createSign(['token' => $token, 'skuid' => $sku['skuid']], $roamapi->client_key);
    //         $packageResponse = Http::timeout(20)->asForm()->post($roamapi->api_url.'/api_esim/getPackages', [
    //             'token' => $token,
    //             'skuid' => $sku['skuid'],
    //             'sign'  => $packageSign,
    //         ]);

    //         $packageData = $packageResponse->json();


    //         $data = $packageData['data'] ?? null;

    //         if ($data) {
    //             $packages = $data['esimPackageDtoList'] ?? [];

    //             // keep old package statuses
    //             $old = Roam::where('sku_id', $data['skuid'])->first();
    //             $oldPackages = $old ? $old->packages : [];

    //             foreach ($packages as &$package) {
    //                 $oldStatus = collect($oldPackages)
    //                                 ->firstWhere('pid', $package['pid'])['status'] ?? 1;
    //                 $package['status'] = $oldStatus;
    //             }
    //             unset($package);

    //             // Save packages
    //             Roam::updateOrCreate(
    //                 ['sku_id' => $data['skuid']],
    //                 [
    //                     'packages'        => $packages,
    //                     'support_country' => $data['supportCountry'] ?? [],
    //                     'image'           => $data['imageUrl'] ?? null,
    //                 ]
    //             );
    //         }
    //     }



    //     return redirect()->route('updateData')->with('success', 'SKUs and packages synced successfully.')->with('newSkus', $newSkus);
    // }

    public function syncSkusAndPackages()
    {
        try {
            @set_time_limit(0);
            ini_set('memory_limit', '512M');

            $startTime = time();
            $maxExecutionSeconds = 120;
            $maxSkusToProcess = 100;
            $processedSkuCount = 0;

            $roamapi = RoamApi::first();
            if (!$roamapi) {
                return redirect()->route('updateData')->with('error', 'Missing API credentials.');
            }

            // Step 1: Login
            $loginParams = [
                'phonenumber' => $roamapi->client_id,
                'password'    => $roamapi->secret_key,
            ];
            $loginParams['sign'] = $this->createSign($loginParams, $roamapi->client_key);

            $loginResponse = Http::timeout(15)
                ->retry(1, 300)
                ->asForm()
                ->post($roamapi->api_url . '/api_order/login', $loginParams);

            if (!$loginResponse->successful()) {
                Log::warning('Roam eSIM login request failed', [
                    'status' => $loginResponse->status(),
                    'body' => $loginResponse->body(),
                ]);
                return redirect()->route('updateData')->with('error', 'Failed to login or get token.');
            }

            $loginData = $loginResponse->json();
            if (!isset($loginData['data']['token'])) {
                return redirect()->route('updateData')->with('error', 'Failed to login or get token.');
            }

            $token = $loginData['data']['token'];

            // Step 2: Fetch SKUs
            $sign = $this->createTokenSign($token, $roamapi->client_key);
            $skuResponse = Http::timeout(15)
                ->retry(1, 300)
                ->asForm()
                ->post($roamapi->api_url . '/api_esim/getSkus', [
                    'token' => $token,
                    'sign'  => $sign,
                ]);

            if (!$skuResponse->successful()) {
                Log::warning('Roam eSIM SKU request failed', [
                    'status' => $skuResponse->status(),
                    'body' => $skuResponse->body(),
                ]);
                return redirect()->route('updateData')->with('error', 'No SKUs found.');
            }

            $skuData = $skuResponse->json();
            if (!isset($skuData['data']) || !is_array($skuData['data'])) {
                return redirect()->route('updateData')->with('error', 'No SKUs found.');
            }

            $newSkus = [];
            $updatedSkus = [];
            $newPackages = [];
            $updatedPackages = [];

            foreach ($skuData['data'] as $sku) {
                if ((time() - $startTime) > $maxExecutionSeconds) break;

                $skuId = $sku['skuid'] ?? null;
                if (!$skuId) {
                    continue;
                }

                if ($processedSkuCount >= $maxSkusToProcess) break;
                $processedSkuCount++;

                $existing = RoamSku::where('sku_id', $skuId)->first();
                $beforeSku = $existing ? [
                    'country_name' => $existing->country_name,
                    'country_code' => $existing->country_code,
                    'status'       => $existing->status,
                ] : null;

                $afterSku = [
                    'country_name' => $sku['display'] ?? 'N/A',
                    'country_code' => $sku['countryCode'] ?? 'N/A',
                    'status'       => $existing ? $existing->status : '1',
                ];
                $skuChangedKeys = [];
                foreach (['country_name', 'country_code', 'status'] as $field) {
                    if (($beforeSku[$field] ?? null) !== ($afterSku[$field] ?? null)) {
                        $skuChangedKeys[] = $field;
                    }
                }

                $record = RoamSku::updateOrCreate(
                    ['sku_id' => $skuId],
                    $afterSku
                );

                if (!$existing) {
                    $newSkus[] = $record;
                } elseif (!empty($skuChangedKeys)) {
                    $updatedSkus[] = [
                        'sku_id'       => $skuId,
                        'before'       => $beforeSku,
                        'after'        => $afterSku,
                        'changed_keys' => $skuChangedKeys,
                    ];
                }

                // Step 3: Fetch package data for this SKU
                $packageSign = $this->createSign(['token' => $token, 'skuid' => $skuId], $roamapi->client_key);
                $packageResponse = Http::timeout(15)
                    ->retry(1, 300)
                    ->asForm()
                    ->post($roamapi->api_url . '/api_esim/getPackages', [
                        'token' => $token,
                        'skuid' => $skuId,
                        'sign'  => $packageSign,
                    ]);

                if (!$packageResponse->successful()) {
                    Log::warning('Roam eSIM package request failed', [
                        'sku_id' => $skuId,
                        'status' => $packageResponse->status(),
                        'body' => $packageResponse->body(),
                    ]);
                    continue;
                }

                $packagePayload = $packageResponse->json('data');
                if (!is_array($packagePayload)) {
                    continue;
                }

                $packages = $packagePayload['esimPackageDtoList'] ?? [];
                if (!is_array($packages) || empty($packages)) {
                    continue;
                }

                // Preserve old status by a stable key, but refresh package details from API.
                $old = Roam::where('sku_id', $skuId)->first();
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
                    ]));
                };

                $statusByKey = [];
                $oldPackagesByKey = [];
                foreach ($oldPackages as $oldPackage) {
                    if (!is_array($oldPackage)) {
                        continue;
                    }

                    $packageKey = $buildPackageKey($oldPackage);
                    $statusByKey[$packageKey] = $oldPackage['status'] ?? 1;
                    $oldPackagesByKey[$packageKey] = $oldPackage;
                }

                $finalPackages = [];
                $seenKeys = [];
                foreach ($packages as $package) {
                    if (!is_array($package)) {
                        continue;
                    }

                    $packageKey = $buildPackageKey($package);
                    if (isset($seenKeys[$packageKey])) {
                        continue;
                    }
                    $seenKeys[$packageKey] = true;

                    $isNew = !array_key_exists($packageKey, $statusByKey);
                    $beforePackage = $oldPackagesByKey[$packageKey] ?? null;
                    $package['status'] = $statusByKey[$packageKey] ?? 1;
                    $package['sku_id'] = $skuId;
                    $packageChangedKeys = [];
                    if ($beforePackage) {
                        $fieldsToCompare = ['pid', 'priceid', 'showName', 'days', 'flows', 'unit', 'price', 'status'];

                        foreach ($fieldsToCompare as $field) {
                            $beforeVal = $beforePackage[$field] ?? null;
                            $afterVal = $package[$field] ?? null;

                            $different = false;

                            switch ($field) {
                                case 'price':
                                    // compare numerically with rounding to avoid string/float formatting differences
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
                                    // fallback: compare as strings
                                    if ((string) ($beforeVal ?? '') !== (string) ($afterVal ?? '')) $different = true;
                            }

                            if ($different) {
                                $packageChangedKeys[] = $field;
                            }
                        }
                    }

                    if ($isNew) {
                        $package['is_new'] = true;
                        $newPackages[] = $package;
                    } elseif (!empty($packageChangedKeys)) {
                        $updatedPackages[] = [
                            'sku_id'       => $skuId,
                            'pid'          => $package['pid'] ?? ($package['priceid'] ?? '-'),
                            'before'       => $beforePackage,
                            'after'        => $package,
                            'changed_keys' => $packageChangedKeys,
                        ];
                    } else {
                        unset($package['is_new']);
                    }

                    $finalPackages[] = $package;
                }

                if (empty($finalPackages)) {
                    continue;
                }

                Roam::updateOrCreate(
                    ['sku_id' => $skuId],
                    [
                        'packages'        => $finalPackages,
                        'support_country' => $packagePayload['supportCountry'] ?? [],
                        'image'           => $packagePayload['imageUrl'] ?? ($old->image ?? ''),
                    ]
                );
            }

            return redirect()
                ->route('updateData')
                ->with('success', 'SKUs and packages synced successfully.')
                ->with('newSkus', $newSkus)
                ->with('updatedSkus', $updatedSkus)
                ->with('newPackages', $newPackages)
                ->with('updatedPackages', $updatedPackages)
                ->with('syncReport', [
                    'synced_at' => now()->format('Y-m-d H:i:s'),
                    'processed_skus' => $processedSkuCount,
                    'new_skus' => count($newSkus),
                    'updated_skus' => count($updatedSkus),
                    'new_packages' => count($newPackages),
                    'updated_packages' => count($updatedPackages),
                ]);
        } catch (Throwable $e) {
            Log::error('Roam eSIM sync failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('updateData')
                ->with('error', 'Sync failed. Please try again.');
        }
    }



    private function createSign(array $data, string $clientKey): string
    {
        unset($data['sign']); // remove existing sign if any

        ksort($data); // sort by keys

        $parts = [];
        foreach ($data as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        $plainText = implode('', $parts) . $clientKey;

        return md5($plainText);
    }

    private function createTokenSign($token, $clientKey)
    {
        return md5("token={$token}" . $clientKey);
    }

    // chaeck status
    public function toggleStatus($skuid)
    {
        $sku = RoamSku::where('sku_id', $skuid)->first();
        $sku->status = $sku->status ? 0 : 1;
        $sku->save();

        return response()->json([
            'status' => true,
            'new_status' => $sku->status
        ]);
    }


    //store api url, client id,secret,key

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'client_key' => 'required|string',
            'api_url' => 'required|url',
        ]);

        RoamApi::create([
            'client_id' => $validated['client_id'],
            'secret_key' => $validated['client_secret'],  // store as secret_key
            'client_key' => $validated['client_key'],
            'api_url'    => $validated['api_url'],
        ]);

        return redirect()->back()->with('success', 'Roam API credentials saved successfully!');
    }




    //for roam update

    public function update(Request $request, $id)
    {

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:0,1'
        ]);
        $roam = Roam::findOrFail($id);


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/upload/roam', $filename);


            if ($roam->image && Storage::exists('public/upload/roam/' . $roam->image)) {
                Storage::delete('public/upload/roam/' . $roam->image);
            }

            $roam->image = $filename;
        }

        $roam->save();


        $roamSku = RoamSku::where('sku_id', $roam->sku_id)->first();
        if ($roamSku) {
            $roamSku->status = $request->status;
            $roamSku->save();
        }

        return redirect()->route('roamEsimIndex')->with('success', 'Roam data updated successfully!');
    }





    // For manage status

    public function updatePackageStatus(Request $request)
    {
        $roam = Roam::where('sku_id', $request->sku_id)->first();

        if (!$roam) {
            return back()->with('error', 'Roam package not found.');
        }

        $packages = $roam->packages; // JSON column cast to array
        $index = $request->index;

        if (isset($packages[$index])) {

            $packages[$index]['status'] = $request->input('status', 0);


            $roam->packages = $packages;
            $roam->save();
        }

        return redirect()
            ->route('roamEsimIndex')
            ->with('success', 'Package status updated.');
    }



    //for manage price

    // public function pricestore(Request $request)
    // {
    //      if ($request->has('plans')) {
    //             foreach ($request->plans as $plan) {
    //                if (!empty($plan['price'])) {
    //                     PriceList::updateOrCreate(
    //                         ['product_code' => $plan['priceid']], // priceid
    //                         [
    //                             'price' => $plan['price'],// user input
    //                             'increment' => null,
    //                         ]             
    //                     );
    //                 }
    //             }
    //         }


    //     return back()->with('success', 'Prices saved successfully!');
    // }

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

    // public function updateExchangeRate(Request $request)
    // {
    //     //dd($request->all());
    //     if ($request->has('plans')) {
    //         foreach ($request->plans as $plan) {
    //             $priceid = $plan['priceid'] ?? null;
    //             $rate = $plan['exchange_rate'] ?? null;

    //             $skuId = $plan['sku_id'] ?? null;

    //             if (!$priceid) {
    //                 continue;
    //             }

    //             if ($rate === null || $rate == 0) {
    //                 continue;
    //             }

    //             PriceList::updateOrCreate(
    //                 [
    //                     'product_code' => $priceid,
    //                     'dp_status' => 0,  // set dp_status = 0 for roam esim
    //                     'dp_info' => null,
    //                     'plan' => $skuId
    //                 ],
    //                 [
    //                     'exchange_rate' => $rate
    //                 ]
    //             );
    //         }
    //     }
    //     return back()->with('success','Exchange rates saved successfully!');
    // }

    public function updateExchangeRate(Request $request)
    {

        if ($request->has('plans')) {

            foreach ($request->plans as $plan) {

                $priceid     = $plan['priceid'] ?? null;
                $sellingRate = $plan['selling_rate'] ?? null;
                $profit      = $plan['profit'] ?? 0;
                $skuId       = $plan['sku_id'] ?? null;

                // skip invalid
                if (!$priceid || $sellingRate === null || $sellingRate === '') {
                    continue;
                }

                $sellingRate = (int) $sellingRate;
                $profit = (int) $profit;

                PriceList::updateOrCreate(
                    [
                        'product_code' => $priceid,
                        'plan'         => $skuId,
                        'dp_status'    => 0,
                    ],
                    [
                        'exchange_rate' => $sellingRate, // selling rate save
                        'profit'        => $profit,      // profit save
                    ]
                );
            }
        }

        return back()->with('success', 'Saved successfully!');
    }

    public function UpdateData()
    {
        $newSkus = session('newSkus', []);
        $updatedSkus = session('updatedSkus', []);
        $newPackages = session('newPackages', []);
        $updatedPackages = session('updatedPackages', []);
        $syncReport = session('syncReport', []);
        $parentPlanNames = RoamSku::whereIn(
            'sku_id',
            collect($newPackages)->pluck('sku_id')->filter()->unique()->values()->all()
        )->pluck('country_name', 'sku_id')->all();
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamsim.update-data', compact('logo', 'title', 'newSkus', 'updatedSkus', 'newPackages', 'updatedPackages', 'syncReport', 'parentPlanNames'));
    }
}
