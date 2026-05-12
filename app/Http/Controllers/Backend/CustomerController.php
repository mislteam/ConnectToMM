<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $customers = Customer::latest()->get();
        return view('admin.customer.index', compact('logo', 'title', 'customers'));
    }
}
