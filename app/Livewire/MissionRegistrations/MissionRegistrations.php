<?php

namespace App\Livewire\MissionRegistrations;

use App\Enums\MissionComponentUnit;
use App\Enums\MissionManagementTier;
use App\Enums\MissionOffice;
use App\Enums\MissionPsoCluster;
use App\Enums\MissionRpcoOffice;
use App\Enums\RegistrationSource;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Models\Meeting;
use App\Models\Mission;
use App\Models\MissionRegistration;
use Flux\Flux;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Mission Registrations')]
class MissionRegistrations extends Component
{
    use WithPagination;

    public string $search = '';

    public string $missionFilter = '';

    public string $sourceFilter = '';

    public string $officeFilter = '';

    public ?string $attendanceDateFilter = null;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public ?int $editingId = null;

    public bool $showModal = false;

    public ?int $viewingId = null;

    public bool $showViewModal = false;

    public ?int $deletingId = null;

    public ?int $mission_id = null;

    public string $first_name = '';

    public ?string $middle_initial = null;

    public ?string $suffix = null;

    public string $last_name = '';

    public ?string $office = null;

    public ?string $management_tier = null;

    public ?string $pso_cluster = null;

    public ?string $rpco_office = null;

    public string $position = '';

    public ?string $component_unit = null;

    public string $contact_number = '';

    public ?string $sex = null;

    public ?string $email = null;

