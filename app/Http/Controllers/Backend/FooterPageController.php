<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;
use App\Models\Link;
use Illuminate\Http\Request;

class FooterPageController extends Controller
{
    public function contactIndex()
    {
        $contactInfo = ContactInfo::first();
        return view('admin.page.footer.contactInfo.index', array_merge($this->sharedData(), compact('contactInfo')));
    }

    public function supportIndex()
    {
        $supportLinks = Link::where('type', 'support')->get();
        return view('admin.page.footer.support.index', array_merge($this->sharedData(), compact('supportLinks')));
    }

    public function importantIndex()
    {
        $importantLinks = Link::where('type', 'important')->get();
        return view('admin.page.footer.important.index', array_merge($this->sharedData(), compact('importantLinks')));
    }

    public function contactEdit(ContactInfo $info)
    {

        return view('admin.page.footer.contactInfo.edit', array_merge($this->sharedData(), compact('info')));
    }

    public function importantEdit(Link $link)
    {
        return view('admin.page.footer.important.edit', array_merge($this->sharedData(), compact('link')));
    }

    public function supportEdit(Link $support)
    {
        return view('admin.page.footer.support.edit', array_merge($this->sharedData(), compact('support')));
    }

    public function contactUpdate(ContactInfo $info, Request $request)
    {
        $data = $request->validate([
            'description'           => 'required|string',
            'email'                 => 'required|email',
            'phone'                 => 'required|string',
            'joytel_image' => 'nullable|image|mimes:jpeg,png',
            'roam_image' => 'nullable|image|mimes:jpeg,png',
            'title'                 => 'nullable|array',
            'title.*'               => 'required_with:title|string',
            'icon_name'             => 'nullable|array',
            'icon_name.*'           => 'required_with:icon_name|string',
            'other_social_url'      => 'nullable|array',
            'other_social_url.*'    => 'required_with:other_social_url|url',
        ]);
        $socialLinks = [];

        if ($request->has('title')) {
            $titles = $request->input('title', []);
            $icons  = $request->input('icon_name', []);
            $urls   = $request->input('other_social_url', []);

            foreach ($titles as $i => $title) {
                $socialLinks[] = [
                    'title' => $title,
                    'icon'  => $icons[$i] ?? '',
                    'link'  => $urls[$i] ?? '',
                ];
            }
        }

        if ($request->hasFile('joytel_image')) {
            image_delete('general/sim_imgs', $info->joytel_image);
            $info->joytel_image = store_image($request->file('joytel_image'), 'general/sim_imgs');
        }

        if ($request->hasFile('roam_image')) {
            image_delete('general/sim_imgs', $info->roam_image);
            $info->roam_image = store_image($request->file('roam_image'), 'general/sim_imgs');
        }

        $info->update(array_merge($data, [
            'social_media_links' => $socialLinks,
            'joytel_image' => $info->joytel_image ?? null,
            'roam_image' => $info->roam_image ?? null,
        ]));

        return redirect()->route('footer.contact.index')
            ->with('success', 'Contact Info Updated Successfully!');
    }


    public function importantUpdate(Link $link, Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'link' => 'required|string',
            'type' => 'required|string',
        ]);
        $link->update($data);
        return redirect()->route('footer.important.index')->with('success', 'Important Links Updated Successfully!');
    }

    public function supportUpdate(Link $support, Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'link' => 'required|string',
            'type' => 'required|string',
        ]);
        $support->update($data);
        return redirect()->route('footer.support.index')->with('success', 'Support Updated Successfully!');
    }

    private function sharedData()
    {
        return [
            "logo" => \App\Models\GeneralSetting::where('type', 'file')->first(),
            "title" => \App\Models\GeneralSetting::where('type', 'string')->first(),
        ];
    }
}
