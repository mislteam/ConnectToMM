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

    public function show(Customer $customer)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.customer.show', compact('logo', 'title', 'customer'));
    }

    public function edit(Customer $customer)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.customer.edit', compact('customer', 'logo', 'title'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('profile_images', $imageName, 'public');

            $customer->profile_image = $imageName;
            $customer->save();
        }

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->save();

        return redirect()->route('show.admin')->with('success', 'Customer updated successfully.');
    }
}
