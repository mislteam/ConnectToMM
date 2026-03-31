<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $generals = GeneralSetting::all();
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.setting.general.index', compact('generals','logo','title'));
    }

    public function edit(GeneralSetting $data)
    {
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.setting.general.edit', compact('data','logo','title'));
    }

    public function update(GeneralSetting $data, Request $request)
    {
        if ($data->type == 'file') {
            $request->validate([
                'file' => 'required|file|mimes:png,jpg,svg|max:2048'
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $value = time() . '_' . $file->getClientOriginalName();
                $path = public_path('general/logo');
                if (file_exists($path . '/' . $data->value)) {
                    unlink($path . '/' . $data->value);
                }
            }
            $file->move($path, $value);
            $data->value = $value;
            $data->save();
        } elseif ($data->type == 'string') {
            $request->validate([
                'title'=>'required|string|max:255'
            ]);
            $data->value=$request->title;
            $data->save();
        }

        return redirect()->route('generalIndex');
    }
}
