<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoyCreateFormRequest;
use App\Http\Requests\JoyUpdateFormRequest;
use App\Imports\JoytelEsimImport;
use App\Imports\JoytelRechargeImport;
use App\Models\GeneralSetting;
use App\Models\Joytel;
use App\Models\JoyUsageLocation;
use App\Models\PriceList;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JoytelController extends Controller
{
    // esim Index
    public function esim()
    {
        return $this->renderIndex('admin.joytel.esim.index', 'esim');
    }

    // physical Index
    public function physical()
    {
        return $this->renderIndex('admin.joytel.physical.index', 'recharge');
    }

    // esim create
    public function esimCreate()
    {
        return $this->renderCreate('admin.joytel.esim.create');
    }

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

    // physical store
    public function physicalStore(JoyCreateFormRequest $request)
    {
        try {
            return $this->storeJoytelPlan($request, 'physical.index');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // esim edit
    // public function esimEdit(Joytel $esim)
    // {
    //     $data = $this->getData();
    //     $data["esim"] = $esim;
    //     return view('admin.joytel.esim.edit', $data);
    // }

    public function esimEdit(Joytel $esim)
    {
        $data = $this->getData();
        $esim->plan = is_array($esim->plan) ? $esim->plan : json_decode($esim->plan, true);
        $data["esim"] = $esim;
        return view('admin.joytel.esim.edit', $data);
    }

    // physical edit
    // public function editPhysical(Joytel $recharge)
    // {
    //     $data = $this->getData();
    //     $data['recharge'] = $recharge;
    //     return view('admin.joytel.physical.edit', $data);
    // }

    public function editPhysical(Joytel $recharge)
    {
        $data = $this->getData();
        $recharge->plan = is_array($recharge->plan) ? $recharge->plan : json_decode($recharge->plan, true);
        $data['recharge'] = $recharge;
        return view('admin.joytel.physical.edit', $data);
    }

    // update esim
    public function updateEsim(Joytel $esim, JoyUpdateFormRequest $request)
    {
        return $this->updateJoytel($esim, $request, 'esim.index');
    }

    // update physical
    public function updatePhysical(Joytel $recharge, JoyUpdateFormRequest $request)
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

    // update code status
    public function updateCodeStatus(Request $request)
    {
        $validated = $request->validate([
            'sim_id' => 'required|integer|exists:joytels,id',
            'updates' => 'required|array',
            'updates.*.product_code' => 'required|string',
            'updates.*.code_status' => 'required|integer|in:0,1'
        ]);

        $joytel = Joytel::findOrFail($validated['sim_id']);
        $plan = $joytel->plan;
        foreach ($plan as &$row) {
            foreach ($validated['updates'] as $update) {
                if ($row['product_code'] === $update['product_code']) {
                    $row['code_status'] = $update['code_status'];
                }
            }
        }
        $joytel->plan = $plan;
        $joytel->save();
        return response()->json(['success' => true]);
    }

    // update price
    // public function updatePrice(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'updates' => 'required|array',
    //             'update.*.sim_id' => ['required', 'exists:joytels,id'],
    //             'updates.*.product_code' => [
    //                 'required',
    //                 'string',
    //                 function ($attribute, $value, $fail) {
    //                     $exists = Joytel::whereJsonContains('plan', ['product_code' => $value])->exists();
    //                     if (!$exists) {
    //                         $fail("The product code {$value} does not exist with service_day='day' in joytels");
    //                     }
    //                 }
    //             ],
    //             'updates.*.price' => ['nullable', 'integer', 'regex:/^(0|[1-9]\d*)$/'],
    //             'updates.*.increment' => ['nullable', 'integer', 'regex:/^(0|[1-9]\d*)$/'],
    //         ]);

    //         foreach ($validated['updates'] as $update) {
    //             PriceList::updateOrCreate(
    //                 ['product_code' => $update['product_code']],
    //                 [
    //                     'price' => $update['price'],
    //                     'increment' => $update['increment'] ?? null,
    //                     'joytel_id' => $update['sim_id'] ?? null,
    //                 ]
    //             );
    //         }

    //         return response()->json(['success' => true]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     }
    // }


    public function updateExchangeRate(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.product_code' => 'required|string',
            'updates.*.exchange_rate' => 'nullable|numeric|min:0',
            'updates.*.profit' => 'nullable|numeric',
            'updates.*.joytel_id' => 'required'
        ]);

        $updatedRows = [];

        foreach ($validated['updates'] as $row) {
            $productCode = trim($row['product_code']);
            $newRate = $row['exchange_rate'] ?? 0;
            $profit = $row['profit'] ?? 0;
            $joytelId = $row['joytel_id'];
            $joytelProduct = \App\Models\Joytel::find($joytelId);
            $productName = $joytelProduct->product_name ?? '';

            $priceList = PriceList::firstOrNew(['product_code' => $productCode]);

            if ($priceList->exchange_rate != $newRate) {

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
            'message' => 'Exchange rates updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $esim = Joytel::findOrFail($id);

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
    private function renderIndex($route, $keyword)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $query = Joytel::whereRaw('LOWER(product_type) LIKE ?', ['%' . $keyword . '%']);
        $sim_lists = $query->latest()->get();
        $additional_prices = PriceList::latest()->get();
        return view($route, compact('logo', 'title', 'sim_lists', 'additional_prices'));
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
            "usage_locations" => JoyUsageLocation::where('status', 1)->pluck('location')->unique(),
        ];
    }

    // update function for physical + esim
    private function updateJoytel(Joytel $model, JoyUpdateFormRequest $request, string $redirectRoute)
    {
        try {
            $rows = json_decode($request->input('rows_json'), true) ?: [];
            if (!is_array($rows)) $rows = [];

            $status = $request->input('status');

            $productCodes = [];
            foreach ($rows as &$row) {
                if (!is_array($row)) continue;

                if ($status === "0") {
                    $row['code_status'] = 0;
                }

                // if (isset($row['service_day'])) {
                //     $sd = trim((string)$row['service_day']);
                //     $n = is_numeric($sd) ? (int)$sd : (int)preg_replace('/\D/', '', $sd);
                //     if ($n) $row['service_day'] = $n . ' day';
                // }

                if (!isset($row['product_code'])) {
                    return response()->json(['success' => false, 'message' => 'Each row must have a product code'], 422);
                }

                if (in_array($row['product_code'], $productCodes)) {
                    return response()->json(['success' => false, 'message' => 'Duplicate product code detected: ' . $row['product_code']], 422);
                }

                $productCodes[] = $row['product_code'];
            }
            unset($row);

            $removedPhotos = $request->input('removed_photos', []);
            $photos = $model->photo ?? [];
            foreach ($removedPhotos as $fileToRemove) {
                $filePath = public_path('sim/' . $fileToRemove);
                if (file_exists($filePath)) unlink($filePath);
                if (($key = array_search($fileToRemove, $photos)) !== false) unset($photos[$key]);
            }
            $model->photo = array_values($photos);
            $model->save();

            $filePaths = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('sim'), $fileName);
                    $filePaths[] = $fileName;
                }
            }

            $oldPhotos = is_array($model->photo) ? $model->photo : (json_decode($model->photo, true) ?: []);
            $mergedPhotos = array_merge($oldPhotos, $filePaths);

            $model->update([
                'category_name'     => $request->cat_name,
                'product_name'      => $request->product_name,
                'usage_location'    => $request->locations,
                'supplier'          => $request->supplier,
                'product_type'      => $request->product_type,
                'plan'              => $rows,
                'photo'             => $mergedPhotos,
                'remark'            => $request->product_name,
                'activation_policy' => $request->activation_policy,
                'delivery_time'     => $request->del_time,
                'status'            => $request->status ?? 1,
            ]);

            session()->flash('success', 'SIM updated successfully!');

            return response()->json([
                'success' => true,
                'rows' => $rows,
                'redirect_url' => route($redirectRoute)
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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

        Joytel::create([
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
}
