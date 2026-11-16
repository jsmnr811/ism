<?php

use App\Enums\MissionOffice;
use App\Enums\RegistrationSource;
use App\Enums\Sex;
use App\Livewire\MissionRegistrations\MissionRegistrations;
use App\Models\Meeting;
use App\Models\Mission;
use App\Models\MissionRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

test('mission registrations page requires authentication', function () {
    auth()->logout();

    $this->get(route('mission-registrations.index'))
        ->assertRedirect(route('login'));
});

test('mission registrations page is forbidden without permission', function () {
    auth()->logout();

    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $this->get(route('mission-registrations.index'))
        ->assertForbidden();
});

test('mission registrations page displays landing and manual registrations', function () {
    $mission = Mission::factory()->create(['title' => 'Midterm Review']);

    $landing = MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'Landing',
        'last_name' => 'Participant',
        'source' => RegistrationSource::Landing->value,
        'registered_by' => null,
        'attendance_dates' => ['2026-10-05', '2026-10-08'],
    ]);

    $manual = MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'Manual',
        'last_name' => 'Participant',
        'source' => RegistrationSource::Manual->value,
        'registered_by' => $this->admin->id,
    ]);

    Livewire::test(MissionRegistrations::class)
        ->assertSee($landing->fullName())
        ->assertSee($manual->fullName())
        ->assertSee('Midterm Review')
        ->assertSee(RegistrationSource::Landing->label())
        ->assertSee(RegistrationSource::Manual->label())
        ->assertSee('Oct 5, 2026')
        ->assertSee('Oct 8, 2026');
});

test('registrations can be filtered by source', function () {
    $mission = Mission::factory()->create();

    MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'Landing',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Participant',
        'source' => RegistrationSource::Landing->value,
    ]);

    MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'Manual',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Participant',
        'source' => RegistrationSource::Manual->value,
    ]);

    Livewire::test(MissionRegistrations::class)
        ->set('sourceFilter', RegistrationSource::Manual->value)
        ->assertSee('Manual Participant')
        ->assertDontSee('Landing Participant');
});

test('registrations can be filtered by mission', function () {
    $firstMission = Mission::factory()->create(['title' => 'First Mission']);
    $secondMission = Mission::factory()->create(['title' => 'Second Mission']);

    MissionRegistration::factory()->create([
        'mission_id' => $firstMission->id,
        'first_name' => 'First',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Registrant',
    ]);

    MissionRegistration::factory()->create([
        'mission_id' => $secondMission->id,
        'first_name' => 'Second',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Registrant',
    ]);

    Livewire::test(MissionRegistrations::class)
        ->set('missionFilter', (string) $secondMission->id)
        ->assertSee('Second Registrant')
        ->assertDontSee('First Registrant');
});

test('registrations can be filtered by office', function () {
    $mission = Mission::factory()->create();

    MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'Guest',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Attendee',
        'office' => MissionOffice::Guest->value,
    ]);

    MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'WorldBank',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Attendee',
        'office' => MissionOffice::WorldBank->value,
    ]);

    Livewire::test(MissionRegistrations::class)
        ->set('officeFilter', MissionOffice::WorldBank->value)
        ->assertSee('WorldBank Attendee')
        ->assertDontSee('Guest Attendee');
});

test('registrations can be filtered by attendance date', function () {
    $mission = Mission::factory()->create();

    MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'FirstDay',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Attendee',
        'attendance_dates' => ['2026-10-05', '2026-10-08'],
    ]);

    MissionRegistration::factory()->create([
        'mission_id' => $mission->id,
        'first_name' => 'LastDay',
        'middle_initial' => null,
        'suffix' => null,
        'last_name' => 'Attendee',
        'attendance_dates' => ['2026-10-23'],
    ]);

    Livewire::test(MissionRegistrations::class)
        ->set('attendanceDateFilter', '2026-10-08')
        ->assertSee('FirstDay Attendee')
        ->assertDontSee('LastDay Attendee');
});

test('attendance date options follow the selected mission agenda', function () {
    $firstMission = Mission::factory()->create();
    $secondMission = Mission::factory()->create();

    Meeting::factory()->create(['mission_id' => $firstMission->id, 'date' => '2026-10-05']);
    Meeting::factory()->create(['mission_id' => $firstMission->id, 'date' => '2026-10-08']);
    Meeting::factory()->create(['mission_id' => $secondMission->id, 'date' => '2026-10-23']);

    $component = Livewire::test(MissionRegistrations::class)
        ->set('missionFilter', (string) $firstMission->id);

    expect($component->instance()->agendaDates)->toBe(['2026-10-05', '2026-10-08']);

    $component
        ->assertSee('Oct 5, 2026')
        ->assertSee('Oct 8, 2026')
        ->assertDontSee('Oct 23, 2026');
});

