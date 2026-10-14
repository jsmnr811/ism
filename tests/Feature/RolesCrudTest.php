<?php

use App\Livewire\Administration\Roles;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);
});

test('roles page requires authentication', function () {
    auth()->logout();

    $this->get(route('roles.index'))
        ->assertRedirect(route('login'));
});

test('roles page can be rendered by authenticated user', function () {
    $this->get(route('roles.index'))
        ->assertOk();
});

test('roles page displays existing roles', function () {
    Livewire::test(Roles::class)
        ->assertSee('admin')
        ->assertSee('user');
});

test('user can create a new role', function () {
    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('name', 'manager')
        ->call('saveRole')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('roles', [
        'name' => 'manager',
        'guard_name' => 'web',
    ]);
});

test('user can edit an existing role', function () {
    $role = Role::where('name', 'user')->first();

    Livewire::test(Roles::class)
        ->call('editRole', $role->id)
        ->set('name', 'content user')
        ->call('saveRole')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('roles', [
        'name' => 'content user',
    ]);
    $this->assertDatabaseMissing('roles', [
        'name' => 'user',
    ]);
});

test('user can delete a role through the livewire alert confirmation', function () {
    $role = Role::where('name', 'admin')->first();

    Livewire::test(Roles::class)
        ->call('confirmDelete', $role->id)
        ->assertSet('deletingId', $role->id)
        ->call('deleteRole');

    $this->assertDatabaseMissing('roles', [
        'name' => 'admin',
    ]);
});

test('validation requires role name', function () {
    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('name', '')
        ->call('saveRole')
        ->assertHasErrors('name');
});

test('validation requires minimum length for role name', function () {
    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('name', 'ab')
        ->call('saveRole')
        ->assertHasErrors('name');
});

test('validation prevents duplicate role names', function () {
    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('name', 'admin')
        ->call('saveRole')
        ->assertHasErrors('name');
});

test('search filters roles', function () {
    Role::create(['name' => 'manager', 'guard_name' => 'web']);

    Livewire::test(Roles::class)
        ->set('search', 'manager')
        ->assertSee('manager')
        ->assertSet('roles', fn ($roles) => $roles->pluck('name')->all() === ['manager']);
});

test('create modal resets form fields', function () {
    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('name', 'temp role')
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('editingId', null)
        ->assertSet('selectedPermissions', []);
});

test('user can assign permissions to a new role', function () {
    $permissionIds = Permission::pluck('id')->take(2)->all();

    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('name', 'writer')
        ->set('selectedPermissions', $permissionIds)
        ->call('saveRole')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('roles', [
        'name' => 'writer',
        'guard_name' => 'web',
    ]);

    foreach ($permissionIds as $pid) {
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => Role::where('name', 'writer')->first()->id,
            'permission_id' => $pid,
        ]);
    }
});

test('user can update assigned permissions when editing a role', function () {
    $role = Role::where('name', 'admin')->first();

    Livewire::test(Roles::class)
        ->call('editRole', $role->id)
        ->assertSet('selectedPermissions', array_map('strval', $role->permissions->pluck('id')->all()))
        ->set('selectedPermissions', [])
        ->call('saveRole')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('role_has_permissions', [
        'role_id' => $role->id,
    ]);
});

test('permission search filters the available permissions list', function () {
    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('permissionSearch', 'manage')
        ->assertSee('manage settings')
        ->assertDontSee('No permissions match your search.');

    Livewire::test(Roles::class)
        ->call('createRole')
        ->set('permissionSearch', 'zzz non exist')
        ->assertSee('No permissions match your search.');
});
