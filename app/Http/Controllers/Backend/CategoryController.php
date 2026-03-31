<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        $categories=Category::latest()->get();
        return view('admin.category.index',compact('categories','logo','title'));
    }

    public function create(){
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.category.create',compact('logo','title'));
    }

    public function store(Request $request){
        $data=$request->validate([
            'cat_name'=>'required|string|max:255',
        ]);
        Category::create($data);
        return redirect()->route('categoryIndex');
    }

    public function edit(Category $category){
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.category.edit',compact('category','logo','title'));
    }

    public function update(Category $category,Request $request){
        $data=$request->validate([
            'cat_name'=>'required|string|max:255'
        ]);
        $category->cat_name=$data['cat_name'];
        $category->save();
        return redirect()->route('categoryIndex');
    }
}
