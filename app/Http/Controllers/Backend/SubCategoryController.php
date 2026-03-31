<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index(){
        $sub_categories=SubCategory::latest()->get();
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.subcategory.index',compact('sub_categories','logo','title'));
    }

    public function create(){
        $categories=Category::all();
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.subcategory.create',compact('categories','logo','title'));
    }

    public function store(Request $request){
        $data=$request->validate([
            'sub_cat_name'=>'required|string|max:255',
            'category'=>'required|exists:categories,id',
        ],[
            'sub_cat_name.required'=>'Sub Category Name is required.',
            'category.required'=>'Category Name is required',
            'category.exists'=>'Category is not existed.'
        ]);
        SubCategory::create([
            'cat_id'=>$data['category'],
            'sub_cat_name'=>$data['sub_cat_name'],
        ]);
        return redirect()->route('subcategoryIndex');
    }

    public function edit(SubCategory $subCategory){
        $categories=Category::all();
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.subcategory.edit',compact('subCategory','categories','logo','title'));
    }

    public function update(SubCategory $subcategory,Request $request)
    {
        $data=$request->validate([
            'sub_cat_name'=>'required|string|max:255',
            'category'=>'required|exists:categories,id',
        ]);

        $subcategory->update([
            'sub_cat_name'=>$data['sub_cat_name'],
            'cat_id'=>$data['category'],
        ]);
        
        return redirect()->route('subcategoryIndex');
    }
}
