<?php

namespace App\Livewire\Administration;

use App\Models\User;
use App\Notifications\WelcomeNewUser;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('Users')]
class Users extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $deletingId = null;

    public string $name = '';

    public string $email = '';

    public bool $emailVerified = false;

    public ?string $selectedRole = '';

    public array $selectedPermissions = [];

    public string $permissionSearch = '';

    /**
     * Get the list of users matching the search query, with sorting and pagination.
     */
    #[Computed]
    public function users()
    {
        return User::query()
            ->withCount('roles')
            ->with(['roles' => function ($query) {
                $query->select('id', 'name')->orderBy('name');
            }])
            ->when(
                $this->search,
                fn ($query, $search) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Get the total number of users.
     */
    #[Computed]
    public function totalUsers(): int
    {
        return User::count();
    }

    /**
     * Get the number of users with the admin role.
     */
    #[Computed]
    public function totalAdmins(): int
    {
        return User::role('admin')->count();
    }

    /**
     * Get the number of users with the regular user role.
     */
    #[Computed]
    public function totalRegularUsers(): int
    {
        return User::role('user')->count();
    }

    /**
     * Get all available roles for the single-select.
     */
    #[Computed]
    public function allRoles()
    {
        return Role::orderBy('name')->get();
    }

    /**
     * Get all available permissions for the assignable multi-select.
     */
    #[Computed]
    public function allPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Get permissions filtered by the search string.
     */
    #[Computed]
    public function filteredPermissions()
    {
        return $this->allPermissions
            ->when($this->permissionSearch, fn ($permissions, $search) => $permissions
                ->filter(fn ($permission) => str_contains(strtolower($permission->name), strtolower($search))))
            ->values();
    }

    /**
     * Get the comma-separated permission names for a user's direct permissions.
     */
    public function permissionNamesFor(User $user): string
    {
        return $user->getDirectPermissions()->pluck('name')->join(', ');
    }

    /**
     * Get the comma-separated role names for a user.
     */
    public function roleNamesFor(User $user): string
    {
        return $user->roles->pluck('name')->join(', ');
    }

    /**
     * Sort by the given field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Show the create modal.
     */
    public function createUser(): void
    {
        abort_if(! auth()->user()?->can('create users'), 403);

        $this->reset('name', 'email', 'emailVerified', 'editingId', 'showModal', 'selectedRole', 'selectedPermissions', 'permissionSearch');
        $this->selectedRole = '';
        $this->showModal = true;
    }

    /**
     * Show the edit modal pre-filled with the user data.
     */
    public function editUser(int $id): void
    {
        abort_if(! auth()->user()?->can('edit users'), 403);
        $user = User::findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->emailVerified = (bool) $user->email_verified_at;
        $this->editingId = $user->id;
        $this->selectedRole = $user->roles->first() ? (string) $user->roles->first()->id : '';

        $directPermissionIds = $user->getDirectPermissions()->pluck('id')->all();

        $this->selectedPermissions = array_map('strval', $directPermissionIds);

        $this->showModal = true;
    }

    /**
     * Store or update the user.
     */
    public function saveUser(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit users')
            : ! auth()->user()?->can('create users'), 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $this->editingId
                    ? 'unique:users,email,'.$this->editingId
                    : 'unique:users,email',
            ],
            'selectedRole' => [
                'nullable',
                'exists:roles,id',
            ],
        ], [
            'selectedRole.exists' => __('The selected role is invalid.'),
        ]);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
        } else {
            $password = Str::password(16);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
            ]);

            $user->notify(new WelcomeNewUser($password));
        }

        if ($this->emailVerified) {
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        }

        $user->syncRoles($this->selectedRole ? [(int) $this->selectedRole] : []);
        $user->syncPermissions(array_map('intval', $this->selectedPermissions));

        $this->dispatch('userUpdated');
        Flux::toast(
            variant: 'success',
            text: $this->editingId
                ? __('User updated.')
                : __('User created. Login credentials have been emailed.')
        );
        $this->reset('name', 'email', 'emailVerified', 'editingId', 'showModal', 'selectedRole', 'selectedPermissions', 'permissionSearch');
    }

    /**
     * Load permissions from the selected role into the permission list.
     */
    public function updatePermissionsFromRole(): void
    {
        if ($this->selectedRole) {
            $role = Role::find((int) $this->selectedRole);
            $this->selectedPermissions = $role ? array_map('strval', $role->permissions->pluck('id')->all()) : [];
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function updatedSelectedRole(?string $value): void
    {
        $this->updatePermissionsFromRole();
    }

    /**
     * Select all filtered permissions.
     */
    public function selectAllPermissions(): void
    {
        $this->selectedPermissions = array_map('strval', $this->filteredPermissions->pluck('id')->all());
    }

    /**
     * Clear all selected permissions.
     */
    public function clearAllPermissions(): void
    {
        $this->selectedPermissions = [];
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete users'), 403);
        $user = User::findOrFail($id);

        $this->deletingId = $user->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $user->name]))
            ->text(__('This action cannot be undone.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deleteUser')
            ->show();
    }

    /**
     * Delete the user.
     */
    public function deleteUser(): void
    {
        if (! $this->deletingId) {
            return;
        }

        User::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('userUpdated');
        Flux::toast(variant: 'success', text: __('User deleted.'));
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->reset('name', 'email', 'emailVerified', 'editingId', 'showModal', 'selectedRole', 'selectedPermissions', 'permissionSearch');
    }
}