    /**
     * Registrations matching the active filters, with sorting and pagination.
     */
    #[Computed]
    public function registrations()
    {
        $sortField = in_array($this->sortField, ['first_name', 'last_name', 'office', 'source', 'created_at'], true)
            ? $this->sortField
            : 'created_at';

        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return MissionRegistration::query()
            ->with(['mission', 'registeredBy'])
            ->when($this->search, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhereHas('mission', fn ($mission) => $mission->where('title', 'like', "%{$search}%"));
            }))
            ->when($this->missionFilter !== '', fn ($query) => $query->where('mission_id', $this->missionFilter))
            ->when($this->sourceFilter !== '', fn ($query) => $query->where('source', $this->sourceFilter))
            ->when($this->officeFilter !== '', fn ($query) => $query->where('office', $this->officeFilter))
            ->when($this->attendanceDateFilter, fn ($query) => $query->whereJsonContains('attendance_dates', $this->attendanceDateFilter))
            ->orderBy($sortField, $sortDirection)
            ->paginate(10);
    }

    /**
     * Total number of registrations.
     */
    #[Computed]
    public function totalRegistrations(): int
    {
        return MissionRegistration::count();
    }

    /**
     * Registrations submitted through the public landing page.
     */
    #[Computed]
    public function totalLanding(): int
    {
        return MissionRegistration::where('source', RegistrationSource::Landing->value)->count();
    }

    /**
     * Registrations created manually by staff with permission.
     */
    #[Computed]
    public function totalManual(): int
    {
        return MissionRegistration::where('source', RegistrationSource::Manual->value)->count();
    }

    /**
     * Missions available for filtering and assignment.
     */
    #[Computed]
    public function missionsList()
    {
        return Mission::orderBy('title')->get();
    }

    /**
     * Attendance dates available in the agenda of the selected mission.
     *
     * @return list<string>
     */
    #[Computed]
    public function agendaDates(): array
    {
        return Meeting::query()
            ->when($this->missionFilter !== '', fn ($query) => $query->where('mission_id', $this->missionFilter))
            ->get(['date'])
            ->map(fn (Meeting $meeting) => $meeting->date?->format('Y-m-d'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Active mission used as the default for new registrations.
     */
    #[Computed]
    public function activeMission(): ?Mission
    {
        return Mission::where('is_active', true)->first();
    }

    /**
     * The registration currently shown in the detail modal.
     */
    #[Computed]
    public function viewing(): ?MissionRegistration
    {
        return $this->viewingId
            ? MissionRegistration::with(['mission', 'registeredBy'])->find($this->viewingId)
            : null;
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

    public function getOfficeOptions(): array
    {
        return MissionOffice::labels();
    }

    public function getManagementTierOptions(): array
    {
        return MissionManagementTier::labels();
    }

    public function getPsoClusterOptions(): array
    {
        return MissionPsoCluster::labels();
    }

    public function getRpcoOfficeOptions(): array
    {
        return MissionRpcoOffice::labels();
    }

    public function getComponentUnitOptions(): array
    {
        return MissionComponentUnit::labels();
    }

    public function getSexOptions(): array
    {
        return Sex::labels();
    }

    public function getSuffixOptions(): array
    {
        return Suffix::labels();
    }

    public function getSourceOptions(): array
    {
        return RegistrationSource::labels();
    }

    public function isPRDPOffice(): bool
    {
        return $this->office === MissionOffice::PRDP->value;
    }

    public function updatedOffice($value): void
    {
        $this->management_tier = null;
        $this->pso_cluster = null;
        $this->rpco_office = null;
        $this->component_unit = null;
    }

    public function updatedManagementTier($value): void
    {
        $this->pso_cluster = null;
        $this->rpco_office = null;
    }

    /**
     * Reset an attendance date that is not part of the newly selected mission agenda.
     */
    public function updatedMissionFilter(): void
    {
        if ($this->attendanceDateFilter && ! in_array($this->attendanceDateFilter, $this->agendaDates, true)) {
            $this->attendanceDateFilter = null;
        }
    }

    /**
     * Open the create modal.
     */
    public function createRegistration(): void
    {
        abort_if(! auth()->user()?->can('create mission registrations'), 403);

        $this->resetForm();
        $this->mission_id = $this->activeMission?->id;
        $this->showModal = true;
    }

    /**
     * Open the edit modal pre-filled with the registration data.
     */
    public function editRegistration(int $id): void
    {
        abort_if(! auth()->user()?->can('edit mission registrations'), 403);

        $registration = MissionRegistration::findOrFail($id);

        $this->resetForm();
        $this->reset('viewingId', 'showViewModal');

        $this->mission_id = $registration->mission_id;
        $this->first_name = $registration->first_name;
        $this->middle_initial = $registration->middle_initial;
        $this->suffix = $registration->suffix?->value;
        $this->last_name = $registration->last_name;
        $this->office = $registration->office?->value;
        $this->management_tier = $registration->management_tier?->value;
        $this->pso_cluster = $registration->pso_cluster?->value;
        $this->rpco_office = $registration->rpco_office?->value;
        $this->position = $registration->position;
        $this->component_unit = $registration->component_unit?->value;
        $this->contact_number = $registration->contact_number;
        $this->sex = $registration->sex?->value;
        $this->email = $registration->email;
        $this->editingId = $registration->id;
        $this->showModal = true;
    }

    /**
     * Show the registration detail modal.
     */
    public function viewRegistration(int $id): void
    {
        abort_if(! auth()->user()?->can('view mission registrations'), 403);

        $this->viewingId = MissionRegistration::findOrFail($id)->id;
        $this->showViewModal = true;
    }

    /**
     * Store or update the registration.
     */
    public function saveRegistration(): void
    {
        abort_if($this->editingId
            ? ! auth()->user()?->can('edit mission registrations')
            : ! auth()->user()?->can('create mission registrations'), 403);

        $validated = $this->validate([
            'mission_id' => [
                'required',
                'exists:missions,id',
            ],
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
            ],
            'middle_initial' => [
                'nullable',
                'string',
                'max:1',
            ],
            'suffix' => [
                'nullable',
                'string',
                'in:'.implode(',', array_keys(Suffix::labels())),
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
            ],
            'office' => [
                'required',
                'in:'.implode(',', array_keys(MissionOffice::labels())),
            ],
            'management_tier' => [
                'nullable',
                'in:'.implode(',', array_keys(MissionManagementTier::labels())),
            ],
            'pso_cluster' => [
                'nullable',
                'required_if:management_tier,'.MissionManagementTier::PSO->value,
                'in:'.implode(',', array_keys(MissionPsoCluster::labels())),
            ],
            'rpco_office' => [
                'nullable',
                'required_if:management_tier,'.MissionManagementTier::RPCO->value,
                'in:'.implode(',', array_keys(MissionRpcoOffice::labels())),
            ],
            'position' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'component_unit' => [
                'nullable',
                'required_if:office,'.MissionOffice::PRDP->value,
                'in:'.implode(',', array_keys(MissionComponentUnit::labels())),
            ],
            'contact_number' => [
                'required',
                'string',
                'min:7',
                'max:20',
            ],
            'sex' => [
                'required',
                'in:'.implode(',', array_keys(Sex::labels())),
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
            ],
        ]);

        $isPRDP = $validated['office'] === MissionOffice::PRDP->value;
        $isPSO = $isPRDP && $validated['management_tier'] === MissionManagementTier::PSO->value;
        $isRPCO = $isPRDP && $validated['management_tier'] === MissionManagementTier::RPCO->value;

        $data = [
            'mission_id' => $validated['mission_id'],
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?: null,
            'suffix' => $validated['suffix'] ?: null,
            'last_name' => $validated['last_name'],
            'office' => $validated['office'],
            'management_tier' => $isPRDP ? ($validated['management_tier'] ?: null) : null,
            'pso_cluster' => $isPSO ? ($validated['pso_cluster'] ?: null) : null,
            'rpco_office' => $isRPCO ? ($validated['rpco_office'] ?: null) : null,
            'position' => $validated['position'],
            'component_unit' => $isPRDP ? ($validated['component_unit'] ?: null) : null,
            'contact_number' => $validated['contact_number'],
            'sex' => $validated['sex'],
            'email' => $validated['email'] ?: null,
        ];

        $isEditing = (bool) $this->editingId;

        if ($isEditing) {
            MissionRegistration::findOrFail($this->editingId)->update($data);
        } else {
            MissionRegistration::create($data + [
                'source' => RegistrationSource::Manual->value,
                'registered_by' => auth()->id(),
            ]);
        }

        $this->dispatch('missionRegistrationUpdated');
        Flux::toast(
            variant: 'success',
            text: $isEditing
                ? __('Registration updated.')
                : __('Registration created.')
        );
        $this->closeModal();
    }

    /**
     * Show the delete confirmation alert.
     */
    public function confirmDelete(int $id): void
    {
        abort_if(! auth()->user()?->can('delete mission registrations'), 403);

        $registration = MissionRegistration::findOrFail($id);

        $this->deletingId = $registration->id;

        LivewireAlert::title(__('Are you sure you want to delete ":name"?', ['name' => $registration->fullName()]))
            ->text(__('This action cannot be undone.'))
            ->warning()
            ->withConfirmButton(__('Delete'))
            ->withCancelButton(__('Cancel'))
            ->onConfirm('deleteRegistration')
            ->show();
    }

    /**
     * Delete the registration.
     */
    public function deleteRegistration(): void
    {
        if (! $this->deletingId) {
            return;
        }

        MissionRegistration::find($this->deletingId)?->delete();

        $this->reset('deletingId');
        $this->dispatch('missionRegistrationUpdated');
        Flux::toast(variant: 'success', text: __('Registration deleted.'));
    }

    /**
     * Close the create/edit modal.
     */
    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    /**
     * Reset the registration form fields.
     */
    protected function resetForm(): void
    {
        $this->reset(
            'mission_id', 'first_name', 'middle_initial', 'suffix', 'last_name',
            'office', 'management_tier', 'pso_cluster', 'rpco_office', 'position',
            'component_unit', 'contact_number', 'sex', 'email', 'editingId'
        );
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.mission-registrations.mission-registrations');
    }
}
