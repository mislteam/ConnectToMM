<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneralSetting;
use App\Models\User;

class AdminController extends Controller
{
   public function index(Request $request)
{
    $users = User::paginate(10);
     $logo=GeneralSetting::where('type','file')->first();
    $title=GeneralSetting::where('type','string')->first();

    return view('admin.alladmin.index', compact('users','logo','title'));
}
//Admin View Page
public function view($id)
{
    $users = User::find($id);
     $logo=GeneralSetting::where('type','file')->first();
    $title=GeneralSetting::where('type','string')->first();
    return view('admin.alladmin.view', compact('users','logo','title'));
}

    // admin create page
    public function create()
    {
        $logo=GeneralSetting::where('type','file')->first();
        $title=GeneralSetting::where('type','string')->first();
        return view('admin.alladmin.create',compact('logo','title'));
    }

     // Store Admin
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:editor',
            'password' => 'required|min:8|confirmed',
        ]);

        // Upload image if exists
        $imageName = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName(); // Unique name
            $image->storeAs('profile_images', $imageName, 'public');     // Store in /storage/app/public/profile_images
        }

        User::create([
            'profile_image' => $imageName,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('show.admin')->with('success', 'Admin created successfully.');
    }
    
    // admin edit page
    public function edit($id)
{
    $user = User::findOrFail($id);
     $logo=GeneralSetting::where('type','file')->first();
    $title=GeneralSetting::where('type','string')->first();
    return view('admin.alladmin.edit', compact('user','logo','title'));
}

    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

        $request->validate([
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
    ]);
 


    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('profile_images', $imageName, 'public');

        $user->profile_image = $imageName;
        $user->save();
    }else{
        return 'fail';
    }

    $user->name = $request->name;
    $user->email = $request->email;
    $user->save();

    return redirect()->route('show.admin')->with('success', 'User updated successfully.');
}

    // Delete Admin
    public function destroy($id)
    {
        $admin = User::findOrFail($id);

        // Delete image if exists
                if ($admin->profile_image && Storage::disk('public')->exists($admin->profile_image)) {
                Storage::disk('public')->delete($admin->profile_image);
            }

            // Delete user
            $admin->delete();

        return redirect()->back()->with('success', 'Admin deleted successfully.');
    }




    public function changePassword(Request $request)
{
    $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|min:8|confirmed', // confirmed means it must match new_password_confirmation
    ]);

    $user = Auth::user();

    if (!Hash::check($request->old_password, $user->password)) {
        return back()->withErrors(['old_password' => 'Old password is incorrect.']);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();

    return back()->with('success', 'Password changed successfully.');
}
    
}
