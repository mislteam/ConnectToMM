<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Item;
use App\Models\Section;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // section & items
    public function homeIndex()
    {
        return $this->sectionItems('home');
    }

    public function commonIndex()
    {
        return $this->sectionItems('all');
    }

    public function aboutIndex()
    {
        return $this->sectionItems('aboutus');
    }

    public function sectionEdit(string $section_key, Section $section)
    {
        $sectionConfig = config("sections.$section_key");
        $route = null;
        if ($sectionConfig['page'] === "home") {
            $route = "page.home.index";
        } else if ($sectionConfig['page'] === "all") {
            $route = "page.common.index";
        }
        return view('admin.page.section.edit', array_merge($this->sharedData(), compact('section_key', 'section', 'route')));
    }

    public function sectionUpdate(Request $request, Section $section)
    {
        // dd($request->all());
        $allowKeys = array_keys(config('sections'));
        $data = $request->validate([
            // section validation
            'section_key' => ['required', 'in:' . implode(',', $allowKeys)],
            'eyebrow_text' => 'required|string|max:255',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'image' => 'nullable|mimes:png,jpeg|image',
            'video_url' => 'nullable',
            // item validation
            'items.*.button_text' => 'nullable|string|max:255',
            'items.*.button_url' => 'nullable|string|max:255',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.item_image' => 'nullable|image|mimes:jpeg,png',
        ]);

        if ($request->hasFile('image')) {
            if ($section->image) {
                $oldPath = public_path('section/' . $section->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $section->image = store_image($request->file('image'), 'section');
        }

        $section->update([
            'eyebrow_text' => $data['eyebrow_text'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image' => $section->image,
            'video' => $data['video_url'] ?? null
        ]);

        if ($request->has('items')) {
            foreach ($request->items as $itemId => $itemData) {
                $item = Item::where('id', $itemId)
                    ->where('section_id', $section->id)
                    ->first();

                if (!$item) continue;
                if ($request->hasFile("items.$itemId.item_image")) {
                    if ($item->item_image) {
                        $oldPath = public_path('item/' . $item->item_image);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $item->item_image = store_image(
                        $request->file("items.$itemId.item_image"),
                        'item'
                    );
                }
                unset($itemData['item_image']);
                $item->update($itemData);
            }
        }
        return back()->with('success', 'Section Updated Successfully!');
    }
    // section & items
    public function catalogIndex()
    {
        $sections = Section::all();
        $items = Item::all();
        return view('admin.page.catalog.index', array_merge($this->sharedData(), compact('sections', 'items')));
    }

    // banner
    public function bannerIndex()
    {
        $banners = Banner::all();
        return view('admin.page.banner.index', array_merge($this->sharedData(), compact('banners')));
    }

    public function bannerEdit(Banner $banner)
    {
        $banner_types = Banner::pluck('banner_type');
        return view('admin.page.banner.edit', array_merge($this->sharedData(), compact('banner', 'banner_types')));
    }

    public function bannerUpdate(Banner $banner, Request $request)
    {
        $request->validate([
            'page'        => 'required|string|max:255',
            'title'       => 'required|string|max:255',
            'subtitle'    => 'required|string',
            'banner_type' => 'required|string',
            'image'       => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image) {
                $oldPath = public_path('banner/' . $banner->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('banner'), $fileName);

            $banner->image = $fileName;
        }

        $banner->fill($request->except('image'));
        $banner->save();
        return redirect()->route('page.banner.index')->with('success', 'Banner Updated Successfully!');
    }

    // faq
    public function faqIndex()
    {
        $faqs = Faq::latest()->get();
        return view('admin.page.faq.index', array_merge($this->sharedData(), compact('faqs')));
    }

    public function faqCreate()
    {
        return view('admin.page.faq.create', $this->sharedData());
    }

    public function faqStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);
        Faq::create($data);
        return redirect()->route('page.faq.index')->with('success', 'FAQ Created Successfully!');
    }

    public function faqEdit(Faq $faq)
    {
        return view('admin.page.faq.edit', array_merge($this->sharedData(), compact('faq')));
    }

    public function faqUpdate(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);
        $faq->update($data);
        return redirect()->route('page.faq.index')->with('success', 'FAQ Updated Successfully!');
    }

    public function faqDelete(Faq $faq)
    {
        $faq->delete();
        return response()->json(['message' => 'FAQ Deleted Successfully!']);
    }

    // shared Data
    private function sharedData()
    {
        return [
            "logo" => \App\Models\GeneralSetting::where('type', 'file')->first(),
            "title" => \App\Models\GeneralSetting::where('type', 'string')->first(),
        ];
    }

    // sections & items
    private function sectionItems($section_key)
    {
        $keys = collect(config('sections'))
            ->filter(fn($section) => $section['page'] === $section_key)
            ->keys()
            ->toArray();
        $sections = Section::whereIn('section_key', $keys)->get();
        return view('admin.page.section.index', array_merge($this->sharedData(), compact('sections', 'section_key')));
    }
}
