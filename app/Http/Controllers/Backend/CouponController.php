<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        $coupons = Coupon::latest()->paginate(10);
        return view('admin.coupons.index', compact('logo', 'title', 'coupons'));
    }

    public function create()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.coupons.create', compact('logo', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'amount' => 'required|integer',
            'attempt_time' => 'nullable|integer',
            'expired_date' => 'nullable|date',
        ]);

        $expiredDate = null;
        if ($request->expired_date) {
            $expiredDate = Carbon::parse($request->expired_date);
        }

        Coupon::create([
            'code' => $request->code,
            'coupon_amount' => $request->amount,
            'attempt_time' => $request->attempt_time ?? 0,
            'expired_date' => $expiredDate,
        ]);
        return redirect()->route('coupon.index')->with('success', 'Coupon Created Successfully!');
    }

    public function show(Coupon $coupon)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.coupons.show', compact('logo', 'title', 'coupon'));
    }

    public function edit(Coupon $coupon)
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        $title = GeneralSetting::where('type', 'string')->first();
        return view('admin.coupons.edit', compact('logo', 'title', 'coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|max:255',
            'amount' => 'required|integer',
            'attempt_time' => 'nullable|integer',
            'expired_date' => 'nullable|date',
            'status' => 'required|in:0,1',
        ]);

        $expiredDate = null;
        if ($request->expired_date) {
            $expiredDate = Carbon::parse($request->expired_date);
        }
        $coupon->update([
            'code' => $request->code,
            'coupon_amount' => $request->amount,
            'attempt_time' => $request->attempt_time ?? 0,
            'expired_date' => $expiredDate,
            'is_active' => $request->status
        ]);
        return redirect()->route('coupon.index')->with('success', 'Coupon Updated Successfully!');
    }
}
