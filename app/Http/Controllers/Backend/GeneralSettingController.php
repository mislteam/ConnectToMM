<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $generals = GeneralSetting::whereIn('name', ['logo', 'title'])->get();
        $logo = GeneralSetting::where('name', 'logo')->first();
        $title = GeneralSetting::where('name', 'title')->first();
        return view('admin.setting.general.index', compact('generals', 'logo', 'title'));
    }

    public function edit($type)
    {
        $orderTypes = null;
        $simTypes = null;
        if ($type === "roam") {
            $orderTypes = json_decode(
                GeneralSetting::where('name', 'roam_order_types')->value('value'),
                true
            ) ?? [];
            $simTypes = json_decode(
                GeneralSetting::where('name', 'roam_sim_types')->value('value'),
                true
            ) ?? [];
        } else if ($type === "joytel") {
            $orderTypes = json_decode(
                GeneralSetting::where('name', 'joytel_order_types')->value('value'),
                true
            ) ?? [];
            $simTypes = json_decode(
                GeneralSetting::where('name', 'joytel_sim_types')->value('value'),
                true
            ) ?? [];
        }

        $data = GeneralSetting::where('name', 'like', $type . '%')->get()->keyBy('name');
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.setting.general.edit', compact('data', 'type', 'logo', 'title', 'orderTypes', 'simTypes'));
    }

    public function update($type, Request $request)
    {
        if ($type === 'logo') {
            $request->validate([
                'file' => 'required|file|mimes:png,jpg,svg|max:2048'
            ]);

            $oldLogo = GeneralSetting::where('name', $type)->first();

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $value = time() . '_' . $file->getClientOriginalName();
                $path = public_path('general/logo');
                if (file_exists($path . '/' . $oldLogo->value)) {
                    unlink($path . '/' . $oldLogo->value);
                }
            }
            $file->move($path, $value);
            $oldLogo->value = $value;
            $oldLogo->save();
        } elseif ($type === 'title') {
            $oldTitle = GeneralSetting::where('name', $type)->first();
            $request->validate([
                'title' => 'required|string|max:255'
            ]);
            $oldTitle->value = $request->title;
            $oldTitle->save();
        } elseif (in_array($type, ['joytel', 'roam'])) {
            $request->validate([
                $type . '_title' => 'required|string|max:255',
                $type . '_image' => 'nullable|file|mimes:png,jpg,svg|max:2048',
                $type . '_esim' => 'nullable|in:0,1',
                $type . '_physical' => 'nullable|in:0,1',
            ]);

            $title = GeneralSetting::where('name', $type . '_title')->first();
            $image = GeneralSetting::where('name', $type . '_logo')->first();

            $title->value = $request->{$type . '_title'};
            $title->save();

            if ($request->hasFile($type . '_image')) {
                $file = $request->file($type . '_image');
                $value = time() . '_' . $file->getClientOriginalName();
                $path = public_path('general/logo');
                if ($image && $image->value && file_exists($path . '/' . $image->value)) {
                    unlink($path . '/' . $image->value);
                }
                $file->move($path, $value);
                $image->value = $value ?? $image->value;
                $image->save();
            }

            $simTypes = [
                'esim' => $request->has($type . '_esim') ? 1 : 0,
                'physical' => $request->has($type . '_physical') ? 1 : 0,
            ];

            GeneralSetting::updateOrCreate(
                [
                    'name' => $type . '_sim_types'
                ],
                [
                    'value' => json_encode($simTypes),
                    'type' => 'json'
                ]
            );

            $orderTypes = [];
            if ($request->has($type . '_esim_new')) {
                $orderTypes[] = 'esim_new';
            }
            if ($request->has($type . '_esim_recharge')) {
                $orderTypes[] = 'esim_recharge';
            }
            if ($request->has($type . '_physical_new')) {
                $orderTypes[] = 'physical_new';
            }
            if ($request->has($type . '_physical_recharge')) {
                $orderTypes[] = 'physical_recharge';
            }

            $hasEsim = in_array('esim_new', $orderTypes) || in_array('esim_recharge', $orderTypes);

            if (!$hasEsim) {
                return back()
                    ->withInput()
                    ->with('error', 'At least one eSIM order type must be enabled.');
            }

            $hasPhysical =
                in_array('physical_new', $orderTypes) ||
                in_array('physical_recharge', $orderTypes);

            if (!$hasPhysical) {
                return back()
                    ->withInput()
                    ->with('error', 'At least one Physical order type must be enabled.');
            }
            $ordertype = GeneralSetting::firstOrNew([
                'name' => $type . '_order_types'
            ]);
            $ordertype->value = json_encode($orderTypes);
            $ordertype->type = 'string';
            $ordertype->save();
        }

        return redirect()->route('generalIndex')->with('success', 'Update Successfully!');
    }
}
