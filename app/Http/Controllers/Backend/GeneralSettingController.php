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
        $data = GeneralSetting::where('name', 'like', $type . '%')->get()->keyBy('name');
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.setting.general.edit', compact('data', 'type', 'logo', 'title'));
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
                $type . '_image' => 'nullable|file|mimes:png,jpg,svg|max:2048'
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
        }

        return redirect()->route('generalIndex')->with('success', 'Update Successfully!');
    }
}
