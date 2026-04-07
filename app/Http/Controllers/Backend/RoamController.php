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

class RoamController extends Controller
{
    public function Esimindex()
    {
        $packages = RoamPhysicalSku::where('status', 1)->get();
        // dd($packages);
        $usd_exchange_rate = Currency::where('name', 'usd')->value('value');
        // dd($usd_exchange_rate);

        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamsim.packages.esim', compact('logo', 'title', 'packages','usd_exchange_rate'));
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
        $roamapi = RoamApi::first();
        if (!$roamapi) {
            return response()->json(['error' => 'Missing API credentials'], 400);
        }

        // Step 1: Login
        $loginParams = [
            'phonenumber' => $roamapi->client_id,
            'password'    => $roamapi->secret_key,
        ];
        $loginParams['sign'] = $this->createSign($loginParams, $roamapi->client_key);

        $loginResponse = Http::timeout(40)->asForm()->post($roamapi->api_url . '/api_order/login', $loginParams);
        $loginData = $loginResponse->json();

        if (!isset($loginData['data']['token'])) {
            return response()->json(['error' => 'Failed to login or get token'], 400);
        }

        $token = $loginData['data']['token'];

        // Step 2: Fetch SKUs
        $sign = $this->createTokenSign($token, $roamapi->client_key);
        $skuResponse = Http::timeout(40)->asForm()->post($roamapi->api_url . '/api_esim/getSkus', [
            'token' => $token,
            'sign'  => $sign,
        ]);
        $skuData = $skuResponse->json();

        if (!isset($skuData['data']) || !is_array($skuData['data'])) {
            return response()->json(['error' => 'No SKUs found'], 400);
        }

        // Step 3: Store SKUs and fetch packages
        $newSkus = [];
        $newPackages = []; // track new packages

        foreach ($skuData['data'] as $sku) {
            $existing = RoamSku::where('sku_id', $sku['skuid'])->first();

            $record = RoamSku::updateOrCreate(
                ['sku_id' => $sku['skuid']],
                [
                    'country_name' => $sku['display'] ?? 'N/A',
                    'country_code' => $sku['countryCode'] ?? 'N/A',
                    'status'       => $existing ? $existing->status : '1',
                ]
            );

            // collect only newly inserted SKUs
            if (!$existing) {
                $newSkus[] = $record;
            }

            // Step 4: Fetch Packages for each SKU
            $packageSign = $this->createSign(['token' => $token, 'skuid' => $sku['skuid']], $roamapi->client_key);
            $packageResponse = Http::timeout(40)->asForm()->post($roamapi->api_url . '/api_esim/getPackages', [
                'token' => $token,
                'skuid' => $sku['skuid'],
                'sign'  => $packageSign,
            ]);

            $packageData = $packageResponse->json();
            $data = $packageData['data'] ?? null;

            if ($data) {
                $packages = $data['esimPackageDtoList'] ?? [];

                // keep old package statuses
                $old = Roam::where('sku_id', $data['skuid'])->first();
                $oldPackages = $old ? $old->packages : [];

                $existingPids = collect($oldPackages)->pluck('pid')->all();

                $finalPackages = $oldPackages;
                foreach ($packages as $package) {
                    if (in_array($package['pid'], $existingPids)) {
                        continue; // skip duplicates
                    }

                    // mark as new
                    $package['status'] = 1;
                    $package['is_new'] = true;

                    $finalPackages[] = $package;
                    $newPackages[] = $package;
                }

                // If no packages exist → do not save anything
                if (empty($finalPackages)) {
                    return redirect()
                        ->route('updateData')
                        ->with('success', 'SKUs and packages synced successfully.')
                        ->with('newSkus', $newSkus)
                        ->with('newPackages', $newPackages);
                }

                // Save merged packages
                Roam::updateOrCreate(
                    ['sku_id' => $data['skuid']],
                    [
                        'packages'        => $finalPackages,
                        'support_country' => $data['supportCountry'] ?? [],
                        'image'           => $data['imageUrl'] ?? null,
                    ]
                );
            }
        }

        return redirect()
            ->route('updateData')
            ->with('success', 'SKUs and packages synced successfully.')
            ->with('newSkus', $newSkus)
            ->with('newPackages', $newPackages);
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

    public function updateExchangeRate(Request $request)
    {
        //dd($request->all());
        if ($request->has('plans')) {
            foreach ($request->plans as $plan) {
                $priceid = $plan['priceid'] ?? null;
                $rate = $plan['exchange_rate'] ?? null;
                
                $skuId = $plan['sku_id'] ?? null;

                if (!$priceid) {
                    continue;
                }

                if ($rate === null || $rate == 0) {
                    continue;
                }

                PriceList::updateOrCreate(
                    [
                        'product_code' => $priceid,
                        'dp_status' => 0,  // set dp_status = 0 for roam esim
                        'dp_info' => null,
                        'plan' => $skuId
                    ],
                    [
                        'exchange_rate' => $rate
                    ]
                );
            }
        }
        return back()->with('success','Exchange rates saved successfully!');
    }

    public function UpdateData()
    {

        $newSkus = session('newSkus', []);
        $newPackages = session('newPackages', []);
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.roamsim.update-data', compact('logo', 'title', 'newSkus', 'newPackages'));
    }
}
