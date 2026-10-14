<?php

use App\Livewire\Administration\Users;
use App\Models\User;
use App\Notifications\WelcomeNewUser;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);
});

test('users page requires authentication', function () {
    auth()->logout();

    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

test('users page can be rendered by authenticated user', function () {
    $this->get(route('users.index'))
        ->assertOk();
});

test('users page displays existing users', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::test(Users::class)
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});

test('user can create a new user with auto-generated password', function () {
    Notification::fake();

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->call('saveUser')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'jane@example.com')->first();

    $this->assertNotNull($newUser);
    $this->assertNotEmpty($newUser->password);
    $this->assertFalse($newUser->password === 'password');

    Notification::assertSentTo($newUser, WelcomeNewUser::class);
});

test('user can edit an existing user', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    Livewire::test(Users::class)
        ->call('editUser', $user->id)
        ->set('name', 'Jane Smith')
        ->set('email', $user->email)
        ->call('saveUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Smith',
    ]);
    $this->assertDatabaseMissing('users', [
        'name' => 'Jane Doe',
    ]);
});

test('create modal resets form fields', function () {
    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', 'temp user')
        ->set('email', 'temp@example.com')
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('editingId', null)
        ->assertSet('selectedRole', null)
        ->assertSet('selectedPermissions', []);
});

test('validation requires user name', function () {
    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', '')
        ->set('email', 'test@example.com')
        ->call('saveUser')
        ->assertHasErrors('name');
});

test('validation requires valid email', function () {
    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', 'Test User')
        ->set('email', 'not-an-email')
        ->call('saveUser')
        ->assertHasErrors('email');
});

test('validation prevents duplicate emails', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', 'Test User')
        ->set('email', 'taken@example.com')
        ->call('saveUser')
        ->assertHasErrors('email');
});

test('search filters users', function () {
    User::factory()->create(['name' => 'Alice Wonder']);
    User::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);

    Livewire::test(Users::class)
        ->set('search', 'Alice')
        ->assertSee('Alice Wonder')
        ->assertDontSee('Bob Builder');
});

test('user can assign a single role to a new user', function () {
    $adminRoleId = Role::where('name', 'admin')->first()->id;

    Notification::fake();

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', 'Charlie Brown')
        ->set('email', 'charlie@example.com')
        ->set('selectedRole', $adminRoleId)
        ->call('saveUser')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'charlie@example.com')->first();

    $this->assertDatabaseHas('model_has_roles', [
        'model_id' => $newUser->id,
        'role_id' => $adminRoleId,
    ]);
});

test('user can change the assigned role when editing', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $userId = Role::where('name', 'user')->first()->id;

    Livewire::test(Users::class)
        ->call('editUser', $user->id)
        ->assertSet('selectedRole', (string) $user->roles->first()->id)
        ->set('selectedRole', (string) $userId)
        ->call('saveUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('model_has_roles', [
        'model_id' => $user->id,
        'role_id' => $userId,
    ]);
});

test('user can delete a user through the livewire alert confirmation', function () {
    $user = User::factory()->create(['name' => 'Delete Me']);

    Livewire::test(Users::class)
        ->call('confirmDelete', $user->id)
        ->assertSet('deletingId', $user->id)
        ->call('deleteUser');

    $this->assertDatabaseMissing('users', [
        'email' => $user->email,
    ]);
});

test('selecting a role auto-populates permissions from that role', function () {
    $adminRoleId = Role::where('name', 'admin')->first()->id;
    $adminRole = Role::find($adminRoleId);

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('selectedRole', (string) $adminRoleId)
        ->assertSet('selectedPermissions', array_map('strval', $adminRole->permissions->pluck('id')->all()));

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('selectedRole', null)
        ->assertSet('selectedPermissions', []);
});

test('user can assign direct permissions to a new user', function () {
    $permissionIds = Permission::pluck('id')->take(2)->all();

    Notification::fake();

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('name', 'Dana Scully')
        ->set('email', 'dana@example.com')
        ->set('selectedRole', null)
        ->set('selectedPermissions', $permissionIds)
        ->call('saveUser')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'dana@example.com')->first();

    foreach ($permissionIds as $permissionId) {
        $this->assertDatabaseHas('model_has_permissions', [
            'model_id' => $newUser->id,
            'permission_id' => $permissionId,
        ]);
    }
});

test('user can update direct permissions when editing', function () {
    $user = User::factory()->create();
    $permission = Permission::first();
    $user->givePermissionTo($permission->name);

    Livewire::test(Users::class)
        ->call('editUser', $user->id)
        ->assertSet('selectedPermissions', array_map('strval', $user->getDirectPermissions()->pluck('id')->all()))
        ->set('selectedPermissions', [])
        ->call('saveUser')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('model_has_permissions', [
        'model_id' => $user->id,
        'permission_id' => $permission->id,
    ]);
});

test('permission search filters the available permissions list', function () {
    Livewire::test(Users::class)
        ->call('createUser')
        ->set('permissionSearch', 'manage')
        ->assertSee('manage settings')
        ->assertDontSee('No permissions match your search.');

    Livewire::test(Users::class)
        ->call('createUser')
        ->set('permissionSearch', 'zzz non exist')
        ->assertSee('No permissions match your search.');
});
