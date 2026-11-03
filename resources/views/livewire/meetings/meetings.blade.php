<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Meetings')],
            ]" />

            <flux:heading size="xl">{{ __('Meetings') }}</flux:heading>
            <flux:subheading>{{ __('Manage your application meetings and their details.') }}</flux:subheading>
        </div>

        @can('create meetings')
            <flux:button icon="plus" wire:click="createMeeting" variant="primary">
                {{ __('Create meeting') }}
            </flux:button>
        @endcan
    </div>

    <!-- Statistics Cards -->
    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.calendar variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Meetings') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalMeetings }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.clock variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Today') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalToday }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.arrow-up-right variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Upcoming') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalUpcoming }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.check-circle variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Past') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalPast }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Search -->
    <flux:input
        wire:model.live="search"
        icon="magnifying-glass"
        :placeholder="__('Search meetings...')"
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
                    :sorted="$sortField === 'type'"
                    :direction="$sortDirection"
                    wire:click="sortBy('type')">
                    {{ __('Type') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'date'"
                    :direction="$sortDirection"
                    wire:click="sortBy('date')">
                    {{ __('Date') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'time_from'"
                    :direction="$sortDirection"
                    wire:click="sortBy('time_from')">
                    {{ __('From') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'time_to'"
                    :direction="$sortDirection"
                    wire:click="sortBy('time_to')">
                    {{ __('To') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'venue'"
                    :direction="$sortDirection"
                    wire:click="sortBy('venue')">
                    {{ __('Venue') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    align="center"
                    :sorted="$sortField === 'reports'"
                    :direction="$sortDirection"
                    wire:click="sortBy('reports')">
                    {{ __('Reports') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'mission_id'"
                    :direction="$sortDirection"
                    wire:click="sortBy('mission_id')">
                    {{ __('Mission') }}
                </flux:table.column>
                <flux:table.column class="!px-4 !py-3" align="end">
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->meetings as $meeting)
                    <flux:table.row :key="$meeting->id">
                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="font-medium text-zinc-800 dark:text-white">{{ $meeting->title }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            @php
                                $badgeColor = match ($meeting->type) {
                                    'ribbon_cutting' => 'yellow',
                                    'technical_discussions' => 'blue',
                                    'kick_off' => 'green',
                                    'wrap_up' => 'orange',
                                    'other' => 'zinc',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge size="sm" variant="pill" color="{{ $badgeColor }}">
                                {{ $meeting->type === 'other' && $meeting->other_type ? $meeting->other_type : ($this->typeLabel($meeting->type)) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $meeting->date->format('Y-m-d') }}
                            </flux:text>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="!px-4 !py-3.5">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ \Carbon\Carbon::parse($meeting->time_from)->format('H:i') }}
                            </flux:text>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="!px-4 !py-3.5">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ \Carbon\Carbon::parse($meeting->time_to)->format('H:i') }}
                            </flux:text>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $meeting->venue ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            @if (count($meeting->report_documents) > 0)
                                <div class="flex flex-col gap-1">
                                    @foreach ($meeting->report_documents as $report)
                                        <a href="{{ $report['url'] }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:underline truncate max-w-40">
                                            {{ $report['title'] ?: Str::limit($report['url'], 30) }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <flux:text class="text-sm text-zinc-400">{{ __('—') }}</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $meeting->mission?->title ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell align="end" class="!px-4 !py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('create meetings')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="document-duplicate"
                                        wire:click="duplicateMeeting({{ $meeting->id }})"
                                        title="{{ __('Duplicate') }}" />
                                @endcan

                                @can('edit meetings')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        wire:click="editMeeting({{ $meeting->id }})"
                                        title="{{ __('Edit') }}" />
                                @endcan

                                @can('delete meetings')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $meeting->id }})"
                                        title="{{ __('Delete') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="!px-4 !py-3.5">
                            <div class="py-8 text-center">
                                <flux:icon.calendar variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                                <flux:heading>{{ __('No meetings found.') }}</flux:heading>
                                <flux:text class="mt-1">
                                    {{ __('Try adjusting your search or create a new meeting.') }}
                                </flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->meetings->hasPages())
        <div class="mt-4">
            {{ $this->meetings->links() }}
        </div>
    @endif

    <flux:modal
        name="meeting-modal"
        wire:model="showModal"
        class="w-full max-w-4xl"
    >
        <form wire:submit.prevent="saveMeeting" class="space-y-6">
            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit meeting') : __('Create meeting') }}
                </flux:heading>

                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $editingId ? __('Update the details of this meeting.') : __('Create a new meeting for your application.') }}
                </flux:text>
            </div>

            {{-- Image preview (edit mode) --}}
            @if ($this->existingImage)
                <div class="flex items-start gap-4">
                    <img src="{{ $this->imageUrl($this->existingImage) }}" alt="{{ __('Meeting image') }}" class="h-24 w-24 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />

                    <div class="flex items-start gap-2">
                        <flux:checkbox
                            wire:model="removeImage"
                            :label="__('Remove image')"
                        />
                    </div>
                </div>
            @endif

            {{-- =========================================================
                Basic Information
            ========================================================== --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="space-y-4 p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="title"
                            :label="__('Title')"
                            :error="$errors->first('title')"
                            placeholder="{{ __('e.g., Quarterly Planning') }}"
                            required
                            autofocus
                        />

                        <flux:input
                            wire:model="meeting_id"
                            :label="__('Meeting ID')"
                            :error="$errors->first('meeting_id')"
                            placeholder="{{ __('e.g., 123456789') }}"
                        />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="date"
                            :label="__('Date')"
                            :error="$errors->first('date')"
                            type="date"
                            required
                        />

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input
                                wire:model="time_from"
                                :label="__('From')"
                                :error="$errors->first('time_from')"
                                type="time"
                                required
                            />

                            <flux:input
                                wire:model="time_to"
                                :label="__('To')"
                                :error="$errors->first('time_to')"
                                type="time"
                                required
                            />
                        </div>
                    </div>

                    <flux:select
                        wire:model="type"
                        :label="__('Type')"
                        :error="$errors->first('type')"
                        placeholder="{{ __('Select a type...') }}"
                    >
                        @foreach ($this->types as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if ($this->type === 'other')
                        <flux:input
                            wire:model="other_type"
                            :label="__('Specify Type')"
                            :error="$errors->first('other_type')"
                            placeholder="{{ __('e.g., Strategy Session') }}"
                        />
                    @endif

                    <flux:select
                        wire:model="mission_id"
                        :label="__('Mission')"
                        :error="$errors->first('mission_id')"
                        placeholder="{{ __('Select mission') }}"
                    >
                        <option value="">{{ __('— Select mission —') }}</option>
                        @foreach ($this->missionsList as $mission)
                            <option value="{{ $mission->id }}">{{ $mission->title }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="venue"
                        :label="__('Venue')"
                        :error="$errors->first('venue')"
                        placeholder="{{ __('e.g., Main Conference Room') }}"
                    />

                    <flux:input
                        wire:model="link"
                        :label="__('Link')"
                        :error="$errors->first('link')"
                        placeholder="{{ __('e.g., https://zoom.us/j/123456789') }}"
                    />

                    <flux:input
                        wire:model="passcode"
                        :label="__('Passcode')"
                        :error="$errors->first('passcode')"
                        placeholder="{{ __('e.g., AbCdEf12') }}"
                    />

                    <div>
                        <flux:label>{{ __('Reports') }}</flux:label>
                        @if (! empty($this->reportLinks))
                            <div class="space-y-3">
                                @foreach ($this->reportLinks as $index => $report)
                                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 space-y-2">
                                        <div class="flex items-start gap-2">
                                            <div class="flex-1 space-y-2">
                                                <flux:input
                                                    wire:model="reportLinks.{{ $index }}.title"
                                                    :error="$errors->first('reportLinks.'.$index.'.title')"
                                                    placeholder="{{ __('Report title') }}"
                                                />
                                                <flux:textarea
                                                    wire:model="reportLinks.{{ $index }}.description"
                                                    :error="$errors->first('reportLinks.'.$index.'.description')"
                                                    placeholder="{{ __('Report description') }}"
                                                    rows="2"
                                                />
                                                <flux:input
                                                    type="url"
                                                    wire:model="reportLinks.{{ $index }}.url"
                                                    :error="$errors->first('reportLinks.'.$index.'.url')"
                                                    placeholder="{{ __('e.g., https://drive.google.com/...') }}"
                                                />
                                            </div>
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="trash"
                                                wire:click="removeReportLink({{ $index }})"
                                                title="{{ __('Remove') }}"
                                            />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="plus"
                            wire:click="addReportLink"
                            class="mt-2"
                        >
                            {{ __('Add report') }}
                        </flux:button>
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Attach meeting reports or documents with a title and description.') }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:label>{{ __('Image') }}</flux:label>
                        <flux:input
                            type="file"
                            wire:model="image"
                            :error="$errors->first('image')"
                            accept="image/*"
                        />
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('PNG, JPG, GIF up to 5MB.') }}
                        </flux:text>
                    </div>

                    <flux:textarea
                        wire:model="description"
                        :label="__('Description')"
                        :error="$errors->first('description')"
                        placeholder="{{ __('Add a description for this meeting...') }}"
                    />
                </div>
            </div>

            {{-- =========================================================
                Footer
            ========================================================== --}}
            <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-5">
                <flux:button variant="ghost" wire:click="closeModal" type="button">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    variant="primary"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="saveMeeting"
                >
                    <span wire:loading.remove wire:target="saveMeeting">
                        {{ $editingId ? __('Update') : __('Create') }}
                    </span>
                    <span wire:loading wire:target="saveMeeting">
                        {{ __('Saving...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
