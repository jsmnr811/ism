<?php

use App\Livewire\Administration\Permissions;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);
});

test('permissions page requires authentication', function () {
    auth()->logout();

    $this->get(route('permissions.index'))
        ->assertRedirect(route('login'));
});

test('permissions page can be rendered by authenticated user', function () {
    $this->get(route('permissions.index'))
        ->assertOk();
});

test('permissions page displays existing permissions', function () {
    Livewire::test(Permissions::class)
        ->set('search', 'edit permissions')
        ->assertSee('edit permissions');

    Livewire::test(Permissions::class)
        ->set('search', 'delete users')
        ->assertSee('delete users');

    Livewire::test(Permissions::class)
        ->set('search', 'edit roles')
        ->assertSee('edit roles');
});

test('user can create a new permission', function () {
    Livewire::test(Permissions::class)
        ->call('createPermission')
        ->set('name', 'publish articles')
        ->call('savePermission')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('permissions', [
        'name' => 'publish articles',
        'guard_name' => 'web',
    ]);
});

test('user can edit an existing permission', function () {
    $permission = Permission::where('name', 'manage settings')->first();

    Livewire::test(Permissions::class)
        ->call('editPermission', $permission->id)
        ->set('name', 'manage blog posts')
        ->call('savePermission')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('permissions', [
        'name' => 'manage blog posts',
    ]);
    $this->assertDatabaseMissing('permissions', [
        'name' => 'manage settings',
    ]);
});

test('user can delete a permission', function () {
    $permission = Permission::where('name', 'manage settings')->first();

    Livewire::test(Permissions::class)
        ->call('confirmDelete', $permission->id)
        ->call('deletePermission');

    $this->assertDatabaseMissing('permissions', [
        'name' => 'manage settings',
    ]);
});

test('validation requires permission name', function () {
    Livewire::test(Permissions::class)
        ->call('createPermission')
        ->set('name', '')
        ->call('savePermission')
        ->assertHasErrors('name');
});

test('validation requires minimum length for permission name', function () {
    Livewire::test(Permissions::class)
        ->call('createPermission')
        ->set('name', 'ab')
        ->call('savePermission')
        ->assertHasErrors('name');
});

test('validation prevents duplicate permission names', function () {
    Livewire::test(Permissions::class)
        ->call('createPermission')
        ->set('name', 'manage users')
        ->call('savePermission')
        ->assertHasErrors('name');
});

test('search filters permissions', function () {
    Livewire::test(Permissions::class)
        ->set('search', 'manage')
        ->assertSee('manage users')
        ->assertSee('manage settings')
        ->assertSee('manage permissions')
        ->assertDontSee('create users');
});

test('create modal resets form fields', function () {
    Livewire::test(Permissions::class)
        ->call('createPermission')
        ->set('name', 'temp permission')
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('editingId', null);
});
