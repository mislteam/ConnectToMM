<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\JoyUsageLocation;
use Illuminate\Http\Request;

class JoyUsageLocationController extends Controller
{
    public function index()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $region_lists = JoyUsageLocation::latest()->get();
        return view('admin.joytel.region.index', compact('logo', 'title', 'region_lists'));
    }

    public function create(){
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $region_names = JoyUsageLocation::pluck('location');
        return view('admin.joytel.region.create',compact('logo','title','region_names'));
    }

    public function store(Request $request){
        $validated=$request->validate([
            'location'=>'required|string|max:255|unique:joy_usage_locations,location',
            'status'=>'required|integer'
        ]);

        JoyUsageLocation::create($validated);
        return redirect()->route('region.index')->with('success','Region created successfully.');
    }

    public function edit(JoyUsageLocation $region)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.joytel.region.edit', compact('logo', 'title', 'region'));
    }

    public function update(Request $request, JoyUsageLocation $region)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        $region->status = $request->status;
        $region->save();

        return redirect()->route('region.index')->with('success', 'Status updated!');
    }
}
