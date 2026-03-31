<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    public function index()
    {
        $contact_details = ContactUs::latest()->get();
        return view('admin.message.index', array_merge($this->sharedData(), compact('contact_details')));
    }

    public function show(ContactUs $message)
    {
        $message->status = true;
        $message->save();
        return view('admin.message.show', array_merge($this->sharedData(), compact('message')));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|required|max:255',
            'email' => 'string|email|max:255',
            'phone' => 'string|required',
            'msg' => 'string|required',
        ]);
        ContactUs::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['msg'],
        ]);

        return back()->with('success', 'Contact Successfully sent to Admin!');
    }

    // shared Data
    private function sharedData()
    {
        return [
            "logo" => \App\Models\GeneralSetting::where('type', 'file')->first(),
            "title" => \App\Models\GeneralSetting::where('type', 'string')->first(),
        ];
    }
}
