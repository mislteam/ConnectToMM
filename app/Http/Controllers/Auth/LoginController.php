<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    public function adminLogin()
    {
        $logo = GeneralSetting::where('type', 'file')->first();
        return view('admin.login', compact('logo'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->status == 0 && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return redirect()->intended('/dashboard')->with('success', 'Welcome from Dashboard');
        }

        return redirect()->intended('/admin/login')->with('error', 'User Not Found');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('admin/login');
    }

    public function userLogin()
    {
        return view('frontend.user.login');
    }
}
