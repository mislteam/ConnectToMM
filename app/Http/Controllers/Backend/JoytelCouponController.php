<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JoytelEsim;
use App\Models\JoytelPhysical;
use App\Models\JoytelCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JoytelCouponController extends Controller
{
    public function index()
    {
        $coupons = JoytelCoupon::all();
        return view('admin.joytel.coupon.index', compact('coupons'));
    }

    public function create()
    {
        $esim_product_names = JoytelEsim::pluck('product_name');
        $physical_product_names = JoytelPhysical::pluck('product_name');
        $product_names = $esim_product_names->merge($physical_product_names)->unique()->values();
        return view('admin.joytel.coupon.create', compact('product_names'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'product_names' => 'required|array',
            'product_names.*' => 'string',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'attempt_time' => 'nullable|integer',
            'expired_date' => 'nullable|date',
        ]);

        $expiredDate = null;
        if ($request->expired_date) {
            $expiredDate = Carbon::parse($request->expired_date);
        }

        $product_names = json_encode($request->product_names, true);

        JoytelCoupon::create([
            'code' => $request->code,
            'product_names' => $product_names,
            'discount_percentage' => $request->discount_percentage,
            'attempt_time' => $request->attempt_time ?? 0,
            'expired_date' => $expiredDate,
        ]);
        return redirect()->route('joytel.coupon.index')->with('success', 'Coupon Created Successfully!');
    }

    public function edit(JoytelCoupon $coupon)
    {
        $esim_product_names = JoytelEsim::pluck('product_name');
        $physical_product_names = JoytelPhysical::pluck('product_name');
        $product_names = $esim_product_names->merge($physical_product_names)->unique()->values();
        $db_products = json_decode($coupon->product_names, true);
        return view('admin.joytel.coupon.edit', compact('coupon', 'product_names', 'db_products'));
    }

    public function update(Request $request, JoytelCoupon $coupon)
    {
        $request->validate([
            'code' => 'required|max:255',
            'product_names' => 'required|array',
            'product_names.*' => 'string',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'attempt_time' => 'nullable|integer',
            'expired_date' => 'nullable|date',
            'status' => 'required|in:0,1',
        ]);

        $expiredDate = null;
        if ($request->expired_date) {
            $expiredDate = Carbon::parse($request->expired_date);
        }
        $product_names = $request->product_names;
        if (in_array('All', $product_names)) {
            $product_names = ['All'];
        }

        $coupon->update([
            'code' => $request->code,
            'product_names' => json_encode($product_names, true),
            'discount_percentage' => $request->discount_percentage,
            'attempt_time' => $request->attempt_time ?? 0,
            'expired_date' => $expiredDate,
            'is_active' => $request->status
        ]);
        return redirect()->route('joytel.coupon.index')->with('success', 'Coupon Updated Successfully!');
    }

    public function show(JoytelCoupon $coupon)
    {
        return view('admin.joytel.coupon.show', compact('coupon'));
    }

    public function delete(Request $request)
    {
        $coupon = JoytelCoupon::findOrFail($request->id);
        $coupon->delete();
        session()->flash("success", "Coupon Deleted Successfully!");
        return response()->json(['success' => true]);
    }
}
