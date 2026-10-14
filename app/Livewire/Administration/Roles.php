<?php

namespace App\Livewire\Administration;

use App\Models\User;
use Flux\Flux;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('Roles')]
class Roles extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $deletingId = null;

    public string $name = '';

    public array $selectedPermissions = [];

    public string $permissionSearch = '';

    /**
     * Get the list of roles matching the search query, with sorting and pagination.
     */
    #[Computed]
    public function roles()
    {
        return Role::query()
            ->withCount('permissions')
            ->with(['permissions' => function ($query) {
                $query->select('id', 'name')->orderBy('name');
            }])
            ->when($this->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Get the total number of roles.
     */
    #[Computed]
    public function totalRoles(): int
    {
        return Role::count();
    }

    /**
     * Get the total number of permissions.
     */
    #[Computed]
    public function totalPermissions(): int
    {
        return Permission::count();
    }

    /**
     * Get the number of users that have at least one role.
     */
    #[Computed]
    public function totalUsersWithRoles(): int
    {
        return User::has('roles')->count();
    }

    /**
     * Get all available permissions for the multi-select.
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
     * Get the comma-separated permission names for a role.
     */
    public function permissionNamesFor(Role $role): string
    {
        return $role->permissions->pluck('name')->join(', ');
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
    public function createRole(): void
    {
        abort_if(! auth()->user()?->can('create roles'), 403);

        $this->reset('name', 'editingId', 'showModal', 'selectedPermissions', 'permissionSearch');
        $this->showModal = true;
    }

    /**
     * Show the edit modal pre-filled with the role data.
     */
    public function editRole(int $id): void
    {
        abort_if(! auth()->user()?->can('edit roles'), 403);

        $role = Role::findOrFail($id);

        $this->name = $role->name;
        $this->editingId = $role->id;
        $this->selectedPermissions = $role->permissions->pluck('id')->all();
        $this->showModal = true;
    }

    /**
     * Select every permission currently visible in the filtered list.
     */
    public function selectAllPermissions(): void
    {
        $visibleIds = $this->filteredPermissions->pluck('id')->all();

        $this->selectedPermissions = collect($this->selectedPermissions)
            ->merge($visibleIds)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Clear every permission currently visible in the filtered list.
     */
    public function clearAllPermissions(): void
    {
        $visibleIds = $this->filteredPermissions->pluck('id')->all();

        $this->selectedPermissions = collect($this->selectedPermissions)
            ->reject(fn ($id) => in_array($id, $visibleIds))
            ->values()
            ->all();
    }

    /**
     * Store or update the role.
     */
    public function saveRole(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit roles')
            : ! auth()->user()?->can('create roles'), 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                $this->editingId
                    ? 'unique:roles,name,'.$this->editingId
                    : 'unique:roles,name',
            ],
        ]);

        if ($this->editingId) {
            $role = Role::findOrFail($this->editingId);
            $role->update(['name' => $validated['name']]);
        } else {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);
        }

        $role->syncPermissions(array_map('intval', $this->selectedPermissions));

        $this->dispatch('roleUpdated');
        Flux::toast(
            variant: 'success',
            text: $this->editingId
                ? __('Role updated.')
                : __('Role created.')
        );
        $this->reset('name', 'editingId', 'showModal', 'selectedPermissions', 'permissionSearch');
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete roles'), 403);
        $role = Role::findOrFail($id);

        $this->deletingId = $role->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $role->name]))
            ->text(__('Any users assigned to this role will lose it.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deleteRole')
            ->show();
    }

    /**
     * Delete the role.
     */
    public function deleteRole(): void
    {
        if (! $this->deletingId) {
            return;
        }

        Role::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('roleUpdated');
        Flux::toast(variant: 'success', text: __('Role deleted.'));
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->reset('name', 'editingId', 'showModal', 'selectedPermissions', 'permissionSearch');
    }
}
