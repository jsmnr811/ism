<?php

namespace App\Livewire\Missions;

use App\Models\Mission;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Missions')]
class Missions extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $deletingId = null;

    public string $title = '';

    public ?string $start_date = null;

    public ?string $end_date = null;

    public $banner;

    public ?string $existingBanner = null;

    public bool $removeBanner = false;

    public ?string $agenda_link = '';

    public ?string $virtual_site_visit_link = '';

    public bool $enable_registration = false;

    public bool $is_active = false;

    /**
     * Get the list of missions matching the search query, with sorting and pagination.
     */
    #[Computed]
    public function missions()
    {
        return Mission::query()
            ->when($this->search, fn ($query, $search) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('cluster', 'like', "%{$search}%")
                ->orWhere('component', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Get the total number of missions.
     */
    #[Computed]
    public function totalMissions(): int
    {
        return Mission::count();
    }

    /**
     * Get the number of active missions.
     */
    #[Computed]
    public function totalActive(): int
    {
        return Mission::where('is_active', true)->count();
    }

    /**
     * Get the active mission.
     */
    #[Computed]
    public function activeMission(): ?Mission
    {
        return Mission::where('is_active', true)->first();
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
     * Get the public URL for a mission banner.
     */
    public function bannerUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Show the create modal.
     */
    public function createMission(): void
    {
        abort_if(! auth()->user()?->can('create missions'), 403);

        $this->reset('title', 'start_date', 'end_date', 'banner', 'existingBanner', 'removeBanner', 'agenda_link', 'enable_registration', 'is_active', 'editingId', 'showModal');
        $this->is_active = Mission::where('is_active', true)->exists();

        if ($this->is_active) {
            $activeMission = $this->activeMission;
            LivewireAlert::title(__('Active Mission Exists'))
                ->text(__('A mission is currently active: ":name". Only one active mission is allowed at a time. Activating this mission will deactivate the current active mission.', ['name' => $activeMission?->title ?? '']))
                ->warning()
                ->withConfirmButton(__('Proceed'))
                ->withCancelButton(__('Cancel'))
                ->onConfirm('openCreateModal')
                ->show();

            return;
        }

        $this->showModal = true;
    }

    /**
     * Open the create modal after confirmation.
     */
    public function openCreateModal(): void
    {
        $this->showModal = true;
    }

    /**
     * Show the edit modal pre-filled with the mission data.
     */
    public function editMission(int $id): void
    {
        abort_if(! auth()->user()?->can('edit missions'), 403);

        $mission = Mission::findOrFail($id);

        $this->title = $mission->title;
        $this->start_date = $mission->start_date ? $mission->start_date->format('Y-m-d') : null;
        $this->end_date = $mission->end_date ? $mission->end_date->format('Y-m-d') : null;
        $this->existingBanner = $mission->banner;
        $this->removeBanner = false;
        $this->agenda_link = $mission->agenda_link;
        $this->enable_registration = $mission->enable_registration;
        $this->is_active = $mission->is_active;
        $this->editingId = $mission->id;
        $this->showModal = true;
    }

    /**
     * Store or update the mission.
     */
    public function saveMission(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit missions')
            : ! auth()->user()?->can('create missions'), 403);

        $validated = $this->validate([
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'banner' => [
                'nullable',
                'image',
                'max:5120',
            ],
            'agenda_link' => [
                'nullable',
                'string',
                'max:255',
            ],
            'virtual_site_visit_link' => [
                'nullable',
                'string',
                'max:255',
            ],
            'enable_registration' => [
                'boolean',
            ],
            'is_active' => [
                'boolean',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $activeMissionExists = Mission::where('is_active', true)
                        ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                        ->exists();

                    if ($activeMissionExists) {
                        $fail(__('A mission is already marked as active. Only one active mission is allowed.'));
                    }
                },
            ],
            'removeBanner' => [
                'boolean',
            ],
        ]);

        $bannerPath = $this->existingBanner;

        if ($this->removeBanner && $bannerPath) {
            Storage::disk('public')->delete($bannerPath);
            $bannerPath = null;
        }

        if ($this->banner) {
            if ($bannerPath) {
                Storage::disk('public')->delete($bannerPath);
            }

            $bannerPath = $this->banner->store('missions', 'public');
        }

        $data = [
            'title' => $validated['title'],
            'start_date' => $validated['start_date'] ?: null,
            'end_date' => $validated['end_date'] ?: null,
            'banner' => $bannerPath,
            'agenda_link' => $validated['agenda_link'] ?: null,
            'virtual_site_visit_link' => $validated['virtual_site_visit_link'] ?: null,
            'enable_registration' => $validated['enable_registration'],
            'is_active' => $validated['is_active'],
        ];

        if ($this->editingId) {
            Mission::findOrFail($this->editingId)->update($data);
        } else {
            Mission::create($data);
        }

        $this->dispatch('missionUpdated');
        Flux::toast(
            variant: 'success',
            text: $this->editingId
                ? __('Mission updated.')
                : __('Mission created.')
        );
        $this->reset('title', 'start_date', 'end_date', 'banner', 'existingBanner', 'removeBanner', 'agenda_link', 'enable_registration', 'is_active', 'editingId', 'showModal');
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete missions'), 403);

        $mission = Mission::findOrFail($id);

        $this->deletingId = $mission->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $mission->title]))
            ->text(__('This action cannot be undone.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deleteMission')
            ->show();
    }

    /**
     * Delete the mission and its banner image.
     */
    public function deleteMission(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $mission = Mission::find($this->deletingId);

        if ($mission && $mission->banner) {
            Storage::disk('public')->delete($mission->banner);
        }

        Mission::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('missionUpdated');
        Flux::toast(variant: 'success', text: __('Mission deleted.'));
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->reset('title', 'start_date', 'end_date', 'banner', 'existingBanner', 'removeBanner', 'agenda_link', 'enable_registration', 'is_active', 'editingId', 'showModal');
    }
}
