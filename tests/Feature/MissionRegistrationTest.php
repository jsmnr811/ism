<?php

use App\Enums\MissionComponentUnit;
use App\Enums\MissionManagementTier;
use App\Enums\MissionOffice;
use App\Enums\MissionPsoCluster;
use App\Enums\Sex;
use App\Livewire\MissionRegistrations\MissionRegistrationForm;
use App\Models\Mission;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('registration page is accessible when enabled', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    $this->get(route('missions.register', $mission))
        ->assertOk();
});

test('registration page is forbidden when not enabled', function () {
    $mission = Mission::factory()->create(['enable_registration' => false]);

    $this->get(route('missions.register', $mission))
        ->assertForbidden();
});

test('user can submit a registration for guest office', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Juan')
        ->set('middle_initial', 'A')
        ->set('last_name', 'Dela Cruz')
        ->set('office', MissionOffice::Guest->value)
        ->set('position', 'Project Manager')
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Male->value)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('mission_registrations', [
        'mission_id' => $mission->id,
        'first_name' => 'Juan',
        'middle_initial' => 'A',
        'last_name' => 'Dela Cruz',
        'office' => MissionOffice::Guest->value,
        'position' => 'Project Manager',
        'contact_number' => '09123456789',
        'sex' => Sex::Male->value,
    ]);
});

test('user can submit a registration for PRDP office with management tier', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Maria')
        ->set('last_name', 'Santos')
        ->set('office', MissionOffice::PRDP->value)
        ->set('management_tier', MissionManagementTier::NPCO->value)
        ->set('position', 'Director')
        ->set('component_unit', MissionComponentUnit::OfficeOfNationalProjectDirector->value)
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Female->value)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('mission_registrations', [
        'mission_id' => $mission->id,
        'office' => MissionOffice::PRDP->value,
        'management_tier' => MissionManagementTier::NPCO->value,
        'component_unit' => MissionComponentUnit::OfficeOfNationalProjectDirector->value,
    ]);
});

test('validation requires first name and last name', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', '')
        ->set('last_name', '')
        ->call('save')
        ->assertHasErrors(['first_name', 'last_name']);
});

test('validation requires office and contact number', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Juan')
        ->set('last_name', 'Dela Cruz')
        ->set('office', '')
        ->set('contact_number', '')
        ->call('save')
        ->assertHasErrors(['office', 'contact_number']);
});

test('pso_cluster is required when management tier is PSO', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Juan')
        ->set('last_name', 'Dela Cruz')
        ->set('office', MissionOffice::PRDP->value)
        ->set('management_tier', MissionManagementTier::PSO->value)
        ->set('position', 'Manager')
        ->set('component_unit', MissionComponentUnit::IPLAN->value)
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Male->value)
        ->call('save')
        ->assertHasErrors('pso_cluster');
});

test('rpco_office is required when management tier is RPCO', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Juan')
        ->set('last_name', 'Dela Cruz')
        ->set('office', MissionOffice::PRDP->value)
        ->set('management_tier', MissionManagementTier::RPCO->value)
        ->set('position', 'Manager')
        ->set('component_unit', MissionComponentUnit::IPLAN->value)
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Male->value)
        ->call('save')
        ->assertHasErrors('rpco_office');
});

test('component_unit is required when office is PRDP', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Juan')
        ->set('last_name', 'Dela Cruz')
        ->set('office', MissionOffice::PRDP->value)
        ->set('management_tier', MissionManagementTier::NPCO->value)
        ->set('position', 'Manager')
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Male->value)
        ->call('save')
        ->assertHasErrors('component_unit');
});

test('form resets after successful submission', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Juan')
        ->set('last_name', 'Dela Cruz')
        ->set('office', MissionOffice::Guest->value)
        ->set('position', 'Manager')
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Male->value)
        ->call('save')
        ->assertSet('first_name', '')
        ->assertSet('last_name', '')
        ->assertSet('office', null);
});

test('registration can be created with all PRDP fields', function () {
    $mission = Mission::factory()->create(['enable_registration' => true]);

    Livewire::test(MissionRegistrationForm::class, ['mission' => $mission])
        ->set('first_name', 'Maria')
        ->set('middle_initial', 'S')
        ->set('last_name', 'Reyes')
        ->set('office', MissionOffice::PRDP->value)
        ->set('management_tier', MissionManagementTier::PSO->value)
        ->set('pso_cluster', MissionPsoCluster::NorthLuzon->value)
        ->set('position', 'Cluster Coordinator')
        ->set('component_unit', MissionComponentUnit::IBUILD->value)
        ->set('contact_number', '09123456789')
        ->set('email', 'test@example.com')
        ->set('sex', Sex::Female->value)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('mission_registrations', [
        'mission_id' => $mission->id,
        'pso_cluster' => MissionPsoCluster::NorthLuzon->value,
        'management_tier' => MissionManagementTier::PSO->value,
    ]);
});
