<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Mission Registrations')],
            ]" />

            <flux:heading size="xl">{{ __('Mission Registrations') }}</flux:heading>
            <flux:subheading>{{ __('Participants registered through the landing page or added manually by staff.') }}</flux:subheading>
        </div>

        @can('create mission registrations')
            <flux:button icon="plus" wire:click="createRegistration" variant="primary">
                {{ __('Add participant') }}
            </flux:button>
        @endcan
    </div>

    <!-- Statistics Cards -->
    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.user-group variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Registrations') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalRegistrations }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon.globe-alt variant="micro" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Landing Page') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalLanding }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon.user-plus variant="micro" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Added by Staff') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalManual }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Filters -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <flux:input
            wire:model.live="search"
            icon="magnifying-glass"
            :placeholder="__('Search participants...')" />

        <flux:select wire:model.live="missionFilter">
            <flux:select.option value="">{{ __('All missions') }}</flux:select.option>
            @foreach ($this->missionsList as $mission)
                <flux:select.option value="{{ $mission->id }}">{{ $mission->title }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="officeFilter">
            <flux:select.option value="">{{ __('All offices') }}</flux:select.option>
            @foreach ($this->getOfficeOptions() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="attendanceDateFilter">
            <flux:select.option value="">{{ __('All attendance dates') }}</flux:select.option>
            @foreach ($this->agendaDates as $date)
                <flux:select.option value="{{ $date }}">
                    {{ \Illuminate\Support\Carbon::parse($date)->format('M j, Y') }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="sourceFilter">
            <flux:select.option value="">{{ __('All sources') }}</flux:select.option>
            @foreach ($this->getSourceOptions() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'last_name'"
                    :direction="$sortDirection"
                    wire:click="sortBy('last_name')">
                    {{ __('Participant') }}
                </flux:table.column>
                <flux:table.column class="!px-4 !py-3">{{ __('Mission') }}</flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'office'"
                    :direction="$sortDirection"
                    wire:click="sortBy('office')">
                    {{ __('Office') }}
                </flux:table.column>
                <flux:table.column class="!px-4 !py-3">{{ __('Attendance Dates') }}</flux:table.column>
                <flux:table.column class="!px-4 !py-3" align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->registrations as $registration)
                    <flux:table.row :key="$registration->id">
                        <flux:table.cell class="!px-4 !py-3.5">
                            <div class="min-w-0">
                                <span class="font-medium text-zinc-800 dark:text-white">{{ $registration->fullName() }}</span>
                                <flux:text class="block truncate text-xs">{{ $registration->email ?: $registration->contact_number }}</flux:text>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5 whitespace-normal">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ $registration->mission?->title ?? __('—') }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ $registration->office?->label() ?? __('—') }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            @if (! empty($registration->attendance_dates))
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($registration->attendance_dates as $date)
                                        <flux:badge color="zinc" variant="subtle" size="sm">
                                            {{ \Illuminate\Support\Carbon::parse($date)->format('M j, Y') }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @else
                                <flux:text class="text-sm text-zinc-400">{{ __('—') }}</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end" class="!px-4 !py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    wire:click="viewRegistration({{ $registration->id }})"
                                    title="{{ __('View') }}" />

                                @can('edit mission registrations')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        wire:click="editRegistration({{ $registration->id }})"
                                        title="{{ __('Edit') }}" />
                                @endcan

                                @can('delete mission registrations')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $registration->id }})"
                                        title="{{ __('Delete') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="!px-4 !py-3.5">
                            <div class="py-8 text-center">
                                <flux:icon.user-group variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                                <flux:heading>{{ __('No registrations found.') }}</flux:heading>
                                <flux:text class="mt-1">{{ __('Try adjusting your filters or add a participant manually.') }}</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->registrations->hasPages())
        <div class="mt-4">
            {{ $this->registrations->links() }}
        </div>
    @endif

    <!-- Create / Edit Modal -->
    <flux:modal name="registration-modal" wire:model="showModal" class="w-full max-w-3xl">
        <form wire:submit.prevent="saveRegistration" class="space-y-6">
            <div class="space-y-1">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit registration') : __('Add participant') }}
                </flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Manually registered participants are recorded as added by :name.', ['name' => auth()->user()?->name ?? __('staff')]) }}
                </flux:text>
            </div>

            <div class="max-h-[65vh] space-y-4 overflow-y-auto pe-1">
                <flux:select
                    wire:model="mission_id"
                    :label="__('Mission')"
                    :error="$errors->first('mission_id')"
                    placeholder="{{ __('Select mission') }}"
                    required>
                    @foreach ($this->missionsList as $mission)
                        <flux:select.option value="{{ $mission->id }}">{{ $mission->title }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:fieldset>
                    <flux:legend>{{ __('Full Name') }}</flux:legend>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:input
                            wire:model="first_name"
                            :label="__('First Name')"
                            :error="$errors->first('first_name')"
                            placeholder="{{ __('e.g., Juan') }}"
                            required />
                        <flux:input
                            wire:model="middle_initial"
                            :label="__('Middle Initial')"
                            :error="$errors->first('middle_initial')"
                            placeholder="{{ __('e.g., A') }}"
                            maxlength="1" />
                        <flux:select
                            wire:model="suffix"
                            :label="__('Suffix')"
                            :error="$errors->first('suffix')"
                            placeholder="{{ __('Suffix') }}">
                            @foreach ($this->getSuffixOptions() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input
                            wire:model="last_name"
                            :label="__('Last Name')"
                            :error="$errors->first('last_name')"
                            placeholder="{{ __('e.g., Dela Cruz') }}"
                            class="sm:col-span-2"
                            required />
                    </div>
                </flux:fieldset>

                <flux:select
                    wire:model.live="office"
                    :label="__('Office')"
                    :error="$errors->first('office')"
                    placeholder="{{ __('Select office') }}"
                    required>
                    @foreach ($this->getOfficeOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if ($this->isPRDPOffice())
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select
                            wire:model.live="management_tier"
                            :label="__('Management Tier')"
                            :error="$errors->first('management_tier')"
                            placeholder="{{ __('Select management tier') }}">
                            @foreach ($this->getManagementTierOptions() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        @if ($management_tier === \App\Enums\MissionManagementTier::PSO->value)
                            <flux:select
                                wire:model="pso_cluster"
                                :label="__('PSO Cluster')"
                                :error="$errors->first('pso_cluster')"
                                placeholder="{{ __('Select PSO cluster') }}">
                                @foreach ($this->getPsoClusterOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if ($management_tier === \App\Enums\MissionManagementTier::RPCO->value)
                            <flux:select
                                wire:model="rpco_office"
                                :label="__('RPCO Office')"
                                :error="$errors->first('rpco_office')"
                                placeholder="{{ __('Select RPCO office') }}">
                                @foreach ($this->getRpcoOfficeOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        <flux:select
                            wire:model="component_unit"
                            :label="__('Component / Unit')"
                            :error="$errors->first('component_unit')"
                            placeholder="{{ __('Select component/unit') }}">
                            @foreach ($this->getComponentUnitOptions() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="position"
                        :label="__('Position')"
                        :error="$errors->first('position')"
                        placeholder="{{ __('e.g., Project Manager') }}"
                        required />
                    <flux:select
                        wire:model="sex"
                        :label="__('Sex')"
                        :error="$errors->first('sex')"
                        placeholder="{{ __('Select sex') }}"
                        required>
                        @foreach ($this->getSexOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input
                        wire:model="contact_number"
                        :label="__('Contact Number')"
                        :error="$errors->first('contact_number')"
                        placeholder="{{ __('e.g., 09123456789') }}"
                        required />
                    <flux:input
                        wire:model="email"
                        type="email"
                        :label="__('Email')"
                        :error="$errors->first('email')"
                        placeholder="{{ __('e.g., juan@example.com') }}" />
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-5">
                <flux:button variant="ghost" wire:click="closeModal" type="button">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button
                    variant="primary"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="saveRegistration">
                    <span wire:loading.remove wire:target="saveRegistration">
                        {{ $editingId ? __('Update') : __('Create') }}
                    </span>
                    <span wire:loading wire:target="saveRegistration">{{ __('Saving...') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Detail Modal -->
    <flux:modal name="registration-view-modal" wire:model="showViewModal" class="w-full max-w-xl">
        @if ($this->viewing)
            <div class="space-y-6">
                <div class="space-y-1">
                    <flux:heading size="lg">{{ $this->viewing->fullName() }}</flux:heading>
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $this->viewing->mission?->title ?? __('—') }}
                    </flux:text>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Office') }}</flux:text>
                        <flux:text>{{ $this->viewing->office?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Position') }}</flux:text>
                        <flux:text>{{ $this->viewing->position }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Management Tier') }}</flux:text>
                        <flux:text>{{ $this->viewing->management_tier?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Component / Unit') }}</flux:text>
                        <flux:text>{{ $this->viewing->component_unit?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('PSO Cluster') }}</flux:text>
                        <flux:text>{{ $this->viewing->pso_cluster?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('RPCO Office') }}</flux:text>
                        <flux:text>{{ $this->viewing->rpco_office?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Sex') }}</flux:text>
                        <flux:text>{{ $this->viewing->sex?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Contact Number') }}</flux:text>
                        <flux:text>{{ $this->viewing->contact_number }}</flux:text>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Email') }}</flux:text>
                        <flux:text>{{ $this->viewing->email ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Source') }}</flux:text>
                        <flux:text>{{ $this->viewing->source->label() }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Registered By') }}</flux:text>
                        <flux:text>{{ $this->viewing->registeredBy?->name ?? __('Self-registered') }}</flux:text>
                    </div>
                </div>

                @if (! empty($this->viewing->attendance_dates))
                    <div class="space-y-2">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Attendance') }}</flux:text>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->viewing->attendance_dates as $index => $date)
                                <flux:badge color="zinc" variant="subtle" size="sm">
                                    {{ \Illuminate\Support\Carbon::parse($date)->format('M j, Y') }}
                                    @if (! empty($this->viewing->attendance_mode[$index]))
                                        · {{ ucfirst($this->viewing->attendance_mode[$index]) }}
                                    @endif
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-5">
                    <flux:button variant="ghost" wire:click="$set('showViewModal', false)">
                        {{ __('Close') }}
                    </flux:button>

                    @can('edit mission registrations')
                        <flux:button
                            variant="primary"
                            icon="pencil"
                            wire:click="editRegistration({{ $this->viewing->id }})">
                            {{ __('Edit') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        @endif
    </flux:modal>
</div>
