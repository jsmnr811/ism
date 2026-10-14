<?php

use App\Livewire\Missions\Missions;
use App\Models\Mission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);
});

test('missions page requires authentication', function () {
    auth()->logout();

    $this->get(route('missions.index'))
        ->assertRedirect(route('login'));
});

test('missions page can be rendered by authenticated user', function () {
    $this->get(route('missions.index'))
        ->assertOk();
});

test('missions page displays existing missions', function () {
    Mission::factory()->create(['title' => 'Annual Audit']);
    Mission::factory()->create(['title' => 'Quarterly Review']);

    Livewire::test(Missions::class)
        ->assertSee('Annual Audit')
        ->assertSee('Quarterly Review');
});

test('user can create a new mission', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'Security Audit')
        ->set('start_date', '2026-07-02')
        ->set('end_date', '2026-07-18')
        ->call('saveMission')
        ->assertHasNoErrors();

    $mission = Mission::where('title', 'Security Audit')->first();

    $this->assertNotNull($mission);
    $this->assertSame('2026-07-02', $mission->start_date->format('Y-m-d'));
    $this->assertSame('2026-07-18', $mission->end_date->format('Y-m-d'));
});

test('user can edit an existing mission', function () {
    $mission = Mission::factory()->create(['title' => 'Old Title']);

    Livewire::test(Missions::class)
        ->call('editMission', $mission->id)
        ->set('title', 'New Title')
        ->set('start_date', '2026-07-02')
        ->set('end_date', '2026-07-18')
        ->call('saveMission')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'title' => 'New Title',
    ]);
    $this->assertDatabaseMissing('missions', [
        'id' => $mission->id,
        'title' => 'Old Title',
    ]);
});

test('user can delete a mission through the livewire alert confirmation', function () {
    $mission = Mission::factory()->create(['title' => 'Delete Me']);

    Livewire::test(Missions::class)
        ->call('confirmDelete', $mission->id)
        ->assertSet('deletingId', $mission->id)
        ->call('deleteMission');

    $this->assertDatabaseMissing('missions', [
        'id' => $mission->id,
    ]);
});

test('validation requires mission title', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', '')
        ->call('saveMission')
        ->assertHasErrors('title');
});

test('validation requires minimum length for mission title', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'ab')
        ->call('saveMission')
        ->assertHasErrors('title');
});

test('search filters missions', function () {
    Mission::factory()->create(['title' => 'Alpha Mission']);
    Mission::factory()->create(['title' => 'Beta Mission']);

    Livewire::test(Missions::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Mission')
        ->assertDontSee('Beta Mission');
});

test('create modal resets form fields', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'temp mission')
        ->set('start_date', '2026-07-02')
        ->set('end_date', '2026-07-18')
        ->call('closeModal')
        ->assertSet('title', '')
        ->assertSet('editingId', null)
        ->assertSet('showModal', false);
});

test('createMission aborts for user without create permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    Livewire::test(Missions::class)
        ->call('createMission')
        ->assertForbidden();
});

test('editMission aborts for user without edit permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $mission = Mission::factory()->create();

    Livewire::test(Missions::class)
        ->call('editMission', $mission->id)
        ->assertForbidden();
});

test('confirmDelete aborts for user without delete permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $mission = Mission::factory()->create();

    Livewire::test(Missions::class)
        ->call('confirmDelete', $mission->id)
        ->assertForbidden();
});

test('missions page denied for user without view permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $this->get(route('missions.index'))
        ->assertForbidden();
});

test('edit modal pre-fills existing mission data', function () {
    $mission = Mission::factory()->create([
        'title' => 'Test Mission',
        'start_date' => '2026-07-02',
        'end_date' => '2026-07-18',
    ]);

    Livewire::test(Missions::class)
        ->call('editMission', $mission->id)
        ->assertSet('title', 'Test Mission')
        ->assertSet('start_date', '2026-07-02')
        ->assertSet('end_date', '2026-07-18');
});

test('duration fields are optional', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'No Duration Mission')
        ->set('start_date', '')
        ->set('end_date', '')
        ->call('saveMission')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('missions', [
        'title' => 'No Duration Mission',
        'start_date' => null,
        'end_date' => null,
    ]);
});

test('end_date must be after or equal to start_date', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'Date Range Test')
        ->set('start_date', '2026-07-18')
        ->set('end_date', '2026-07-02')
        ->call('saveMission')
        ->assertHasErrors('end_date');
});

test('banner image can be uploaded', function () {
    Storage::fake('public');

    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'Mission With Banner')
        ->set('banner', UploadedFile::fake()->image('banner.png'))
        ->call('saveMission')
        ->assertHasNoErrors();

    $mission = Mission::where('title', 'Mission With Banner')->first();

    $this->assertNotNull($mission->banner);
    Storage::disk('public')->assertExists($mission->banner);
});

test('agenda link and registration can be saved', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'Registered Mission')
        ->set('agenda_link', 'https://drive.google.com/agenda')
        ->set('enable_registration', true)
        ->call('saveMission')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('missions', [
        'title' => 'Registered Mission',
        'agenda_link' => 'https://drive.google.com/agenda',
        'enable_registration' => true,
    ]);
});

test('old banner is deleted when replaced on edit', function () {
    Storage::fake('public');

    $storedBanner = 'missions/old-banner.png';
    Storage::disk('public')->put($storedBanner, 'fake-content');

    $mission = Mission::factory()->create([
        'title' => 'Banner Mission',
        'banner' => $storedBanner,
    ]);

    Livewire::test(Missions::class)
        ->call('editMission', $mission->id)
        ->set('banner', UploadedFile::fake()->image('new-banner.png'))
        ->call('saveMission')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing($storedBanner);
    $mission->refresh();
    Storage::disk('public')->assertExists($mission->banner);
});

test('banner is deleted when mission is deleted', function () {
    Storage::fake('public');

    $bannerPath = 'missions/delete-banner.png';
    Storage::disk('public')->put($bannerPath, 'fake-content');

    $mission = Mission::factory()->create([
        'title' => 'Delete With Banner',
        'banner' => $bannerPath,
    ]);

    Livewire::test(Missions::class)
        ->call('confirmDelete', $mission->id)
        ->call('deleteMission');

    Storage::disk('public')->assertMissing($bannerPath);
    $this->assertDatabaseMissing('missions', ['id' => $mission->id]);
});

test('only one mission can be active at a time', function () {
    Mission::factory()->create(['title' => 'Active Mission', 'is_active' => true]);

    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'Second Active')
        ->set('is_active', true)
        ->call('saveMission')
        ->assertHasErrors('is_active');
});

test('editing active mission preserves its active status without error', function () {
    $mission = Mission::factory()->create(['title' => 'Active Mission', 'is_active' => true]);

    Livewire::test(Missions::class)
        ->call('editMission', $mission->id)
        ->set('title', 'Updated Active Mission')
        ->call('saveMission')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'title' => 'Updated Active Mission',
        'is_active' => true,
    ]);
});

test('registration status is independent from active status', function () {
    Livewire::test(Missions::class)
        ->call('createMission')
        ->set('title', 'Inactive With Registration')
        ->set('is_active', false)
        ->set('enable_registration', true)
        ->call('saveMission')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('missions', [
        'title' => 'Inactive With Registration',
        'is_active' => false,
        'enable_registration' => true,
    ]);
});
