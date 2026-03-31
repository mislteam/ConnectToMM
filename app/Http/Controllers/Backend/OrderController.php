<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(){
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.order.index',compact('logo','title'));
    }
}
