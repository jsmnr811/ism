<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Roles')],
            ]" />

            <flux:heading size="xl">{{ __('Roles') }}</flux:heading>
            <flux:subheading>{{ __('Manage your application roles and assign them to users.') }}</flux:subheading>
        </div>

        <flux:button icon="plus" wire:click="createRole" variant="primary">
            {{ __('Create role') }}
        </flux:button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.shield-check variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Roles') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalRoles }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.key variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Permissions') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalPermissions }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.users variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Users With Roles') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalUsersWithRoles }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Search -->
    <flux:input
        wire:model.live="search"
        icon="magnifying-glass"
        :placeholder="__('Search roles...')"
        class="max-w-sm"
    />

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'name'"
                    :direction="$sortDirection"
                    wire:click="sortBy('name')"
                >
                    {{ __('Name') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'permissions_count'"
                    :direction="$sortDirection"
                    wire:click="sortBy('permissions_count')"
                >
                    {{ __('Permissions') }}
                </flux:table.column>
                <flux:table.column class="!px-4 !py-3" align="end">
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->roles as $role)
                    <flux:table.row :key="$role->id">
                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="font-medium text-zinc-800 dark:text-white">{{ $role->name }}</span>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="!px-4 !py-3.5">
                            @if ($role->permissions->isNotEmpty())
                                <flux:tooltip :content="$this->permissionNamesFor($role)" position="top">
                                    <flux:badge size="sm" variant="pill">
                                        {{ $role->permissions_count }}
                                    </flux:badge>
                                </flux:tooltip>
                            @else
                                <flux:text class="text-sm text-zinc-400">{{ __('None') }}</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end" class="!px-4 !py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    wire:click="editRole({{ $role->id }})"
                                    title="{{ __('Edit') }}"
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $role->id }})"
                                    title="{{ __('Delete') }}"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="!px-4 !py-3.5">
                            <div class="py-8 text-center">
                                <flux:icon.shield-check variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                                <flux:heading>{{ __('No roles found.') }}</flux:heading>
                                <flux:text class="mt-1">
                                    {{ __('Try adjusting your search or create a new role.') }}
                                </flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->roles->hasPages())
        <div class="mt-4">
            {{ $this->roles->links() }}
        </div>
    @endif

<flux:modal
    name="role-modal"
    wire:model="showModal"
    class="w-full max-w-lg"
>
    <form wire:submit.prevent="saveRole" class="space-y-6">
        <div class="space-y-1">
            <flux:heading size="lg">
                {{ $editingId ? __('Edit role') : __('Create role') }}
            </flux:heading>

            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $editingId ? __('Update the name and permissions for this role.') : __('Create a new role and assign permissions to it.') }}
            </flux:text>
        </div>

        <flux:input
            wire:model="name"
            :label="__('Name')"
            :error="$errors->first('name')"
            placeholder="{{ __('e.g., manager') }}"
            required
            autofocus
        />

        <flux:field :error="$errors->first('selectedPermissions')">
            <div class="flex items-center justify-between">
                <flux:label>{{ __('Permissions') }}</flux:label>
                <flux:badge size="sm" variant="pill" color="zinc">
                    {{ $this->selectedPermissions ? count($this->selectedPermissions) : 0 }} / {{ $this->allPermissions->count() }}
                </flux:badge>
            </div>

            <div class="mt-2 flex items-center gap-2">
                <flux:input
                    wire:model.live.debounce.300ms="permissionSearch"
                    icon="magnifying-glass"
                    :placeholder="__('Search permissions...')"
                    class="flex-1"
                    size="sm"
                    clearable
                />

                <flux:button
                    size="sm"
                    variant="ghost"
                    wire:click="selectAllPermissions"
                    type="button"
                >
                    {{ __('Select all') }}
                </flux:button>

                <flux:button
                    size="sm"
                    variant="ghost"
                    wire:click="clearAllPermissions"
                    type="button"
                >
                    {{ __('Clear') }}
                </flux:button>
            </div>

            <div class="mt-2 max-h-64 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/30">
                @forelse ($this->filteredPermissions as $permission)
                    <label
                        wire:key="permission-{{ $permission->id }}"
                        class="flex items-center gap-3 px-3 py-2.5 border-b border-zinc-200/70 dark:border-zinc-700/50 last:border-b-0 cursor-pointer hover:bg-white dark:hover:bg-zinc-800 transition-colors"
                    >
                        <flux:checkbox
                            wire:model="selectedPermissions"
                            value="{{ $permission->id }}"
                        />
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ $permission->name }}</span>
                    </label>
                @empty
                    <div class="flex flex-col items-center justify-center gap-2 px-4 py-10 text-center">
                        <flux:icon.magnifying-glass variant="outline" class="size-8 text-zinc-300 dark:text-zinc-600" />
                        <flux:text class="text-sm text-zinc-400">
                            {{ __('No permissions match your search.') }}
                        </flux:text>
                    </div>
                @endforelse
            </div>
        </flux:field>

        <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:button variant="ghost" wire:click="closeModal" type="button">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button variant="primary" type="submit">
                {{ $editingId ? __('Update role') : __('Create role') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
</div>
