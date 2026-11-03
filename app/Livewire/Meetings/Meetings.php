<?php

namespace App\Livewire\Meetings;

use App\Models\Meeting;
use App\Models\Mission;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Meetings')]
class Meetings extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $sortField = 'date';

    public string $sortDirection = 'desc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $deletingId = null;

    public string $title = '';

    public ?string $description = '';

    public ?string $date = null;

    public ?string $time_from = null;

    public ?string $time_to = null;

    public string $type = 'technical_discussions';

    public ?string $other_type = '';

    public $image;

    public ?string $existingImage = null;

    public bool $removeImage = false;

    public ?string $venue = '';

    public ?string $link = '';

    public ?string $meeting_id = '';

    public ?string $passcode = '';

    public ?int $mission_id = null;

    /** @var array<int, array{title: string, description: string, url: string}>|null */
    public ?array $reportLinks = [];

    /**
     * Get the list of meetings matching the search query, with sorting and pagination.
     */
    #[Computed]
    public function meetings()
    {
        return Meeting::query()
            ->when($this->search, fn ($query, $search) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('venue', 'like', "%{$search}%")
                ->orWhere('meeting_id', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('other_type', 'like', "%{$search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Get the total number of meetings.
     */
    #[Computed]
    public function totalMeetings(): int
    {
        return Meeting::count();
    }

    /**
     * Get the number of upcoming meetings (date >= today).
     */
    #[Computed]
    public function totalUpcoming(): int
    {
        return Meeting::whereDate('date', '>=', now()->toDateString())->count();
    }

    /**
     * Get the number of past meetings (date < today).
     */
    #[Computed]
    public function totalPast(): int
    {
        return Meeting::whereDate('date', '<', now()->toDateString())->count();
    }

    /**
     * Get the number of meetings happening today.
     */
    #[Computed]
    public function totalToday(): int
    {
        return Meeting::whereDate('date', now()->toDateString())->count();
    }

    /**
     * Get the available meeting types.
     */
    #[Computed]
    public function types(): array
    {
        return [
            'ribbon_cutting' => __('Ribbon Cutting'),
            'technical_discussions' => __('Technical Discussions'),
            'kick_off' => __('Kick Off'),
            'wrap_up' => __('Wrap Up'),
            'other' => __('Other'),
        ];
    }

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
     * Get the public URL for a meeting image.
     */
    public function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Get the human-readable type label for a meeting.
     */
    public function typeLabel(string $type): string
    {
        return $this->types[$type] ?? $type;
    }

    /**
     * Get the comma-separated type counts for the statistics.
     */
    public function typeDistribution(): array
    {
        $counts = Meeting::select('type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->all();

        $result = [];
        foreach ($this->types as $value => $label) {
            $result[$label] = $counts[$value] ?? 0;
        }

        return $result;
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
     * Show the create modal.
     */
    public function createMeeting(): void
    {
        abort_if(! auth()->user()?->can('create meetings'), 403);

        $this->reset('title', 'description', 'date', 'time_from', 'time_to', 'type', 'other_type', 'image', 'existingImage', 'removeImage', 'venue', 'link', 'meeting_id', 'passcode', 'reportLinks', 'mission_id', 'editingId', 'showModal');
        $this->type = 'technical_discussions';
        $activeMission = $this->activeMission;
        $this->mission_id = $activeMission?->id;
        $this->showModal = true;
    }

    /**
     * Show the edit modal pre-filled with the meeting data.
     */
    public function editMeeting(int $id): void
    {
        abort_if(! auth()->user()?->can('edit meetings'), 403);

        $meeting = Meeting::findOrFail($id);

        $this->title = $meeting->title;
        $this->description = $meeting->description;
        $this->date = $meeting->date ? $meeting->date->format('Y-m-d') : null;
        $this->time_from = $meeting->time_from ? $meeting->time_from->format('H:i') : null;
        $this->time_to = $meeting->time_to ? $meeting->time_to->format('H:i') : null;
        $this->type = $meeting->type;
        $this->other_type = $meeting->other_type;
        $this->existingImage = $meeting->image;
        $this->removeImage = false;
        $this->venue = $meeting->venue;
        $this->link = $meeting->link;
        $this->meeting_id = $meeting->meeting_id;
        $this->passcode = $meeting->passcode;
        $this->mission_id = $meeting->mission_id;
        $this->reportLinks = $meeting->report_documents ?: [];
        $this->editingId = $meeting->id;
        $this->showModal = true;
    }

    /**
     * Store or update the meeting.
     */
    public function saveMeeting(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit meetings')
            : ! auth()->user()?->can('create meetings'), 403);

        $validated = $this->validate([
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'date' => [
                'required',
                'date',
            ],
            'time_from' => [
                'required',
                'date_format:H:i',
            ],
            'time_to' => [
                'required',
                'date_format:H:i',
            ],
            'type' => [
                'required',
                'in:technical_discussions,kick_off,wrap_up,other',
            ],
            'other_type' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,other',
            ],
            'image' => [
                'nullable',
                'image',
                'max:5120',
            ],
            'venue' => [
                'nullable',
                'string',
                'max:255',
            ],
            'link' => [
                'nullable',
                'string',
                'max:255',
            ],
            'meeting_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'passcode' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mission_id' => [
                'nullable',
                'exists:missions,id',
            ],
            'reportLinks' => [
                'nullable',
                'array',
            ],
            'reportLinks.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reportLinks.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'reportLinks.*.url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'removeImage' => [
                'boolean',
            ],
        ]);

        $imagePath = $this->existingImage;

        if ($this->removeImage && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($this->image) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $this->image->store('meetings', 'public');
        }

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'time_from' => $validated['time_from'],
            'time_to' => $validated['time_to'],
            'type' => $validated['type'],
            'other_type' => $validated['other_type'] ?: null,
            'image' => $imagePath,
            'venue' => $validated['venue'],
            'link' => $validated['link'],
            'meeting_id' => $validated['meeting_id'],
            'passcode' => $validated['passcode'],
            'reports' => $this->normalizedReports(),
            'mission_id' => $validated['mission_id'],
        ];

        if ($this->editingId) {
            Meeting::findOrFail($this->editingId)->update($data);
        } else {
            Meeting::create($data);
        }

        $this->dispatch('meetingUpdated');
        Flux::toast(
            variant: 'success',
            text: $this->editingId
                ? __('Meeting updated.')
                : __('Meeting created.')
        );
        $this->reset('title', 'description', 'date', 'time_from', 'time_to', 'type', 'other_type', 'image', 'existingImage', 'removeImage', 'venue', 'link', 'meeting_id', 'passcode', 'reportLinks', 'editingId', 'showModal');
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete meetings'), 403);

        $meeting = Meeting::findOrFail($id);

        $this->deletingId = $meeting->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $meeting->title]))
            ->text(__('This action cannot be undone.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deleteMeeting')
            ->show();
    }

    /**
     * Delete the meeting.
     */
    public function deleteMeeting(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $meeting = Meeting::find($this->deletingId);

        if ($meeting && $meeting->image) {
            Storage::disk('public')->delete($meeting->image);
        }

        Meeting::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('meetingUpdated');
        Flux::toast(variant: 'success', text: __('Meeting deleted.'));
    }

    /**
     * Add a new report link input.
     */
    public function addReportLink(): void
    {
        $this->reportLinks[] = [
            'title' => '',
            'description' => '',
            'url' => '',
        ];
    }

    /**
     * Remove a report link at the given index.
     */
    public function removeReportLink(int $index): void
    {
        if (isset($this->reportLinks[$index])) {
            unset($this->reportLinks[$index]);
            $this->reportLinks = array_values($this->reportLinks);
        }
    }

    /**
     * Normalize the report inputs into structured documents, dropping empty entries.
     *
     * @return list<array{title: string|null, description: string|null, url: string}>
     */
    protected function normalizedReports(): ?array
    {
        $reports = collect($this->reportLinks ?? [])
            ->map(fn ($report) => [
                'title' => filled($report['title'] ?? null) ? $report['title'] : null,
                'description' => filled($report['description'] ?? null) ? $report['description'] : null,
                'url' => $report['url'] ?? null,
            ])
            ->filter(fn ($report) => filled($report['url']))
            ->values()
            ->all();

        return $reports ?: null;
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->reset('title', 'description', 'date', 'time_from', 'time_to', 'type', 'other_type', 'image', 'existingImage', 'removeImage', 'venue', 'link', 'meeting_id', 'passcode', 'reportLinks', 'mission_id', 'editingId', 'showModal');
    }
}
