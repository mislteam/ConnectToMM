<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.setting.permission.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.setting.permission.create');
    }

    public function roleStore(Request $request)
    {
        $data = $request->validate([
            'role_name' => 'required|string|max:255',
        ]);
        Role::create([
            'name' => $data['role_name'],
            'guard_name' => 'web'
        ]);
        return redirect()->route('permission.index')->with('success', 'Role saved successfully!');
    }

    public function edit($role)
    {
        $role = Role::findOrFail($role);
        $generalJoyTitle = \App\Models\GeneralSetting::where('name', 'joytel_title')->first();
        $generalRoamTitle = \App\Models\GeneralSetting::where('name', 'roam_title')->first();
        $menuOrder = [
            'dashboard' => 'Dashboards',
            'order' => 'All Orders',
            'customer' => 'All Customer',
            'joytel.esim' => $generalJoyTitle->value . ' eSIM',
            'joytel.physical' => $generalJoyTitle->value . ' Physical',
            'joytel.region' => $generalJoyTitle->value . ' Location',
            'joytel.coupon' => $generalJoyTitle->value . ' Coupon',
            'roam.esim' => $generalRoamTitle->value . ' eSIM',
            'roam.physical' => $generalRoamTitle->value . ' Physical',
            'roam.esimSKU' => $generalRoamTitle->value . ' esim SKUs',
            'roam.physicalSKU' => $generalRoamTitle->value . ' Physical SKUs',
            'roam.api-credentials' => $generalRoamTitle->value . ' API Credentials',
            'roam.esim-update' => $generalRoamTitle->value . ' eSIM Update Data',
            'roam.physical-update' => $generalRoamTitle->value . ' Physical Update Data',
            'admin' => 'All Admin',
            'message' => 'Messages',
            'blog' => 'Blog',
            'blog.category' => 'Blog Category',
            'general' => 'General Setting',
            'permission' => 'Permission',
            'currency' => 'Currency',
            'payment' => 'Payment Setting',
            'page' => 'Page',
        ];
        $actionOrder = ['menu', 'view', 'create', 'edit', 'delete'];

        $permissions = Permission::all()->pluck('name')->toArray();

        $permissionMap = [];

        foreach ($menuOrder as $module => $label) {
            foreach ($actionOrder as $action) {
                $perm = "{$module}.{$action}";
                if (in_array($perm, $permissions)) {
                    $permissionMap[$module]['label'] = $label;
                    $permissionMap[$module]['actions'][] = $action;
                }
            }
        }

        return view('admin.setting.permission.edit', compact('role', 'permissionMap'));
    }

    public function update(Request $request)
    {
        $role = Role::findById($request->id);

        if (!$role) {
            return back()->with('error', 'Role not found');
        }

        $submitted = $request->permissions ?? [];
        $role->syncPermissions($submitted);
        return back()->with('success', 'Permissions updated successfully');
    }
}
