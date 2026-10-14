<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Users')],
            ]" />

            <flux:heading size="xl">{{ __('Users') }}</flux:heading>
            <flux:subheading>{{ __('Manage your application users and assign roles.') }}</flux:subheading>
        </div>

        <flux:button icon="plus" wire:click="createUser" variant="primary">
            {{ __('Create user') }}
        </flux:button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.users variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Users') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalUsers }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.shield-check variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Admins') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalAdmins }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.user-group variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Regular Users') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalRegularUsers }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Search -->
    <flux:input
        wire:model.live="search"
        icon="magnifying-glass"
        :placeholder="__('Search users...')"
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
                    :sorted="$sortField === 'email'"
                    :direction="$sortDirection"
                    wire:click="sortBy('email')">
                    {{ __('Email') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'roles_count'"
                    :direction="$sortDirection"
                    wire:click="sortBy('roles_count')">
                    {{ __('Roles') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sortBy('created_at')">
                    {{ __('Created') }}
                </flux:table.column>
                <flux:table.column class="!px-4 !py-3" align="end">
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell class="!px-4 !py-3.5">
                        <span class="font-medium text-zinc-800 dark:text-white">{{ $user->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell class="!px-4 !py-3.5">
                        <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $user->email }}</span>
                    </flux:table.cell>

                    <flux:table.cell align="center" class="!px-4 !py-3.5">
                        @if ($user->roles->isNotEmpty())
                        <flux:tooltip :content="$this->roleNamesFor($user)" position="top">
                            <flux:badge size="sm" variant="pill">
                                {{ $user->roles_count }}
                            </flux:badge>
                        </flux:tooltip>
                        @else
                        <flux:text class="text-sm text-zinc-400">{{ __('None') }}</flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell align="center" class="!px-4 !py-3.5">
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $user->created_at ? $user->created_at->format('Y-m-d') : '—' }}
                        </flux:text>
                    </flux:table.cell>

                    <flux:table.cell align="end" class="!px-4 !py-3.5">
                        <div class="flex items-center justify-end gap-1">
                            @can('impersonate users')
                                @if ($user->isNot(auth()->user()) && ! $user->hasRole('admin'))
                                    <form method="POST" action="{{ route('impersonate', $user) }}" class="inline-flex">
                                        @csrf
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="user-circle"
                                            type="submit"
                                            title="{{ __('Impersonate') }}"
                                            onclick="return confirm('{{ __('Are you sure you want to impersonate this user? You will be logged in as them until you stop.') }}')"
                                        />
                                    </form>
                                @endif
                            @endcan

                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                                wire:click="editUser({{ $user->id }})"
                                title="{{ __('Edit') }}" />
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="confirmDelete({{ $user->id }})"
                                title="{{ __('Delete') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="!px-4 !py-3.5">
                        <div class="py-8 text-center">
                            <flux:icon.users variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                            <flux:heading>{{ __('No users found.') }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ __('Try adjusting your search or create a new user.') }}
                            </flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->users->hasPages())
    <div class="mt-4">
        {{ $this->users->links() }}
    </div>
    @endif

    <flux:modal
        name="user-modal"
        wire:model="showModal"
        class="w-full max-w-3xl"
    >
        <form wire:submit.prevent="saveUser" class="space-y-6">

            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit user') : __('Create user') }}
                </flux:heading>

                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $editingId ? __('Update the details of this user.') : __('Create a new user for your application.') }}
                </flux:text>
            </div>

            {{-- =========================================================
                Basic information
            ========================================================== --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="space-y-4 p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="name"
                            :label="__('Name')"
                            :error="$errors->first('name')"
                            placeholder="{{ __('e.g., Jane Doe') }}"
                            required
                            autofocus
                        />

                        <flux:input
                            wire:model="email"
                            :label="__('Email')"
                            :error="$errors->first('email')"
                            type="email"
                            placeholder="{{ __('e.g., jane@example.com') }}"
                            required
                        />
                    </div>

                    @unless ($editingId)
                        <flux:callout icon="information-circle" class="text-sm">
                            {{ __('A secure password will be auto-generated and emailed to the user upon creation.') }}
                        </flux:callout>
                    @endunless
                </div>

                <div class="flex items-center justify-between gap-4 border-t border-zinc-200 bg-zinc-50 px-5 py-3.5 dark:border-zinc-700 dark:bg-zinc-800/40">
                    <div>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                            {{ __('Email verified') }}
                        </p>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Skip verification and mark this address as confirmed.') }}
                        </p>
                    </div>

                    <flux:switch wire:model="emailVerified" />
                </div>
            </div>

            {{-- =========================================================
                Role
            ========================================================== --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <label for="selectedRole" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    {{ __('Role') }}
                </label>

                <select
    id="selectedRole"
    wire:model.live="selectedRole"
    required
    class="mt-1.5 block w-full rounded-lg border bg-white py-2 pl-3 pr-10 text-sm text-zinc-900 shadow-sm transition focus:outline-none focus:ring-2 dark:bg-zinc-900 dark:text-white
        {{ $errors->has('selectedRole')
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
            : 'border-zinc-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-zinc-600' }}"
>
    <!-- Use an empty value string to perfectly align with a null/empty backend state -->
    <option value="" disabled selected hidden>{{ __('Select a role...') }}</option>

    @foreach ($this->allRoles as $role)
        <!-- Adding wire:key prevents Livewire from mismatching options during DOM renders -->
        <option value="{{ $role->id }}" wire:key="role-{{ $role->id }}">
            {{ $role->name }}
        </option>
    @endforeach
</select>


                @error('selectedRole')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Selecting a role pre-fills its permissions below — you can then customize them.') }}
                </p>
            </div>

            {{-- =========================================================
                Permissions
            ========================================================== --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
                    <div>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                            {{ __('Permissions') }}
                        </p>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Pre-filled from the selected role. Uncheck for limited access, or check more for extended access.') }}
                        </p>
                    </div>

                    <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-400 dark:ring-zinc-700">
                        {{ $this->selectedPermissions ? count($this->selectedPermissions) : 0 }} / {{ $this->allPermissions->count() }}
                    </span>
                </div>

                <div class="p-5">
                    <flux:field :error="$errors->first('selectedPermissions')">
                        <div class="flex items-center gap-2">
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
                                wire:click="updatePermissionsFromRole"
                                :disabled="! $selectedRole"
                                type="button"
                            >
                                {{ __('Reset to role') }}
                            </flux:button>

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

                        <div class="mt-3 max-h-56 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                            @forelse ($this->filteredPermissions as $permission)
                                <label
                                    wire:key="permission-{{ $permission->id }}"
                                    class="flex cursor-pointer items-center justify-between gap-3 border-b border-zinc-100 px-4 py-2.5 transition-colors last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                                >
                                    <span class="flex items-center gap-3">
                                        <flux:checkbox
                                            wire:model="selectedPermissions"
                                            value="{{ $permission->id }}"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                                            {{ $permission->name }}
                                        </span>
                                    </span>

                                    @if (in_array((string) $permission->id, $this->selectedPermissions ?? []))
                                        <flux:icon.check variant="outline" class="size-4 text-zinc-400 dark:text-zinc-500" />
                                    @endif
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
                </div>
            </div>

            {{-- =========================================================
                Footer
            ========================================================== --}}
            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:button variant="ghost" wire:click="closeModal" type="button">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="saveUser">
                    <span wire:loading.remove wire:target="saveUser">
                        {{ $editingId ? __('Update') : __('Create') }}
                    </span>
                    <span wire:loading wire:target="saveUser">
                        {{ __('Saving...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>