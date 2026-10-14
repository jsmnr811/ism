<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <x-breadcrumbs :items="[
                ['label' => __('Dashboard'), 'href' => route('dashboard')],
                ['label' => __('Site Visits')],
            ]" />

            <flux:heading size="xl">{{ __('Site Visits') }}</flux:heading>
            <flux:subheading>{{ __('Manage your application site visits and their details.') }}</flux:subheading>
        </div>

        @can('create site visits')
            <flux:button icon="plus" wire:click="createSiteVisit" variant="primary">
                {{ __('Create site visit') }}
            </flux:button>
        @endcan
    </div>

    <!-- Statistics Cards -->
    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.check-circle variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Site Visits') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalSiteVisits }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.photo variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('With Images') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalWithImages }}</flux:heading>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.x-mark variant="micro" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="min-w-0">
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Without Images') }}</flux:text>
                    <flux:heading size="xl">{{ $this->totalWithoutImages }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Search -->
    <flux:input
        wire:model.live="search"
        icon="magnifying-glass"
        :placeholder="__('Search site visits...')"
        class="max-w-sm"
    />

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'sp_name'"
                    :direction="$sortDirection"
                    wire:click="sortBy('sp_name')">
                    {{ __('SP Name') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'cluster'"
                    :direction="$sortDirection"
                    wire:click="sortBy('cluster')">
                    {{ __('Cluster') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'component'"
                    :direction="$sortDirection"
                    wire:click="sortBy('component')">
                    {{ __('Component') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'location'"
                    :direction="$sortDirection"
                    wire:click="sortBy('location')">
                    {{ __('Location') }}
                </flux:table.column>
                <flux:table.column
                    class="!px-4 !py-3"
                    :sorted="$sortField === 'mission_id'"
                    :direction="$sortDirection"
                    wire:click="sortBy('mission_id')">
                    {{ __('Mission') }}
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
                @forelse ($this->siteVisits as $siteVisit)
                    <flux:table.row :key="$siteVisit->id">
                        <flux:table.cell class="!px-4 !py-3.5">
                            <div class="flex items-center gap-2">
                                @if (is_array($siteVisit->images) && count($siteVisit->images) > 0)
                                    <div class="flex -space-x-1">
                                        @foreach (array_slice($siteVisit->images, 0, 3) as $image)
                                            <img src="{{ $this->imageUrl($image) }}" alt="{{ __('Site visit image') }}" class="h-6 w-6 rounded object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                                        @endforeach
                                        @if (count($siteVisit->images) > 3)
                                            <div class="flex h-6 w-6 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-800 ring-1 ring-zinc-200 dark:ring-zinc-700 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                                +{{ count($siteVisit->images) - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <span class="font-medium text-zinc-800 dark:text-white">{{ $siteVisit->sp_name }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $siteVisit->cluster?->label() ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $siteVisit->component?->label() ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $siteVisit->location ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $siteVisit->mission?->title ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="!px-4 !py-3.5">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $siteVisit->created_at?->format('M j, Y g:i A') ?: '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell align="end" class="!px-4 !py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit site visits')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        wire:click="editSiteVisit({{ $siteVisit->id }})"
                                        title="{{ __('Edit') }}" />
                                @endcan

                                @can('delete site visits')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $siteVisit->id }})"
                                        title="{{ __('Delete') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="!px-4 !py-3.5">
                            <div class="py-8 text-center">
                                <flux:icon.check-circle variant="outline" class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                                <flux:heading>{{ __('No site visits found.') }}</flux:heading>
                                <flux:text class="mt-1">
                                    {{ __('Try adjusting your search or create a new site visit.') }}
                                </flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->siteVisits->hasPages())
        <div class="mt-4">
            {{ $this->siteVisits->links() }}
        </div>
    @endif

    <!-- Create / Edit Modal -->
    <flux:modal
        name="site-visit-modal"
        wire:model="showModal"
        class="w-full max-w-3xl"
    >
        <form wire:submit.prevent="saveSiteVisit" class="space-y-6">
            <!-- Header -->
            <div class="space-y-1">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit site visit') : __('Create site visit') }}
                </flux:heading>

                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $editingId ? __('Update the details of this site visit.') : __('Create a new site visit for your application.') }}
                </flux:text>
            </div>

            <!-- Form Fields -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="space-y-4 p-5">
                    <!-- Basic Information -->
                    <flux:input
                        wire:model="sp_name"
                        :label="__('SP Name')"
                        :error="$errors->first('sp_name')"
                        placeholder="{{ __('e.g., Barangay Hall Annex') }}"
                        required
                        autofocus
                    />

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

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select
                            wire:model="cluster"
                            :label="__('Cluster')"
                            :error="$errors->first('cluster')"
                            placeholder="{{ __('Select cluster') }}"
                        >
                            @foreach ($this->getClusterOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select
                            wire:model="component"
                            :label="__('Component')"
                            :error="$errors->first('component')"
                            placeholder="{{ __('Select component') }}"
                        >
                            @foreach ($this->getComponentOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <flux:input
                        wire:model="location"
                        :label="__('Location')"
                        :error="$errors->first('location')"
                        placeholder="{{ __('e.g., Quezon City') }}"
                    />

                    <!-- Additional Details -->
                    <div class="space-y-4">
                        <flux:select
                            wire:model="funding_source"
                            :label="__('Funding Source')"
                            :error="$errors->first('funding_source')"
                            placeholder="{{ __('Select funding source') }}"
                        >
                            @foreach ($this->getFundingSourceOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input
                            wire:model="document_link"
                            :label="__('Document Link')"
                            :error="$errors->first('document_link')"
                            placeholder="{{ __('e.g., https://drive.google.com/...') }}"
                        />

                        <flux:input
                            wire:model="virtual_site_visit_link"
                            :label="__('Virtual Site Visit Link')"
                            :error="$errors->first('virtual_site_visit_link')"
                            placeholder="{{ __('e.g., https://zoom.us/j/...') }}"
                        />
                    </div>

                    <!-- Existing Images (edit mode) -->
                    @if (! empty($this->existingImages))
                        <div class="space-y-2">
                            <flux:label>{{ __('Current Images') }}</flux:label>
                            <div class="flex flex-wrap gap-3">
                                @foreach ($this->existingImages as $index => $image)
                                    @if (! $this->isImageRemoved($index))
                                        <div class="relative">
                                            <img src="{{ $this->imageUrl($image) }}" alt="{{ __('Site visit image') }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                                            <flux:button
                                                size="xs"
                                                variant="ghost"
                                                icon="x-mark"
                                                wire:click="removeImage({{ $index }})"
                                                class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500/20 text-red-500 dark:text-red-400"
                                                title="{{ __('Remove image') }}"
                                            />
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- New Image Upload -->
                    <div>
                        <flux:label>{{ __('Images') }}</flux:label>
                        <flux:input
                            type="file"
                            wire:model="images"
                            :error="$errors->first('images')"
                            accept="image/*"
                            multiple
                        />
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('PNG, JPG, GIF up to 5MB each. Maximum 5 images.') }}
                        </flux:text>

                        @if ($this->images)
                            <div class="mt-3 flex flex-wrap gap-3">
                                @foreach ($this->images as $index => $image)
                                    <img src="{{ $image->temporaryUrl() }}" alt="{{ __('New image preview') }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                                @endforeach
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
                    wire:target="saveSiteVisit">
                    <span wire:loading.remove wire:target="saveSiteVisit">
                        {{ $editingId ? __('Update') : __('Create') }}
                    </span>
                    <span wire:loading wire:target="saveSiteVisit">
                        {{ __('Saving...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
