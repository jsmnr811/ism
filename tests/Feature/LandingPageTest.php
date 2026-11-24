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

it('renders the landing page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('SP Profile & Documents', false);
});

it('lazy loads the heavy landing sections until requested', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee("lazySection('agenda')", false)
        ->assertDontSee("lazySection('meetings')", false)
        ->assertSee("lazySection('site-visits')", false)
        ->assertSee("lazySection('recordings')", false)
        ->assertSee("lazySection('gallery')", false)
        ->assertDontSee('id="agenda-section"', false)
        ->assertDontSee('id="meetings-section"', false)
        ->assertDontSee('id="site-visits-section"', false)
        ->assertDontSee('id="googlePhotosStreamContainer"', false);
});

it('renders every lazy section on demand', function (string $section) {
    $this->get(route('landing.section', ['section' => $section]))->assertOk();
})->with(['agenda', 'meetings', 'site-visits', 'registration', 'recordings', 'gallery', 'reports']);

it('rejects unknown lazy section names', function () {
    $this->get(route('landing.section', ['section' => 'nope']))->assertNotFound();
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

it('counts report documents from meeting reports', function () {
    $mission = Mission::factory()->create(['is_active' => true]);

    Meeting::factory()->create([
        'mission_id' => $mission->id,
        'reports' => [
            ['title' => 'Kickoff Deck', 'description' => null, 'url' => 'https://example.com/deck'],
            ['title' => 'Minutes', 'description' => null, 'url' => 'https://example.com/minutes'],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertViewHas('meetingsCount', 1)
        ->assertViewHas('reportsCount', 2)
        ->assertViewHas('hasReports', true);
});

it('exposes meeting video links in the landing dataset', function () {
    $mission = Mission::factory()->create(['is_active' => true, 'enable_registration' => true]);

    Meeting::factory()->create([
        'mission_id' => $mission->id,
        'video_links' => [
            ['title' => 'Session Recording', 'url' => 'https://youtu.be/session123'],
        ],
    ]);

    $this->get(route('landing.data'))
        ->assertOk()
        ->assertJsonFragment([
            'title' => 'Session Recording',
            'url' => 'https://youtu.be/session123',
        ]);
});

it('shows meeting recordings in their own section', function () {
    $mission = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    Meeting::factory()->create([
        'mission_id' => $mission->id,
        'title' => 'Recorded Kick-off',
        'recording_link' => 'https://youtu.be/recorded-kickoff',
        'transcription_link' => 'https://drive.google.com/recorded-kickoff-transcript',
    ]);

    $this->get(route('landing.section', ['section' => 'recordings']))
        ->assertOk()
        ->assertSee('Meeting Recordings')
        ->assertSee('Recorded Kick-off')
        ->assertSee('https://youtu.be/recorded-kickoff')
        ->assertSee('Transcript')
        ->assertSee('https://drive.google.com/recorded-kickoff-transcript');
});

it('exposes meeting recording links in the landing dataset', function () {
    $mission = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    Meeting::factory()->create([
        'mission_id' => $mission->id,
        'title' => 'Recorded Kick-off',
        'recording_link' => 'https://youtu.be/recorded-kickoff',
    ]);

    $this->get(route('landing.data'))
        ->assertOk()
        ->assertJsonFragment([
            'title' => 'Recorded Kick-off',
            'recording_link' => 'https://youtu.be/recorded-kickoff',
        ]);
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

    $response = $this->get(route('landing.section', ['section' => 'site-visits']));

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

    $this->get('/')
        ->assertOk()
        ->assertViewHas('bannerMission', fn ($mission) => $mission->id === $inactive->id);

    $response = $this->get(route('landing.section', ['section' => 'site-visits']));

    $response->assertOk();
    expect($response->viewData('siteVisitsMission')->id)->toBe($inactive->id);
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

    $response = $this->get(route('landing.section', ['section' => 'meetings']));

    $response->assertOk();
    expect($response->viewData('meetings'))->toHaveCount(2);
    expect($response->viewData('meetingsMission')->id)->toBe($active->id);
});

it('exposes site visit document and virtual site visit links in the dataset', function () {
    $mission = Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    SiteVisit::factory()->create([
        'mission_id' => $mission->id,
        'document_link' => 'https://example.test/document.pdf',
        'virtual_site_visit_link' => 'https://example.test/virtual-tour',
    ]);

    $visit = $this->getJson(route('landing.data'))->assertOk()->json('siteVisits.0');

    expect($visit['document_link'])->toBe('https://example.test/document.pdf');
    expect($visit['virtual_site_visit_link'])->toBe('https://example.test/virtual-tour');
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

    $response = $this->get(route('landing.section', ['section' => 'meetings']));

    $response->assertOk();
    expect($response->viewData('meetingsMission')->id)->toBe($inactive->id);
    expect($response->viewData('meetings'))->toHaveCount(1);
});

it('shows only the selected mission photos in the photo stream dataset', function () {
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

    $photos = $this->getJson(route('landing.data'))->assertOk()->json('photos');

    expect($photos)->toHaveCount(1);
    expect($photos[0]['caption'])->toBe('Active stream capture');
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

    $data = $this->getJson(route('landing.data'))->assertOk();

    expect($data->json('photos'))->toBeEmpty();
    expect($data->json('siteVisits.0.images.0.url'))->toContain('agt-capture.jpg');

    // The banner is still rendered with the initial page.
    $this->get('/')->assertOk()->assertSee('banner.jpg', false);
});

it('allows photo uploads while a mission is active', function () {
    Mission::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $this->getJson(route('landing.data'))
        ->assertOk()
        ->assertJsonPath('canUploadPhotos', true);

    $this->get(route('landing.section', ['section' => 'gallery']))
        ->assertOk()
        ->assertSee('Upload Photo');
});

it('allows photo uploads within two weeks after the latest mission ends', function () {
    Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->subWeek()->toDateString(),
    ]);

    $this->getJson(route('landing.data'))
        ->assertOk()
        ->assertJsonPath('canUploadPhotos', true);
});

it('closes photo uploads after two weeks past the latest mission', function () {
    Mission::factory()->create([
        'is_active' => false,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subWeeks(3)->toDateString(),
    ]);

    $this->getJson(route('landing.data'))
        ->assertOk()
        ->assertJsonPath('canUploadPhotos', false);

    $this->get(route('landing.section', ['section' => 'gallery']))
        ->assertOk()
        ->assertDontSee('Upload Photo');
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

it('serves mission photos without relying on the public storage symlink', function () {
    Storage::fake('public');

    $mission = Mission::factory()->create();
    $path = UploadedFile::fake()->image('capture.jpg')->store('mission-photos', 'public');

    $photo = MissionPhoto::factory()->create([
        'mission_id' => $mission->id,
        'image_path' => $path,
    ]);

    expect($photo->image_url)->toContain('/mission-photos/'.$photo->id);

    $this->get($photo->image_url)->assertOk();
});

it('serves legacy mission photo paths from public and private storage', function () {
    Storage::fake('public');
    Storage::fake('local');

    $mission = Mission::factory()->create();

    Storage::disk('public')->put('mission-photos/legacy-public.jpg', 'image-bytes');
    $publicPhoto = MissionPhoto::factory()->create([
        'mission_id' => $mission->id,
        'image_path' => '/storage/mission-photos/legacy-public.jpg',
    ]);

    Storage::disk('local')->put('mission-photos/legacy-private.jpg', 'image-bytes');
    $privatePhoto = MissionPhoto::factory()->create([
        'mission_id' => $mission->id,
        'image_path' => 'mission-photos/legacy-private.jpg',
    ]);

    $this->get($publicPhoto->image_url)->assertOk();
    $this->get($privatePhoto->image_url)->assertOk();
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

    $photos = $this->getJson(route('landing.data'))->assertOk()->json('photos');

    expect($photos)->toHaveCount(1);
    expect($photos[0]['caption'])->toBe('Active mission capture');
});
