<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);
});

test('administrator is seeded with admin role', function () {
    $admin = User::where('email', 'admin@example.com')->first();

    expect($admin)->not->toBeNull();
    expect($admin->name)->toBe('Administrator');
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasRole('user'))->toBeFalse();
});

test('administrator can manage all permissions', function () {
    $admin = User::where('email', 'admin@example.com')->first();

    foreach (Permission::all() as $permission) {
        expect($admin->can($permission->name))->toBeTrue();
    }
});

test('regular user is seeded with user role', function () {
    $user = User::where('email', 'user@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Regular User');
    expect($user->hasRole('user'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
});

test('regular user has no extra permissions', function () {
    $user = User::where('email', 'user@example.com')->first();

    expect($user->can('view users'))->toBeFalse();
    expect($user->can('create users'))->toBeFalse();
    expect($user->can('manage settings'))->toBeFalse();
});

test('seeder is idempotent', function () {
    $this->seed(UserSeeder::class);

    $adminCount = User::where('email', 'admin@example.com')->count();
    $userCount = User::where('email', 'user@example.com')->count();

    expect($adminCount)->toBe(1);
    expect($userCount)->toBe(1);
});
