<?php

namespace App\Livewire\SiteVisits;

use App\Enums\SiteVisitCluster;
use App\Enums\SiteVisitComponent;
use App\Enums\SiteVisitFundingSource;
use App\Models\Mission;
use App\Models\SiteVisit;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\FileUpload\TemporaryFileUpload;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Site Visits')]
class SiteVisits extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $deletingId = null;

    public string $sp_name = '';

    public ?string $cluster = '';

    public ?string $component = '';

    public ?string $location = '';

    public ?string $funding_source = '';

    public ?string $document_link = '';

    public ?string $virtual_site_visit_link = '';

    public ?int $mission_id = null;

    /** @var TemporaryFileUpload[]|null */
    public $images;

    /** @var string[]|null */
    public ?array $existingImages = null;

    /** @var int[]|null */
    public ?array $removedImages = [];

    /**
     * Get the list of missions for the dropdown.
     */
    #[Computed]
    public function missionsList()
    {
        return Mission::orderBy('title')->get();
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
     * Get the list of site visits matching the search query, with sorting and pagination.
     */
    #[Computed]
    public function siteVisits()
    {
        return SiteVisit::query()
            ->when($this->search, fn ($query, $search) => $query
                ->where('sp_name', 'like', "%{$search}%")
                ->orWhere('cluster', 'like', "%{$search}%")
                ->orWhere('component', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Get the total number of site visits.
     */
    #[Computed]
    public function totalSiteVisits(): int
    {
        return SiteVisit::count();
    }

    /**
     * Get the count of site visits with images.
     */
    #[Computed]
    public function totalWithImages(): int
    {
        return SiteVisit::all()->filter(function ($siteVisit) {
            return is_array($siteVisit->images) && count($siteVisit->images) > 0;
        })->count();
    }

    /**
     * Get the count of site visits without images.
     */
    #[Computed]
    public function totalWithoutImages(): int
    {
        return SiteVisit::all()->filter(function ($siteVisit) {
            return ! is_array($siteVisit->images) || count($siteVisit->images) === 0;
        })->count();
    }

    /**
     * Get the public URL for a site visit image.
     */
    public function imageUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
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
     * Get the list of cluster options.
     */
    public function getClusterOptions(): array
    {
        return SiteVisitCluster::labels();
    }

    /**
     * Get the list of component options.
     */
    public function getComponentOptions(): array
    {
        return SiteVisitComponent::labels();
    }

    /**
     * Get the list of funding source options.
     */
    public function getFundingSourceOptions(): array
    {
        return SiteVisitFundingSource::labels();
    }

    /**
     * Show the create modal.
     */
    public function createSiteVisit(): void
    {
        abort_if(! auth()->user()?->can('create site visits'), 403);

        $this->reset('sp_name', 'cluster', 'component', 'location', 'funding_source', 'document_link', 'virtual_site_visit_link', 'mission_id', 'images', 'existingImages', 'removedImages', 'editingId', 'showModal');
        $activeMission = $this->activeMission;
        $this->mission_id = $activeMission?->id;
        $this->showModal = true;
    }

    /**
     * Show the edit modal pre-filled with the site visit data.
     */
    public function editSiteVisit(int $id): void
    {
        abort_if(! auth()->user()?->can('edit site visits'), 403);

        $siteVisit = SiteVisit::findOrFail($id);

        $this->sp_name = $siteVisit->sp_name;
        $this->cluster = $siteVisit->cluster?->value ?? null;
        $this->component = $siteVisit->component?->value ?? null;
        $this->location = $siteVisit->location;
        $this->funding_source = $siteVisit->funding_source?->value ?? null;
        $this->document_link = $siteVisit->document_link;
        $this->virtual_site_visit_link = $siteVisit->virtual_site_visit_link;
        $this->mission_id = $siteVisit->mission_id;
        $this->existingImages = $siteVisit->images ?? null;
        $this->removedImages = [];
        $this->editingId = $siteVisit->id;
        $this->showModal = true;
    }

    /**
     * Store or update the site visit.
     */
    public function saveSiteVisit(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit site visits')
            : ! auth()->user()?->can('create site visits'), 403);

        $validated = $this->validate([
            'sp_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'cluster' => [
                'nullable',
                'in:'.implode(',', array_keys(SiteVisitCluster::labels())),
            ],
            'component' => [
                'nullable',
                'in:'.implode(',', array_keys(SiteVisitComponent::labels())),
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'funding_source' => [
                'nullable',
                'in:'.implode(',', array_keys(SiteVisitFundingSource::labels())),
            ],
            'document_link' => [
                'nullable',
                'string',
                'max:255',
            ],
            'virtual_site_visit_link' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mission_id' => [
                'nullable',
                'exists:missions,id',
            ],
            'images' => [
                'nullable',
                'array',
                'max:5',
            ],
            'images.*' => [
                'image',
                'max:5120',
            ],
            'removedImages' => [
                'array',
            ],
            'removedImages.*' => [
                'integer',
            ],
        ]);

        $existingImages = $this->existingImages ?? [];

        if (! empty($this->removedImages)) {
            foreach ($this->removedImages as $index) {
                if (isset($existingImages[$index])) {
                    Storage::disk('public')->delete($existingImages[$index]);
                }
            }
            $existingImages = array_values(array_filter(
                $existingImages,
                fn ($_, $i) => ! in_array($i, $this->removedImages),
                ARRAY_FILTER_USE_BOTH
            ));
        }

        if ($this->images) {
            $uploadedImages = [];
            foreach ($this->images as $image) {
                $uploadedImages[] = $image->store('site-visits', 'public');
            }

            $allImages = array_merge($existingImages, $uploadedImages);
        } else {
            $allImages = $existingImages;
        }

        $data = [
            'sp_name' => $validated['sp_name'],
            'cluster' => $validated['cluster'] ?: null,
            'component' => $validated['component'] ?: null,
            'location' => $validated['location'],
            'funding_source' => $validated['funding_source'] ?: null,
            'document_link' => $validated['document_link'],
            'virtual_site_visit_link' => $validated['virtual_site_visit_link'],
            'images' => empty($allImages) ? null : $allImages,
            'mission_id' => $validated['mission_id'],
        ];

        if ($this->editingId) {
            SiteVisit::findOrFail($this->editingId)->update($data);
        } else {
            SiteVisit::create($data);
        }

        $this->dispatch('siteVisitUpdated');
        Flux::toast(
            variant: 'success',
            text: $this->editingId
                ? __('Site visit updated.')
                : __('Site visit created.')
        );
        $this->reset('sp_name', 'cluster', 'component', 'location', 'funding_source', 'document_link', 'virtual_site_visit_link', 'images', 'existingImages', 'removedImages', 'editingId', 'showModal');
    }

    /**
     * Remove an image from the existing images list.
     */
    public function removeImage(int $index): void
    {
        if (! in_array($index, $this->removedImages)) {
            $this->removedImages[] = $index;
        }
    }

    /**
     * Check if an image index has been marked for removal.
     */
    public function isImageRemoved(int $index): bool
    {
        return in_array($index, $this->removedImages);
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete site visits'), 403);

        $siteVisit = SiteVisit::findOrFail($id);

        $this->deletingId = $siteVisit->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $siteVisit->sp_name]))
            ->text(__('This action cannot be undone.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deleteSiteVisit')
            ->show();
    }

    /**
     * Delete the site visit and its images.
     */
    public function deleteSiteVisit(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $siteVisit = SiteVisit::find($this->deletingId);

        if ($siteVisit && $siteVisit->images) {
            foreach ($siteVisit->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        SiteVisit::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('siteVisitUpdated');
        Flux::toast(variant: 'success', text: __('Site visit deleted.'));
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->reset('sp_name', 'cluster', 'component', 'location', 'funding_source', 'document_link', 'virtual_site_visit_link', 'mission_id', 'images', 'existingImages', 'removedImages', 'editingId', 'showModal');
    }
}
