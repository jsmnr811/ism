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

#[Title('Permissions')]
class Permissions extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $deletingId = null;

    public string $name = '';

    /**
     * Get the list of permissions matching the search query, with sorting and pagination.
     */
    #[Computed]
    public function permissions()
    {
        return Permission::query()
            ->withCount('roles')
            ->with(['roles' => function ($query) {
                $query->select('id', 'name')->orderBy('name');
            }])
            ->when($this->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
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
     * Get the total number of roles.
     */
    #[Computed]
    public function totalRoles(): int
    {
        return Role::count();
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
     * Get the comma-separated role names for a permission.
     */
    public function roleNamesFor(Permission $permission): string
    {
        return $permission->roles->pluck('name')->join(', ');
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
    public function createPermission(): void
    {
        abort_if(! auth()->user()?->can('create permissions'), 403);

        $this->reset('name', 'editingId', 'showModal');
        $this->showModal = true;
    }

    /**
     * Show the edit modal pre-filled with the permission data.
     */
    public function editPermission(int $id): void
    {
        abort_if(! auth()->user()?->can('edit permissions'), 403);

        $permission = Permission::findOrFail($id);

        $this->name = $permission->name;
        $this->editingId = $permission->id;
        $this->showModal = true;
    }

    /**
     * Store or update the permission.
     */
    public function savePermission(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit permissions')
            : ! auth()->user()?->can('create permissions'), 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                $this->editingId
                    ? 'unique:permissions,name,'.$this->editingId
                    : 'unique:permissions,name',
            ],
        ]);

        if ($this->editingId) {
            Permission::find($this->editingId)?->update(['name' => $validated['name']]);
        } else {
            Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);
        }

        $this->dispatch('permissionUpdated');
        Flux::toast(
            variant: 'success',
            text: $this->editingId
                ? __('Permission updated.')
                : __('Permission created.')
        );
        $this->reset('name', 'editingId', 'showModal');
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete permissions'), 403);
        $permission = Permission::findOrFail($id);

        $this->deletingId = $permission->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $permission->name]))
            ->text(__('Any roles assigned to this permission will lose it.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deletePermission')
            ->show();
    }

    /**
     * Delete the permission.
     */
    public function deletePermission(): void
    {
        if (! $this->deletingId) {
            return;
        }

        Permission::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('permissionUpdated');
        Flux::toast(variant: 'success', text: __('Permission deleted.'));
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->reset('name', 'editingId', 'showModal');
    }
}
