<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Missions')],
            ]" />

            <flux:heading size="xl">{{ __('Missions') }}</flux:heading>
            <flux:subheading>{{ __('Manage your application missions and their durations.') }}</flux:subheading>
        </div>

        @can('create missions')
            <flux:button icon="plus" wire:click="createMission" variant="primary">
                {{ __('Create mission') }}
            </flux:button>
        @endcan
    </div>

    <!-- Statistics Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon.check-circle variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div class="min-w-0">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Missions') }}</flux:text>
                        <flux:heading size="xl">{{ $this->totalMissions }}</flux:heading>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                        <flux:icon.check-circle variant="micro" class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="min-w-0">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Active') }}</flux:text>
                        <flux:heading size="xl">{{ $this->totalActive }}</flux:heading>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon.clock variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div class="min-w-0">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('With Duration') }}</flux:text>
                        <flux:heading size="xl">{{ \App\Models\Mission::whereNotNull('start_date')->count() }}</flux:heading>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon.users variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div class="min-w-0">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Without Duration') }}</flux:text>
                        <flux:heading size="xl">{{ \App\Models\Mission::whereNull('start_date')->count() }}</flux:heading>
                    </div>
                </div>
            </flux:card>
        </div>

    <!-- Search -->
    <flux:input
        wire:model.live="search"
        icon="magnifying-glass"
        :placeholder="__('Search missions...')"
        class="max-w-sm"
    />

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'title'"
                    :direction="$sortDirection"
                    wire:click="sortBy('title')">
                    {{ __('Title') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'start_date'"
                    :direction="$sortDirection"
                    wire:click="sortBy('start_date')">
                    {{ __('Duration') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'is_active'"
                    :direction="$sortDirection"
                    wire:click="sortBy('is_active')">
                    {{ __('Status') }}
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
                @forelse ($this->missions as $mission)
                    <flux:table.row :key="$mission->id">
                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="font-medium text-zinc-800 dark:text-white">{{ $mission->title }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            @if ($mission->start_date && $mission->end_date)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $mission->start_date->format('M j, Y') }} – {{ $mission->end_date->format('M j, Y') }}
                                </flux:text>
                            @elseif ($mission->start_date)
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $mission->start_date->format('M j, Y') }}
                                </flux:text>
                            @else
                                <flux:text class="text-sm text-zinc-400">{{ __('—') }}</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="center" class="!px-4 !py-3.5">
                            @if ($mission->is_active)
                                <flux:badge color="green" class="text-xs">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" variant="subtle" class="text-xs">{{ __('Inactive') }}</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="center" class="!px-4 !py-3.5">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $mission->created_at ? $mission->created_at->format('Y-m-d') : '—' }}
                            </flux:text>
                        </flux:table.cell>

                        <flux:table.cell align="end" class="!px-4 !py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit missions')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        wire:click="editMission({{ $mission->id }})"
                                        title="{{ __('Edit') }}" />
                                @endcan

                                @can('delete missions')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $mission->id }})"
                                        title="{{ __('Delete') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="!px-4 !py-3.5">
                            <div class="py-8 text-center">
                                <flux:icon.check-circle variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                                <flux:heading>{{ __('No missions found.') }}</flux:heading>
                                <flux:text class="mt-1">
                                    {{ __('Try adjusting your search or create a new mission.') }}
                                </flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->missions->hasPages())
        <div class="mt-4">
            {{ $this->missions->links() }}
        </div>
    @endif

    <!-- Create / Edit Modal -->
    <flux:modal
        name="mission-modal"
        wire:model="showModal"
        class="w-full max-w-xl"
    >
        <form wire:submit.prevent="saveMission" class="space-y-6">
            <!-- Header -->
            <div class="space-y-1">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit mission') : __('Create mission') }}
                </flux:heading>

                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? __('Update the details of this mission.') : __('Create a new mission for your application.') }}
                </flux:text>
            </div>

            <!-- Form Fields -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="space-y-4 p-5">
                    <flux:input
                        wire:model="title"
                        :label="__('Title')"
                        :error="$errors->first('title')"
                        placeholder="{{ __('e.g., Annual Audit') }}"
                        required
                        autofocus
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="start_date"
                            :label="__('Start Date')"
                            :error="$errors->first('start_date')"
                            type="date"
                        />

                        <flux:input
                            wire:model="end_date"
                            :label="__('End Date')"
                            :error="$errors->first('end_date')"
                            type="date"
                        />
                    </div>

                    <flux:input
                        wire:model="agenda_link"
                        :label="__('Agenda Link')"
                        :error="$errors->first('agenda_link')"
                        placeholder="{{ __('e.g., https://drive.google.com/agenda') }}"
                    />

                    <flux:field>
                        <div class="flex items-center justify-between">
                            <flux:label for="enable_registration">{{ __('Enable Registration') }}</flux:label>
                            <flux:switch
                                wire:model="enable_registration"
                                id="enable_registration"
                            />
                        </div>
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Allow users to register for this mission.') }}
                        </flux:text>
                    </flux:field>

                    <flux:field>
                        <div class="flex items-center justify-between">
                            <flux:label for="is_active">{{ __('Active Mission') }}</flux:label>
                            <flux:switch
                                wire:model="is_active"
                                id="is_active"
                            />
                        </div>
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Mark this mission as active. Only one active mission is allowed at a time.') }}
                        </flux:text>
                        @error('is_active')
                            <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </flux:field>

                    <!-- Banner Image -->
                    <div>
                        <flux:label>{{ __('Banner Image') }}</flux:label>

                        @if ($this->existingBanner)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ $this->bannerUrl($this->existingBanner) }}" alt="{{ __('Banner') }}" class="h-24 w-40 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />

                                <flux:checkbox
                                    wire:model="removeBanner"
                                    :label="__('Remove banner')"
                                />
                            </div>
                        @endif

                        <flux:input
                            type="file"
                            wire:model="banner"
                            :error="$errors->first('banner')"
                            accept="image/*"
                        />
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('PNG, JPG, GIF up to 5MB. Recommended aspect ratio: 16:9.') }}
                        </flux:text>

                        @if ($this->banner)
                            <div class="mt-3">
                                <img src="{{ $this->banner->temporaryUrl() }}" alt="{{ __('Banner preview') }}" class="h-24 w-40 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-5">
                <flux:button variant="ghost" wire:click="closeModal" type="button">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    variant="primary"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="saveMission">
                    <span wire:loading.remove wire:target="saveMission">
                        {{ $editingId ? __('Update') : __('Create') }}
                    </span>
                    <span wire:loading wire:target="saveMission">
                        {{ __('Saving...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
