<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @if (session()->has('impersonate_user_id'))
            <div class="border-b border-amber-200 bg-amber-50 px-4 py-2.5 text-sm dark:border-amber-800/50 dark:bg-amber-900/30">
                <div class="mx-auto flex max-w-7xl items-center justify-between lg:px-2">
                    <div class="flex items-center gap-2 font-medium text-amber-900 dark:text-amber-200">
                        <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                        {{ __('You are impersonating :name.', ['name' => auth()->user()->name]) }}
                    </div>

                    <form method="POST" action="{{ route('impersonate.stop') }}" class="inline-flex">
                        @csrf
                        <flux:button
                            size="sm"
                            variant="subtle"
                            icon="arrow-left-end-on-rectangle"
                            type="submit"
                            :label="__('Stop impersonating')"
                            data-test="stop-impersonation-button"
                        />
                    </form>
                </div>
            </div>
        @endif

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Dashboard')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()->can('view missions') || auth()->user()->can('view meetings') || auth()->user()->can('view site visits'))
                    <flux:sidebar.group :heading="__('ISM Management')" class="grid">
                        @can('view missions')
                        <flux:sidebar.item icon="check-circle" :href="route('missions.index')" :current="request()->routeIs('missions.*')" wire:navigate>
                            {{ __('Missions') }}
                        </flux:sidebar.item>
                        @endcan

                        @can('view meetings')
                        <flux:sidebar.item icon="calendar" :href="route('meetings.index')" :current="request()->routeIs('meetings.*')" wire:navigate>
                            {{ __('Meetings') }}
                        </flux:sidebar.item>
                        @endcan

                        @can('view site visits')
                        <flux:sidebar.item icon="photo" :href="route('site-visits.index')" :current="request()->routeIs('site-visits.*')" wire:navigate>
                            {{ __('Site Visits') }}
                        </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif

                @if (auth()->user()->can('view permissions') || auth()->user()->can('view roles') || auth()->user()->can('view users'))
                <flux:sidebar.group :heading="__('User Management')" class="grid">
                        @can('view permissions')
                        <flux:sidebar.item icon="shield-check" :href="route('permissions.index')" :current="request()->routeIs('permissions.*')" wire:navigate>
                            {{ __('Permissions') }}
                        </flux:sidebar.item>
                        @endcan
                        @can('view roles')
                        <flux:sidebar.item icon="users" :href="route('roles.index')" :current="request()->routeIs('roles.*')" wire:navigate>
                            {{ __('Roles') }}
                        </flux:sidebar.item>
                        @endcan
                        @can('view users')
                        <flux:sidebar.item icon="user-group" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                {{-- <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item> --}}
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    @if (session()->has('impersonate_user_id'))
                        <form method="POST" action="{{ route('impersonate.stop') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-left-end-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="stop-impersonation-button"
                            >
                                {{ __('Stop impersonating') }}
                            </flux:menu.item>
                        </form>

                        <flux:menu.separator />
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @fluxScripts
    </body>
</html>
