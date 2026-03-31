<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(){
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.dashboard',compact('logo','title'));
    }
}
