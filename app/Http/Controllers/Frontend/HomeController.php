<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\HelpSection;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function index()
    {
        $help_section = get_section('need_more_help');
        $simple_section = get_section('simple_transparent_secure');
        $action_section = get_section('what_we_do');
        $service_section = get_section('our_services');
        $manage_section = get_section('manage_section');
        $about_repay = get_section('about_connect_to_myanmar');
        return view('frontend.home', compact('help_section', 'simple_section', 'action_section', 'service_section', 'manage_section', 'about_repay'));
    }

    public function about()
    {
        $banner = Banner::where('banner_type', 'about_us')->first();
        $company = get_section('about_company');
        $about_repay = get_section('about_connect_to_myanmar');
        $work_section = get_section('how_we_work');
        $faq_section = get_section('frequently_asked_questions');
        $faqs = Faq::latest()->take(3)->get();
        return view('frontend.about', compact('banner', 'company', 'about_repay', 'work_section', 'faq_section', 'faqs'));
    }

    public function faq()
    {
        $banner = Banner::where('banner_type', 'faq')->first();
        $faqs = Faq::latest()->get();
        $section = Section::where('section_key', 'need_more_help')->first();
        return view('frontend.faq', compact('banner', 'faqs', 'section'));
    }

    public function blog()
    {
        $banner = Banner::where('banner_type', 'blog')->first();
        $blogs = Blog::latest()->get();
        return view('frontend.blog', compact('banner', 'blogs'));
    }

    public function contact()
    {
        $banner = Banner::where('banner_type', 'contact_us')->first();
        $section = Section::where('section_key', 'need_more_help')->first();
        return view('frontend.contact', compact('banner', 'section'));
    }

    public function customerProfile()
    {
        $banner = Banner::where('banner_type', 'my_account')->first();
        return view('frontend.user.profile', compact('banner'));
    }

    public function customerEdit(Customer $customer, $edit_type, Request $request)
    {
        if ($edit_type === 'profile') {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'file' => 'nullable|image|mimes:jpeg,jpg,png'
            ]);
            $customer->name = $request->name;
            $customer->email = $request->email;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('profile_images', $fileName, 'public');

                $customer->profile_image = $fileName;
                $customer->save();
            }
            $customer->save();
            return back()->with('success', 'Customer Profile Updated Successfully!');
        } else if ($edit_type === 'password') {
            $request->validate([
                'old_password' => 'required|min:8',
                'new_password' => 'required|min:8|different:old_password',
                'confirm_password' => 'required|same:new_password'
            ]);

            if (!Hash::check($request->old_password, $customer->password)) {
                return back()->withErrors(['old_password' => 'Old password is incorrect.']);
            }

            $customer->password = Hash::make($request->new_password);
            $customer->save();
            auth()->logout();
            return redirect()->route('user.login');
        }
    }

    public function orderDetail()
    {
        return view('frontend.user.order-detail');
    }
}