test('attendance date filter resets when the selected mission changes', function () {
    $firstMission = Mission::factory()->create();
    $secondMission = Mission::factory()->create();

    Meeting::factory()->create(['mission_id' => $firstMission->id, 'date' => '2026-10-05']);
    Meeting::factory()->create(['mission_id' => $secondMission->id, 'date' => '2026-10-23']);

    Livewire::test(MissionRegistrations::class)
        ->set('missionFilter', (string) $firstMission->id)
        ->set('attendanceDateFilter', '2026-10-05')
        ->set('missionFilter', (string) $secondMission->id)
        ->assertSet('attendanceDateFilter', null);
});

test('user can manually create a registration', function () {
    $mission = Mission::factory()->create();

    Livewire::test(MissionRegistrations::class)
        ->call('createRegistration')
        ->set('mission_id', $mission->id)
        ->set('first_name', 'Juan')
        ->set('middle_initial', 'A')
        ->set('last_name', 'Dela Cruz')
        ->set('office', MissionOffice::Guest->value)
        ->set('position', 'Project Manager')
        ->set('contact_number', '09123456789')
        ->set('email', 'juan@example.com')
        ->set('sex', Sex::Male->value)
        ->call('saveRegistration')
        ->assertHasNoErrors();

    $registration = MissionRegistration::where('first_name', 'Juan')->first();

    expect($registration)->not->toBeNull()
        ->and($registration->source)->toBe(RegistrationSource::Manual)
        ->and($registration->registered_by)->toBe($this->admin->id)
        ->and($registration->mission_id)->toBe($mission->id);
});

test('manual registration requires mission and participant details', function () {
    Livewire::test(MissionRegistrations::class)
        ->call('createRegistration')
        ->set('mission_id', null)
        ->set('first_name', '')
        ->set('last_name', '')
        ->set('office', '')
        ->set('position', '')
        ->set('contact_number', '')
        ->set('sex', '')
        ->call('saveRegistration')
        ->assertHasErrors(['mission_id', 'first_name', 'last_name', 'office', 'position', 'contact_number', 'sex']);
});

test('component unit is required for PRDP office', function () {
    $mission = Mission::factory()->create();

    Livewire::test(MissionRegistrations::class)
        ->call('createRegistration')
        ->set('mission_id', $mission->id)
        ->set('first_name', 'Maria')
        ->set('last_name', 'Santos')
        ->set('office', MissionOffice::PRDP->value)
        ->set('position', 'Director')
        ->set('contact_number', '09123456789')
        ->set('email', 'maria@example.com')
        ->set('sex', Sex::Female->value)
        ->call('saveRegistration')
        ->assertHasErrors('component_unit');
});

test('editing a registration keeps its original source', function () {
    $registration = MissionRegistration::factory()->create([
        'first_name' => 'Old',
        'source' => RegistrationSource::Landing->value,
        'registered_by' => null,
    ]);

    Livewire::test(MissionRegistrations::class)
        ->call('editRegistration', $registration->id)
        ->set('first_name', 'Updated')
        ->call('saveRegistration')
        ->assertHasNoErrors();

    $registration->refresh();

    expect($registration->first_name)->toBe('Updated')
        ->and($registration->source)->toBe(RegistrationSource::Landing)
        ->and($registration->registered_by)->toBeNull();
});

test('user can delete a registration', function () {
    $registration = MissionRegistration::factory()->create();

    Livewire::test(MissionRegistrations::class)
        ->call('confirmDelete', $registration->id)
        ->call('deleteRegistration');

    $this->assertDatabaseMissing('mission_registrations', ['id' => $registration->id]);
});

test('landing page submissions are recorded as landing registrations', function () {
    $mission = Mission::factory()->create([
        'is_active' => true,
        'enable_registration' => true,
    ]);

    $this->postJson(route('landing.register'), [
        'first_name' => 'Landing',
        'last_name' => 'Visitor',
        'office' => MissionOffice::Guest->value,
        'position' => 'Observer',
        'contact_number' => '09123456789',
        'email' => 'visitor@example.com',
        'sex' => Sex::Female->value,
    ])->assertOk();

    $this->assertDatabaseHas('mission_registrations', [
        'mission_id' => $mission->id,
        'first_name' => 'Landing',
        'source' => RegistrationSource::Landing->value,
        'registered_by' => null,
    ]);
});
