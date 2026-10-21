<?php

use App\Models\Meeting;
use App\Models\Mission;
use App\Models\MissionPhoto;
use App\Models\SiteVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Extract the photo stream payload rendered on the landing page.
 *
 * @return list<array<string, mixed>>
 */
function photosStreamFrom(string $html): array
{
    preg_match('/const missionPhotosData = (\[.*?\]);/s', $html, $matches);

    return json_decode($matches[1] ?? '[]', true) ?: [];
}

it('renders the landing page', function () {
    $this->get('/')->assertOk();
});

it('shows the scale up favicon on every page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('/favicon.ico', false)
        ->assertSee('/favicon-32x32.png', false)
        ->assertSee('/apple-touch-icon.png', false);
});

it('shows the scale up favicon on authenticated pages', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('/favicon.ico', false)
        ->assertSee('/favicon-32x32.png', false)
        ->assertSee('/apple-touch-icon.png', false);
});

it('shows site visits for the active mission', function () {
    $active = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $previous = Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subMonth()->toDateString(),
    ]);

    SiteVisit::factory()->count(2)->create(['mission_id' => $active->id]);
    SiteVisit::factory()->create(['mission_id' => $previous->id]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee($active->title);
    expect($response->viewData('siteVisits'))->toHaveCount(2);
    expect($response->viewData('siteVisitsMission')->id)->toBe($active->id);
});

it('falls back to the latest mission when no mission is active by date', function () {
    $inactive = Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(3)->toDateString(),
        'end_date' => now()->subMonths(2)->toDateString(),
    ]);

    SiteVisit::factory()->create(['mission_id' => $inactive->id]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('siteVisitsMission')->id)->toBe($inactive->id);
    expect($response->viewData('bannerMission')->id)->toBe($inactive->id);
});

it('shows meetings of the active mission', function () {
    $active = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $previous = Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subMonth()->toDateString(),
    ]);

    Meeting::factory()->count(2)->create(['mission_id' => $active->id]);
    Meeting::factory()->create(['mission_id' => $previous->id]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('meetings'))->toHaveCount(2);
    expect($response->viewData('meetingsMission')->id)->toBe($active->id);
});

it('falls back to the latest mission meetings when no mission is active by date', function () {
    $inactive = Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(3)->toDateString(),
        'end_date' => now()->subMonths(2)->toDateString(),
    ]);

    Meeting::factory()->create([
        'mission_id' => $inactive->id,
        'date' => now()->subMonths(2)->toDateString(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('meetingsMission')->id)->toBe($inactive->id);
    expect($response->viewData('meetings'))->toHaveCount(1);
});

it('shows only the selected mission photos in the photo stream', function () {
    $active = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $previous = Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subMonth()->toDateString(),
    ]);

    MissionPhoto::factory()->create([
        'mission_id' => $active->id,
        'caption' => 'Active stream capture',
    ]);
    MissionPhoto::factory()->create([
        'mission_id' => $previous->id,
        'caption' => 'Previous stream capture',
    ]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('photosMission')->id)->toBe($active->id);

    $stream = photosStreamFrom($response->getContent());

    expect($stream)->toHaveCount(1);
    expect($stream[0]['caption'])->toBe('Active stream capture');
});

it('keeps site visit photos and the mission banner out of the photo stream', function () {
    $mission = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'banner' => 'missions/banner.jpg',
    ]);

    SiteVisit::factory()->create([
        'mission_id' => $mission->id,
        'images' => ['site-visits/agt-capture.jpg'],
    ]);

    $response = $this->get('/');

    $response->assertOk();

    $stream = photosStreamFrom($response->getContent());

    expect($stream)->toBeEmpty();

    // The site visit image and the banner are still rendered in their own sections.
    $response->assertSee('agt-capture.jpg', false);
    $response->assertSee('banner.jpg', false);
});

it('allows photo uploads while a mission is active', function () {
    Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('photosUploadAllowed'))->toBeTrue();
});

it('allows photo uploads within two weeks after the latest mission ends', function () {
    Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->subWeek()->toDateString(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('photosUploadAllowed'))->toBeTrue();
});

it('closes photo uploads after two weeks past the latest mission', function () {
    Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subWeeks(3)->toDateString(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('photosUploadAllowed'))->toBeFalse();
});

it('stores uploaded photos for the active mission', function () {
    Storage::fake('public');

    $mission = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $response = $this->post(route('landing.photos'), [
        'images' => [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ],
        'caption' => 'Core drill test',
        'location' => 'Balingasag',
    ]);

    $response->assertOk();
    $response->assertJsonPath('message', '2 photos published.');

    $this->assertDatabaseCount('mission_photos', 2);

    $photo = MissionPhoto::first();
    expect($photo->mission_id)->toBe($mission->id);
    expect($photo->caption)->toBe('Core drill test');
    expect($photo->location)->toBe('Balingasag');
    Storage::disk('public')->assertExists($photo->image_path);
});

it('stores uploaded photos without a caption or location', function () {
    Storage::fake('public');

    $mission = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $response = $this->post(route('landing.photos'), [
        'images' => [UploadedFile::fake()->image('one.jpg')],
    ]);

    $response->assertOk();

    $photo = MissionPhoto::first();
    expect($photo->mission_id)->toBe($mission->id);
    expect($photo->caption)->toBeNull();
    expect($photo->location)->toBeNull();
});

it('rejects photo uploads when the upload window is closed', function () {
    Storage::fake('public');

    Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subWeeks(3)->toDateString(),
    ]);

    $this->post(route('landing.photos'), [
        'images' => [UploadedFile::fake()->image('one.jpg')],
    ])->assertForbidden();

    $this->assertDatabaseCount('mission_photos', 0);
});

it('rejects photo uploads without an image', function () {
    Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $this->post(route('landing.photos'), [
        'images' => [],
        'caption' => 'Missing image',
    ])->assertSessionHasErrors('images');
});

it('shows stored mission photos for the selected mission', function () {
    $active = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $previous = Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subMonth()->toDateString(),
    ]);

    MissionPhoto::factory()->create([
        'mission_id' => $active->id,
        'caption' => 'Active mission capture',
    ]);
    MissionPhoto::factory()->create([
        'mission_id' => $previous->id,
        'caption' => 'Previous mission capture',
    ]);

    $response = $this->get('/');

    $response->assertOk();
    expect($response->viewData('missionPhotos'))->toHaveCount(1);
    $response->assertSee('Active mission capture');
    $response->assertDontSee('Previous mission capture');
});
