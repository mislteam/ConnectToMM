<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::latest()->get();
        return view('admin.category.index', array_merge($this->sharedData(), compact('categories')));
    }

    public function create()
    {
        return view('admin.category.create', $this->sharedData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cat_name' => 'required|string|max:255'
        ]);
        BlogCategory::create($data);
        return redirect()->route('blog.category.index')->with('success', 'Category Created Successfully!');
    }

    public function edit(BlogCategory $category)
    {
        return view('admin.category.edit', array_merge($this->sharedData(), compact('category')));
    }

    public function update(Request $request, BlogCategory $category)
    {
        $data = $request->validate([
            'cat_name' => 'required|string|max:255'
        ]);
        $category->update($data);
        return redirect()->route('blog.category.index')->with('success', 'Category Updated Successfully!');
    }

    private function sharedData()
    {
        return [
            "logo" => \App\Models\GeneralSetting::where('type', 'file')->first(),
            "title" => \App\Models\GeneralSetting::where('type', 'string')->first(),
        ];
    }
}
