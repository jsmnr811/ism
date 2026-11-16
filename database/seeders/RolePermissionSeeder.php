<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $broadPermissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage settings',
        ];

        $granularPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'impersonate users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'view meetings',
            'create meetings',
            'edit meetings',
            'delete meetings',
            'view missions',
            'create missions',
            'edit missions',
            'delete missions',
            'view site visits',
            'create site visits',
            'edit site visits',
            'delete site visits',
            'view mission registrations',
            'create mission registrations',
            'edit mission registrations',
            'delete mission registrations',
        ];

        foreach (array_merge($broadPermissions, $granularPermissions) as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions([]);
    }
}
