<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoyCreateFormRequest;
use App\Http\Requests\JoyUpdateFormRequest;
use App\Imports\JoytelEsimImport;
use App\Imports\JoytelRechargeImport;
use App\Models\GeneralSetting;
use App\Models\JoytelEsim;
use App\Models\JoytelPhysical;
use App\Models\JoytelApi;
use App\Models\JoyUsageLocation;
use App\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class JoytelController extends Controller
{
    // esim Index
    public function esim()
    {
        return $this->renderIndex('admin.joytel.esim.index', 'esim');
    }

    public function Apiindex()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $api = JoytelApi::first();
        // $categories=Category::latest()->get();
        return view('admin.joytel.api-credential', compact('logo', 'title', 'api'));
    }

    // physical Index
    public function physical()
    {
        return $this->renderIndex('admin.joytel.physical.index', 'recharge', JoytelPhysical::class);
    }

    // // esim create
    // public function esimCreate()
    // {
    //     return $this->renderCreate('admin.joytel.esim.create');
    // }

    // create physical
    public function physicalCreate()
    {
        return $this->renderCreate('admin.joytel.physical.create');
    }

    // esim Store
    public function esimStore(JoyCreateFormRequest $request)
    {
        try {
            return $this->storeJoytelPlan($request, 'esim.index');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // // physical store
    // public function physicalStore(JoyCreateFormRequest $request)
    // {
    //     try {
    //         return $this->storeJoytelPlan($request, 'physical.index');
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function esimEdit(JoytelEsim $esim)
    {
        $data = $this->getData();
        // same product_name rows from DB
        $plans = JoytelEsim::where('product_name', $esim->product_name)->get();
        $data["esim"] = $esim;
        $data["plans"] = $plans;

        return view('admin.joytel.esim.edit', $data);
    }




    public function editPhysical(JoytelPhysical $recharge)
    {
        $data = $this->getData();
        $plans = JoytelPhysical::where('product_name', $recharge->product_name)->get();
        $data["recharge"] = $recharge;
        $data["plans"] = $plans;

        return view('admin.joytel.physical.edit', $data);
    }

    // update esim
    public function updateEsim(JoytelEsim $esim, JoyUpdateFormRequest $request)
    {
        return $this->updateJoytel($esim, $request, 'esim.index');
    }

    // update physical
    public function updatePhysical(JoytelPhysical $recharge, JoyUpdateFormRequest $request)
    {
        return $this->updateJoytel($recharge, $request, 'physical.index');
    }

    public function importEsim(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $import = new JoytelEsimImport();
            Excel::import($import, $request->file('file'));

            return $this->handleImportExcel($import);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function importRecharge(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $import = new JoytelRechargeImport();
            Excel::import($import, $request->file('file'));

            return $this->handleImportExcel($import);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // import Excel
    private function handleImportExcel($import)
    {
        $inserted = $import->inserted;
        $updated  = $import->updated;
        $skipped  = $import->skipped;

        // Case 1: nothing new, nothing updated
        if ($inserted === 0 && $updated === 0 && $skipped > 0) {
            return redirect()->back()->with(
                'error',
                'Already imported. No new data added.'
            );
        }

        // Case 2: only inserted, no updates
        if ($inserted > 0 && $updated === 0) {
            return redirect()->back()->with(
                'success',
                "Excel Import Successfully! {$inserted} new row(s) added."
            );
        }

        // Case 3: only updated, no new rows
        if ($inserted === 0 && $updated > 0) {
            return redirect()->back()->with(
                'success',
                "Excel Import Successfully! {$updated} existing row(s) updated."
            );
        }


        return redirect()->back()->with(
            'success',
            "Excel Import Successfully! {$inserted} new row(s) added, {$updated} existing row(s) updated."
        );
    }



    public function updateCodeStatus(Request $request)
    {
        $validated = $request->validate([
            'joytel_type' => 'required|in:esim,physical',
            'updates' => 'required|array',
            'updates.*.id' => 'required|integer',
            'updates.*.status' => 'required|integer|in:0,1',
        ]);

        foreach ($validated['updates'] as $update) {
            $model = $this->findJoytelRecordById((int) $update['id'], $validated['joytel_type']);

            if (!$model) {
                continue;
            }

            $model->update([
                'status' => $update['status'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }


    public function updateExchangeRate(Request $request)
    {

        $validated = $request->validate([
            'joytel_type' => 'required|in:esim,physical',
            'updates' => 'required|array',
            'updates.*.product_code' => 'required|string',
            'updates.*.exchange_rate' => 'nullable|numeric|min:0',
            'updates.*.profit' => 'nullable|numeric',
            'updates.*.joytel_id' => 'required|integer',
        ]);

        $updatedRows = [];

        foreach ($validated['updates'] as $row) {
            $joytelProduct = $this->findJoytelRecordById((int) $row['joytel_id'], $validated['joytel_type']);

            if (!$joytelProduct) continue;

            $productCode = trim($row['product_code']);
            $newRate = (float) ($row['exchange_rate'] ?? 0);
            $profit = (float) ($row['profit'] ?? 0);

            $productName = $joytelProduct->product_name;

            $priceList = PriceList::firstOrNew([
                'product_code' => $productCode
            ]);

            // only update if changed
            if ((float)$priceList->exchange_rate !== $newRate) {

                $priceList->exchange_rate = $newRate;
                $priceList->profit = $profit;
                $priceList->plan = $productName;

                $priceList->dp_status = 0;
                $priceList->dp_info = null;

                $priceList->save();

                $updatedRows[] = $productCode;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Exchange rates updated successfully',
            'updated' => $updatedRows
        ]);
    }

    private function findJoytelRecordById(int $id, ?string $type = null): ?Model
    {
        if ($type === 'physical') {
            return JoytelPhysical::find($id);
        }

        if ($type === 'esim') {
            return JoytelEsim::find($id);
        }

        return JoytelEsim::find($id)
            ?? JoytelPhysical::find($id);
    }

    public function destroy($id)
    {
        $esim = JoytelEsim::findOrFail($id);

        $plans = is_array($esim->plan) ? $esim->plan : json_decode($esim->plan, true);

        if (is_array($plans)) {
            $productCodes = collect($plans)->pluck('product_code')->filter()->toArray();

            if (!empty($productCodes)) {
                \App\Models\PriceList::whereIn('product_code', $productCodes)->delete();
            }
        }

        $esim->delete();
        session()->flash("success", "SIM Deleted Successfully!");
        return response()->json(['message' => 'eSIM and related price list(s) deleted successfully!']);
    }

    // render index pages function
    private function renderIndex($route, $keyword, string $modelClass = JoytelEsim::class)
    {
        $latestIds = $modelClass::query()
            ->selectRaw('MAX(id) as id')
            ->whereRaw('LOWER(type) LIKE ?', ['%' . $keyword . '%'])
            ->groupBy('product_name');

        $sim_lists = $modelClass::query()
            ->whereIn('id', $latestIds)
            ->latest()
            ->get();

        $plansByProductName = $modelClass::query()
            ->whereIn('product_name', $sim_lists->pluck('product_name'))
            ->orderBy('id')
            ->get()
            ->groupBy('product_name');

        $additional_prices = PriceList::query()
            ->select('product_code', 'exchange_rate', 'profit')
            ->latest()
            ->get();

        $exchangeRates = PriceList::query()->pluck('exchange_rate', 'product_code');
        $userUsdRate = \App\Models\Currency::where('name', 'user_usd_rate')?->value('value');

        return view($route, compact('sim_lists', 'plansByProductName', 'additional_prices', 'exchangeRates', 'userUsdRate'));
    }


    // render create function
    private function renderCreate($route)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $usage_locations = JoyUsageLocation::pluck('location')->unique();
        return view($route, compact('logo', 'title', 'usage_locations'));
    }

    // data for edit function
    private function getData()
    {
        return [
            "logo" => GeneralSetting::where('type', 'file')->first(),
            "title" => GeneralSetting::where('type', 'string')->first(),
            "coverages" => JoyUsageLocation::where('status', 1)->pluck('location')->unique(),
        ];
    }


    // update function for physical + esim
    private function updateJoytel(Model $model, JoyUpdateFormRequest $request, string $redirectRoute)
    {
        try {
            $oldProductName = $model->getOriginal('product_name');
            $removedPhotos = $request->input('removed_photos', []);
            $photos = $this->normalizeJoytelPhotos($model->photo);

            foreach ($removedPhotos as $fileToRemove) {
                $filePath = public_path('sim/' . $fileToRemove);
                if (file_exists($filePath)) unlink($filePath);
                if (($key = array_search($fileToRemove, $photos)) !== false) unset($photos[$key]);
            }

            $filePaths = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $fileName = $file->getClientOriginalName();
                    $file->move(public_path('sim'), $fileName);
                    $filePaths[] = $fileName;
                }
            }

            $mergedPhotos = array_values(array_merge($photos, $filePaths));

            DB::transaction(function () use ($model, $request, $mergedPhotos, $oldProductName) {
                $modelClass = get_class($model);

                $modelClass::where('product_name', $oldProductName)->update([
                    'photo' => $mergedPhotos,
                    'status' => $request->status,
                ]);

                $model->refresh();
            });

            session()->flash('success', 'SIM updated successfully!');

            return response()->json([
                'success' => true,
                'redirect_url' => route($redirectRoute)
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function normalizeJoytelPhotos($photos): array
    {
        if (is_array($photos)) {
            return array_values(array_filter($photos));
        }

        if (is_string($photos) && trim($photos) !== '') {
            $decoded = json_decode($photos, true);

            if (is_array($decoded)) {
                return array_values(array_filter($decoded));
            }

            return [$photos];
        }

        return [];
    }

    // store function for physical + esim
    private function storeJoytelPlan(JoyCreateFormRequest $request, string $redirectRoute)
    {
        $rows = json_decode($request->input('rows_json'), true) ?: [];
        if (!is_array($rows)) {
            $rows = [];
        }

        $productCodes = [];

        foreach ($rows as &$row) {
            if (!is_array($row)) continue;

            $row['code_status'] = $request->filled('status')
                ? (int) $request->input('status')
                : 1;

            // if (isset($row['service_day'])) {
            //     $sd = trim((string)$row['service_day']);
            //     if (!preg_match('/\bday(s)?\b/i', $sd)) {
            //         $n = is_numeric($sd) ? (int)$sd : (int)preg_replace('/\D/', '', $sd);
            //         if ($n) $row['service_day'] = $n . ' day';
            //     }
            // }

            if (!isset($row['product_code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Each row must have a product_code'
                ], 422);
            }

            if (in_array($row['product_code'], $productCodes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate product_code detected: ' . $row['product_code']
                ], 422);
            }

            $productCodes[] = $row['product_code'];
        }
        unset($row);

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('sim'), $fileName);
                $filePaths[] = $fileName;
            }
        }

        JoytelEsim::create([
            'category_name' => $request->cat_name,
            'product_name' => $request->product_name,
            'usage_location' => $request->locations,
            'supplier' => $request->supplier,
            'product_type' => $request->product_type,
            'plan' => $rows,
            'photo' => $filePaths,
            'remark' => $request->product_name,
            'activation_policy' => $request->activation_policy,
            'delivery_time' => $request->del_time,
            'status' => $request->status ?? 1,
        ]);

        session()->flash('success', 'SIM created successfully!');

        return response()->json([
            'success' => true,
            'rows' => $rows,
            'redirect_url' => route($redirectRoute)
        ]);
    }

    public function updateApi(Request $request)
    {
        $validated = $request->validateWithBag('warehouse', [
            'customer_code' => 'required|string|max:255',
            'customer_auth' => 'required|string|max:255',
            'api_url'       => 'required|url|max:255',
        ]);

        $api = JoytelApi::first();

        if (!$api) {

            $api = new JoytelApi();

            $api->rsp_appid = '';
            $api->rsp_secret = '';
            $api->rsp_baseurl = '';
        }

        $api->customer_code = $validated['customer_code'];
        $api->customer_auth = $validated['customer_auth'];
        $api->api_url = $validated['api_url'];

        $api->save();

        return redirect()->back()->with('success', 'Warehouse API credentials updated successfully!')->with('active_tab', 'warehouse');
    }

    public function updateRsp(Request $request)
    {
        $validated = $request->validateWithBag('rsp', [
            'rsp_appid'   => 'required|string|max:255',
            'rsp_secret'  => 'required|string|max:255',
            'rsp_baseurl' => 'required|url|max:255',
        ]);

        $api = JoytelApi::first();

        if (!$api) {
            $api = new JoytelApi();


            $api->customer_code = '';
            $api->customer_auth = '';
            $api->api_url = '';
        }

        $api->rsp_appid = $validated['rsp_appid'];
        $api->rsp_secret = $validated['rsp_secret'];
        $api->rsp_baseurl = $validated['rsp_baseurl'];

        $api->save();

        return redirect()->back()->with('success', 'RSP API credentials updated successfully!')->with('active_tab', 'rsp');
    }
}
