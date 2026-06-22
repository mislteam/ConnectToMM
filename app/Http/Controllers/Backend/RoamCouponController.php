<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RoamCoupon;
use App\Models\RoamPhysicalSku;
use App\Models\RoamSku;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoamCouponController extends Controller
{
    public function index()
    {
        $coupons = RoamCoupon::all();
        return view('admin.roamsim.coupon.index', compact('coupons'));
    }

    public function create()
    {
        $esim_skus = RoamSku::pluck('country_name');
        $physical_skus = RoamPhysicalSku::pluck('country_name');
        $skus = $esim_skus->merge($physical_skus)->unique()->values();
        return view('admin.roamsim.coupon.create', compact('skus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'plans' => 'required|array',
            'plans.*' => 'string',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'attempt_time' => 'nullable|integer',
            'expired_date' => 'nullable|date',
        ]);

        $expiredDate = null;
        if ($request->expired_date) {
            $expiredDate = Carbon::parse($request->expired_date);
        }

        $plans = $request->plans;
        if (in_array('All', $plans)) {
            $plans = ['All'];
        }

        RoamCoupon::create([
            'code' => $request->code,
            'plans' => $plans,
            'discount_percentage' => $request->discount_percentage,
            'attempt_time' => $request->attempt_time ?? 0,
            'expired_date' => $expiredDate,
        ]);
        return redirect()->route('roam.coupon.index')->with('success', 'Coupon Created Successfully!');
    }

    public function show(RoamCoupon $coupon)
    {
        return view('admin.roamsim.coupon.show', compact('coupon'));
    }

    public function edit(RoamCoupon $coupon)
    {
        $esim_skus = RoamSku::pluck('country_name');
        $physical_skus = RoamPhysicalSku::pluck('country_name');
        $skus = $esim_skus->merge($physical_skus)->unique()->values();
        return view('admin.roamsim.coupon.edit', compact('coupon', 'skus'));
    }

    public function update(Request $request, RoamCoupon $coupon)
    {
        $request->validate([
            'code' => 'required|max:255',
            'plans' => 'required|array',
            'plans.*' => 'string',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'attempt_time' => 'nullable|integer',
            'expired_date' => 'nullable|date',
        ]);

        $expiredDate = null;
        if ($request->expired_date) {
            $expiredDate = Carbon::parse($request->expired_date);
        }
        $plans = $request->plans;
        if (in_array('All', $plans)) {
            $plans = ['All'];
        }
        $coupon->update([
            'code' => $request->code,
            'plans' => $request->plans,
            'discount_percentage' => $request->discount_percentage,
            'attempt_time' => $request->attempt_time ?? 0,
            'expired_date' => $expiredDate,
            'is_active' => $request->status
        ]);
        return redirect()->route('roam.coupon.index')->with('success', 'Coupon Updated Successfully!');
    }

    public function delete(Request $request)
    {
        $coupon = RoamCoupon::findOrFail($request->id);
        $coupon->delete();
        session()->flash("success", "Coupon Deleted Successfully!");
        return response()->json(['success' => true]);
    }
}
