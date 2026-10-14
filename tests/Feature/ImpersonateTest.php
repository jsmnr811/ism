<?php

use App\Livewire\Administration\Users;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('impersonate users permission is seeded', function () {
    $this->assertDatabaseHas('permissions', [
        'name' => 'impersonate users',
        'guard_name' => 'web',
    ]);
});

test('admin role is granted the impersonate users permission', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    expect($admin->can('impersonate users'))->toBeTrue();
});

test('user role is not granted the impersonate users permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user);

    expect($user->can('impersonate users'))->toBeFalse();
});

test('admin can impersonate another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $target = User::factory()->create();

    $response = $this->post(route('impersonate', $target));

    $response->assertRedirect(route('dashboard'));
    expect(auth()->id())->toBe($target->id);
    expect(session('impersonate_user_id'))->toBe($admin->id);
});

test('user without impersonate users permission cannot impersonate', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $target = User::factory()->create();

    $response = $this->post(route('impersonate', $target));

    $response->assertForbidden();
    expect(auth()->id())->toBe($user->id);
    expect(session('impersonate_user_id'))->toBeNull();
});

test('user cannot impersonate themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $response = $this->post(route('impersonate', $admin));

    $response->assertForbidden();
    expect(auth()->id())->toBe($admin->id);
});

test('stop impersonation restores the original user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $target = User::factory()->create();

    $this->post(route('impersonate', $target));

    expect(auth()->id())->toBe($target->id);

    $response = $this->post(route('impersonate.stop'));

    $response->assertRedirect(route('users.index'));
    expect(auth()->id())->toBe($admin->id);
    expect(session('impersonate_user_id'))->toBeNull();
});

test('stop impersonation without an active session redirects to dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $response = $this->post(route('impersonate.stop'));

    $response->assertRedirect(route('dashboard'));
    expect(auth()->id())->toBe($admin->id);
    expect(session('impersonate_user_id'))->toBeNull();
});

test('admin cannot impersonate another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $target = User::factory()->create();
    $target->assignRole('admin');

    $response = $this->post(route('impersonate', $target));

    $response->assertForbidden();
    expect(auth()->id())->toBe($admin->id);
    expect(session('impersonate_user_id'))->toBeNull();
});

test('impersonate button is not rendered for an admin target', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $target = User::factory()->create();
    $target->assignRole('admin');

    Livewire::test(Users::class)
        ->assertDontSee(route('impersonate', $target));
});

test('impersonate button is rendered for an authorized admin on the users page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $target = User::factory()->create();

    Livewire::test(Users::class)
        ->assertSee(route('impersonate', $target));
});

test('impersonate button is not rendered for the currently authenticated user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(Users::class)
        ->assertDontSee(route('impersonate', $admin));
});
