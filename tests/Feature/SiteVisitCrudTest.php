<?php

use App\Livewire\SiteVisits\SiteVisits;
use App\Models\SiteVisit;
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

test('site visits page requires authentication', function () {
    auth()->logout();

    $this->get(route('site-visits.index'))
        ->assertRedirect(route('login'));
});

test('site visits page can be rendered by authenticated user', function () {
    $this->get(route('site-visits.index'))
        ->assertOk();
});

test('site visits page displays existing site visits', function () {
    SiteVisit::factory()->create(['sp_name' => 'Barangay Hall']);
    SiteVisit::factory()->create(['sp_name' => 'School Building']);

    Livewire::test(SiteVisits::class)
        ->assertSee('Barangay Hall')
        ->assertSee('School Building');
});

test('user can create a new site visit', function () {
    Storage::fake('public');

    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Community Center')
        ->set('cluster', 'north_luzon')
        ->set('component', 'i_build')
        ->set('location', 'Quezon City')
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('site_visits', [
        'sp_name' => 'Community Center',
        'cluster' => 'north_luzon',
        'component' => 'i_build',
        'location' => 'Quezon City',
    ]);
});

test('user can create a site visit with a date', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Irrigation Project')
        ->set('cluster', 'north_luzon')
        ->set('component', 'i_build')
        ->set('location', 'Baguio City')
        ->set('date', '2026-10-15')
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    $siteVisit = SiteVisit::where('sp_name', 'Irrigation Project')->first();

    $this->assertNotNull($siteVisit);
    $this->assertSame('2026-10-15', $siteVisit->date->format('Y-m-d'));
});

test('site visit date is optional', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Undated Project')
        ->set('cluster', 'north_luzon')
        ->set('component', 'i_build')
        ->set('date', null)
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    $siteVisit = SiteVisit::where('sp_name', 'Undated Project')->first();

    $this->assertNotNull($siteVisit);
    $this->assertNull($siteVisit->date);
});

test('site visit date must be a valid date', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Bad Date Project')
        ->set('cluster', 'north_luzon')
        ->set('component', 'i_build')
        ->set('date', 'not-a-date')
        ->call('saveSiteVisit')
        ->assertHasErrors('date');
});

test('edit modal pre-fills the site visit date', function () {
    $siteVisit = SiteVisit::factory()->create(['date' => '2026-09-20']);

    Livewire::test(SiteVisits::class)
        ->call('editSiteVisit', $siteVisit->id)
        ->assertSet('date', '2026-09-20');
});

test('user can edit an existing site visit', function () {
    $siteVisit = SiteVisit::factory()->create(['sp_name' => 'Old Name']);

    Livewire::test(SiteVisits::class)
        ->call('editSiteVisit', $siteVisit->id)
        ->set('sp_name', 'New Name')
        ->set('cluster', 'south_luzon')
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('site_visits', [
        'id' => $siteVisit->id,
        'sp_name' => 'New Name',
        'cluster' => 'south_luzon',
    ]);
    $this->assertDatabaseMissing('site_visits', [
        'id' => $siteVisit->id,
        'sp_name' => 'Old Name',
    ]);
});

test('user can delete a site visit through the livewire alert confirmation', function () {
    $siteVisit = SiteVisit::factory()->create(['sp_name' => 'Delete Me']);

    Livewire::test(SiteVisits::class)
        ->call('confirmDelete', $siteVisit->id)
        ->assertSet('deletingId', $siteVisit->id)
        ->call('deleteSiteVisit');

    $this->assertDatabaseMissing('site_visits', [
        'id' => $siteVisit->id,
    ]);
});

test('validation requires site visit sp_name', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', '')
        ->call('saveSiteVisit')
        ->assertHasErrors('sp_name');
});

test('validation requires minimum length for sp_name', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'ab')
        ->call('saveSiteVisit')
        ->assertHasErrors('sp_name');
});

test('search filters site visits', function () {
    SiteVisit::factory()->create(['sp_name' => 'Alpha Site']);
    SiteVisit::factory()->create(['sp_name' => 'Beta Site']);

    Livewire::test(SiteVisits::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Site')
        ->assertDontSee('Beta Site');
});

test('create modal resets form fields', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'temp site')
        ->set('cluster', 'Cluster 1')
        ->set('location', 'Manila')
        ->call('closeModal')
        ->assertSet('sp_name', '')
        ->assertSet('editingId', null)
        ->assertSet('showModal', false);
});

test('createSiteVisit aborts for user without create permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->assertForbidden();
});

test('editSiteVisit aborts for user without edit permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $siteVisit = SiteVisit::factory()->create();

    Livewire::test(SiteVisits::class)
        ->call('editSiteVisit', $siteVisit->id)
        ->assertForbidden();
});

