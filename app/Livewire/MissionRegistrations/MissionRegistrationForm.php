<?php

namespace App\Livewire\MissionRegistrations;

use App\Enums\MissionComponentUnit;
use App\Enums\MissionManagementTier;
use App\Enums\MissionOffice;
use App\Enums\MissionPsoCluster;
use App\Enums\MissionRpcoOffice;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Models\Mission;
use App\Models\MissionRegistration;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mission Registration')]
class MissionRegistrationForm extends Component
{
    public Mission $mission;

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

    public function mount(Mission $mission)
    {
        $this->mission = $mission;
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

    public function save(): void
    {
        $validated = $this->validate([
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
                'required',
                'string',
                'email',
                'max:255',
            ],
        ]);

        $validated['mission_id'] = $this->mission->id;

        MissionRegistration::create($validated);

        Flux::toast(
            variant: 'success',
            text: __('Registration submitted successfully.')
        );

        $this->reset([
            'first_name', 'middle_initial', 'suffix', 'last_name', 'office',
            'management_tier', 'pso_cluster', 'rpco_office', 'position',
            'component_unit', 'contact_number', 'sex', 'email',
        ]);
    }

    public function render()
    {
        return view('livewire.mission-registrations.mission-registration-form')
            ->layout('layouts.guest');
    }
}
