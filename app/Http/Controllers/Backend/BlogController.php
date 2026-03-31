<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->get();
        return view('admin.blog.index', array_merge($this->sharedData(), compact('blogs')));
    }

    public function create()
    {
        $categories = BlogCategory::all();
        return view('admin.blog.create', array_merge($this->sharedData(), compact('categories')));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png',
        ]);
        if ($request->hasFile('image')) {
            $fileName = store_image($request->file('image'), 'blog');
        }
        Blog::create([
            'title' => $data['title'],
            'desc' => $data['desc'],
            'category_id' => $data['category_id'],
            'image' => $fileName ?? null,
        ]);
        return redirect()->route('blog.index')->with('success', 'Blog Created Successfully!');
    }

    public function edit(Blog $blog)
    {
        $categories = BlogCategory::all();
        return view('admin.blog.edit', array_merge($this->sharedData(), compact('blog', 'categories')));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png',
        ]);
        if ($request->hasFile('image')) {
            if ($blog->image) {
                $oldPath = public_path('blog/' . $blog->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $blog->image = store_image($request->file('image'), 'blog');
        }
        $blog->update([
            'title' => $data['title'],
            'desc' => $data['desc'],
            'category_id' => $data['category_id'],
            'image' => $blog->image,
        ]);
        return redirect()->route('blog.index')->with('success', 'Blog Updated Successfully!');
    }

    public function destory($blog)
    {
        $blog = Blog::findOrFail($blog);

        $blog->delete();
        session()->flash("success", "Blog Deleted Successfully!");
        return response()->json(['message' => 'Blog deleted successfully!']);
    }

    private function sharedData()
    {
        return [
            "logo" => \App\Models\GeneralSetting::where('type', 'file')->first(),
            "title" => \App\Models\GeneralSetting::where('type', 'string')->first(),
        ];
    }
}