test('confirmDelete aborts for user without delete permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $siteVisit = SiteVisit::factory()->create();

    Livewire::test(SiteVisits::class)
        ->call('confirmDelete', $siteVisit->id)
        ->assertForbidden();
});

test('site visits page denied for user without view permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $this->get(route('site-visits.index'))
        ->assertForbidden();
});

test('edit modal pre-fills existing site visit data', function () {
    $siteVisit = SiteVisit::factory()->create([
        'sp_name' => 'Test Site',
        'cluster' => 'north_luzon',
        'component' => 'i_build',
        'location' => 'Davao City',
        'funding_source' => 'scale_up',
        'document_link' => 'https://example.com/doc.pdf',
        'virtual_site_visit_link' => 'https://zoom.us/j/123',
    ]);

    Livewire::test(SiteVisits::class)
        ->call('editSiteVisit', $siteVisit->id)
        ->assertSet('sp_name', 'Test Site')
        ->assertSet('cluster', 'north_luzon')
        ->assertSet('component', 'i_build')
        ->assertSet('location', 'Davao City')
        ->assertSet('funding_source', 'scale_up')
        ->assertSet('document_link', 'https://example.com/doc.pdf')
        ->assertSet('virtual_site_visit_link', 'https://zoom.us/j/123');
});

test('multiple images can be uploaded and stored', function () {
    Storage::fake('public');

    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Site With Images')
        ->set('images', [
            UploadedFile::fake()->image('image1.png'),
            UploadedFile::fake()->image('image2.png'),
        ])
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    $siteVisit = SiteVisit::where('sp_name', 'Site With Images')->first();

    $this->assertCount(2, $siteVisit->images);
    foreach ($siteVisit->images as $image) {
        Storage::disk('public')->assertExists($image);
    }
});

test('max 5 images can be uploaded', function () {
    Storage::fake('public');

    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Too Many Images')
        ->set('images', [
            UploadedFile::fake()->image('img1.png'),
            UploadedFile::fake()->image('img2.png'),
            UploadedFile::fake()->image('img3.png'),
            UploadedFile::fake()->image('img4.png'),
            UploadedFile::fake()->image('img5.png'),
            UploadedFile::fake()->image('img6.png'),
        ])
        ->call('saveSiteVisit')
        ->assertHasErrors('images');
});

test('existing images can be removed on edit', function () {
    Storage::fake('public');

    $storedImage1 = 'site-visits/img1.png';
    $storedImage2 = 'site-visits/img2.png';
    Storage::disk('public')->put($storedImage1, 'fake-content');
    Storage::disk('public')->put($storedImage2, 'fake-content');

    $siteVisit = SiteVisit::factory()->create([
        'sp_name' => 'With Images',
        'images' => [$storedImage1, $storedImage2],
    ]);

    Livewire::test(SiteVisits::class)
        ->call('editSiteVisit', $siteVisit->id)
        ->call('removeImage', 0)
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing($storedImage1);
    Storage::disk('public')->assertExists($storedImage2);

    $siteVisit->refresh();
    $this->assertCount(1, $siteVisit->images);
    Storage::disk('public')->assertExists($siteVisit->images[0]);
});

test('images are deleted when site visit is deleted', function () {
    Storage::fake('public');

    $imagePath = 'site-visits/delete-test.png';
    Storage::disk('public')->put($imagePath, 'fake-content');

    $siteVisit = SiteVisit::factory()->create([
        'sp_name' => 'Delete With Images',
        'images' => [$imagePath],
    ]);

    Livewire::test(SiteVisits::class)
        ->call('confirmDelete', $siteVisit->id)
        ->call('deleteSiteVisit');

    Storage::disk('public')->assertMissing($imagePath);
    $this->assertDatabaseMissing('site_visits', ['id' => $siteVisit->id]);
});

test('additional fields are saved correctly', function () {
    Livewire::test(SiteVisits::class)
        ->call('createSiteVisit')
        ->set('sp_name', 'Full Details')
        ->set('cluster', 'north_luzon')
        ->set('component', 'i_build')
        ->set('location', 'Davao City')
        ->set('funding_source', 'scale_up')
        ->set('document_link', 'https://drive.google.com/doc')
        ->set('virtual_site_visit_link', 'https://zoom.us/j/999')
        ->call('saveSiteVisit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('site_visits', [
        'sp_name' => 'Full Details',
        'cluster' => 'north_luzon',
        'component' => 'i_build',
        'location' => 'Davao City',
        'funding_source' => 'scale_up',
        'document_link' => 'https://drive.google.com/doc',
        'virtual_site_visit_link' => 'https://zoom.us/j/999',
    ]);
});
