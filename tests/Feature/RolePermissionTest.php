<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('seeder creates expected roles', function () {
    $this->assertDatabaseHas('roles', ['name' => 'admin', 'guard_name' => 'web']);
    $this->assertDatabaseHas('roles', ['name' => 'user', 'guard_name' => 'web']);
});

test('seeder creates expected permissions', function () {
    $this->assertDatabaseHas('permissions', ['name' => 'manage users', 'guard_name' => 'web']);
    $this->assertDatabaseHas('permissions', ['name' => 'manage settings', 'guard_name' => 'web']);
});

test('admin role has all permissions', function () {
    $admin = Role::findByName('admin');

    expect($admin->getAllPermissions()->count())
        ->toBe(Permission::count());
});

test('user role has only assigned permissions', function () {
    $userRole = Role::findByName('user');

    expect($userRole->hasPermissionTo('view users'))->toBeFalse();
    expect($userRole->hasPermissionTo('create users'))->toBeFalse();
    expect($userRole->hasPermissionTo('manage settings'))->toBeFalse();
});

test('user can be assigned a role', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('user'))->toBeFalse();
});

test('user can have multiple roles', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $user->assignRole('user');

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('user'))->toBeTrue();
    expect($user->hasAnyRole(['admin', 'user']))->toBeTrue();
    expect($user->hasAllRoles(['admin', 'user']))->toBeTrue();
});

test('user inherits permissions from role', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    expect($user->can('view users'))->toBeFalse();
    expect($user->can('manage users'))->toBeFalse();
});

test('user with admin role has all permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    foreach (Permission::all() as $permission) {
        expect($user->can($permission->name))->toBeTrue();
    }
});

test('role can be removed from user', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $user->removeRole('admin');

    expect($user->hasRole('admin'))->toBeFalse();
});

test('direct permissions can be given to user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage users');

    expect($user->can('manage users'))->toBeTrue();
    expect($user->hasAllPermissions(['manage users']))->toBeTrue();
});

test('direct permissions can be revoked from user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage users');
    $user->revokePermissionTo('manage users');

    expect($user->can('manage users'))->toBeFalse();
});

test('roles can be created and synced', function () {
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role->syncPermissions(['view users', 'manage settings']);

    expect($role->hasPermissionTo('view users'))->toBeTrue();
    expect($role->hasPermissionTo('manage settings'))->toBeTrue();
    expect($role->hasPermissionTo('create users'))->toBeFalse();
});
