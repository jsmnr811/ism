<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Permissions')],
            ]" />

            <flux:heading size="xl">{{ __('Permissions') }}</flux:heading>
            <flux:subheading>{{ __('Manage your application permissions and assign them to roles.') }}</flux:subheading>
        </div>

        <flux:button icon="plus" wire:click="createPermission" variant="primary">
            {{ __('Create permission') }}
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
        :placeholder="__('Search permissions...')"
        class="max-w-sm" />

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'name'"
                    :direction="$sortDirection"
                    wire:click="sortBy('name')">
                    {{ __('Name') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'roles_count'"
                    :direction="$sortDirection"
                    wire:click="sortBy('roles_count')">
                    {{ __('Roles') }}
                </flux:table.column>
                <flux:table.column class="!px-4 !py-3" align="end">
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->permissions as $permission)
                <flux:table.row :key="$permission->id">
                    <flux:table.cell class="!px-4 !py-3.5">
                        <span class="font-medium text-zinc-800 dark:text-white">{{ $permission->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="center" class="!px-4 !py-3.5">
                        @if ($permission->roles->isNotEmpty())
                        <flux:tooltip :content="$this->roleNamesFor($permission)" position="top">
                            <flux:badge size="sm" variant="pill">
                                {{ $permission->roles_count }}
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
                                wire:click="editPermission({{ $permission->id }})"
                                title="{{ __('Edit') }}" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="confirmDelete({{ $permission->id }})"
                                title="{{ __('Delete') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="!px-4 !py-3.5">
                        <div class="py-8 text-center">
                            <flux:icon.shield-check variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                            <flux:heading>{{ __('No permissions found.') }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ __('Try adjusting your search or create a new permission.') }}
                            </flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->permissions->hasPages())
    <div class="mt-4">
        {{ $this->permissions->links() }}
    </div>
    @endif

    <flux:modal
        name="permission-modal"
        wire:model="showModal">
        <form wire:submit.prevent="savePermission" class="space-y-4">
            <flux:heading>
                {{ $editingId ? __('Edit permission') : __('Create permission') }}
            </flux:heading>

            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $editingId ? __('Update the name of this permission.') : __('Create a new permission for your application.') }}
            </flux:text>

            <flux:input
                wire:model="name"
                :label="__('Name')"
                :error="$errors->first('name')"
                placeholder="{{ __('e.g., manage articles') }}"
                required
                autofocus />

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="ghost" wire:click="closeModal">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ $editingId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>