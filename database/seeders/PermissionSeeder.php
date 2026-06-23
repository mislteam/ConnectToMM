<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'administrator')->first();
        $editor = User::where('role', 'editor')->first();
        $admin?->assignRole('administrator');
        $editor?->assignRole('editor');
        $modules = [
            'dashboard',
            'order',
            'customer',
            'joytel.esim',
            'joytel.physical',
            'joytel.region',
            'joytel.api-credentials',
            'roam.esim',
            'roam.physical',
            'roam.esimSKU',
            'roam.physicalSKU',
            'roam.api-credentials',
            'roam.esim-update',
            'roam.physical-update',
            'admin',
            'message',
            'blog',
            'blog.category',
            'page',
            'general',
            'permission',
            'currency',
            'payment',
            'joytel.coupon',
            'roam.coupon'
        ];

        $actions = [
            'menu',
            'view',
            'create',
            'edit',
            'delete'
        ];

        $adminRole = Role::findByName('administrator', 'web');

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web'
                ]);
                $adminRole->givePermissionTo($permission);
            }
        }
    }
}
