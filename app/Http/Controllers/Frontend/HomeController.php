<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Faq;
use App\Models\HelpSection;
use App\Models\Section;
use Illuminate\Http\Request;

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
}
