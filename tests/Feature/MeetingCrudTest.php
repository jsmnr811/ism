<?php

use App\Livewire\Meetings\Meetings;
use App\Models\Meeting;
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

test('meetings page requires authentication', function () {
    auth()->logout();

    $this->get(route('meetings.index'))
        ->assertRedirect(route('login'));
});

test('meetings page can be rendered by authenticated user', function () {
    $this->get(route('meetings.index'))
        ->assertOk();
});

test('meetings page displays existing meetings', function () {
    Meeting::factory()->create(['title' => 'Team Standup', 'type' => 'technical_discussions']);
    Meeting::factory()->create(['title' => 'Client Demo', 'type' => 'kick_off']);

    Livewire::test(Meetings::class)
        ->assertSee('Team Standup')
        ->assertSee('Client Demo');
});

test('user can create a new meeting', function () {
    Storage::fake('public');

    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Planning Session')
        ->set('description', 'Quarterly planning discussion')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:30')
        ->set('type', 'technical_discussions')
        ->set('venue', 'Conference Room A')
        ->set('link', 'https://zoom.us/j/123456789')
        ->set('meeting_id', '123456789')
        ->set('passcode', 'SecretPass')
        ->set('recording_link', 'https://youtu.be/recording123')
        ->call('saveMeeting')
        ->assertHasNoErrors();

    $meeting = Meeting::where('title', 'Planning Session')->first();

    $this->assertNotNull($meeting);
    $this->assertSame('Quarterly planning discussion', $meeting->description);
    $this->assertSame('2026-10-15', $meeting->date->format('Y-m-d'));
    $this->assertSame('09:00', $meeting->time_from->format('H:i'));
    $this->assertSame('10:30', $meeting->time_to->format('H:i'));
    $this->assertSame('technical_discussions', $meeting->type);
    $this->assertSame('Conference Room A', $meeting->venue);
    $this->assertSame('https://zoom.us/j/123456789', $meeting->link);
    $this->assertSame('123456789', $meeting->meeting_id);
    $this->assertSame('SecretPass', $meeting->passcode);
    $this->assertSame('https://youtu.be/recording123', $meeting->recording_link);
});

test('user can edit an existing meeting', function () {
    $meeting = Meeting::factory()->create(['title' => 'Old Title']);

    Livewire::test(Meetings::class)
        ->call('editMeeting', $meeting->id)
        ->set('title', 'New Title')
        ->call('saveMeeting')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('meetings', [
        'id' => $meeting->id,
        'title' => 'New Title',
    ]);
    $this->assertDatabaseMissing('meetings', [
        'id' => $meeting->id,
        'title' => 'Old Title',
    ]);
});

test('user can create a meeting with structured reports', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Report Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'technical_discussions')
        ->set('reportLinks', [
            ['title' => 'Minutes of the Meeting', 'description' => 'Signed minutes and attendance sheet.', 'url' => 'https://example.com/minutes.pdf'],
            ['title' => '', 'description' => '', 'url' => 'https://example.com/deck.pdf'],
        ])
        ->call('saveMeeting')
        ->assertHasNoErrors();

    $meeting = Meeting::where('title', 'Report Meeting')->first();

    $this->assertNotNull($meeting);
    $this->assertCount(2, $meeting->report_documents);
    $this->assertSame('Minutes of the Meeting', $meeting->report_documents[0]['title']);
    $this->assertSame('Signed minutes and attendance sheet.', $meeting->report_documents[0]['description']);
    $this->assertSame('https://example.com/minutes.pdf', $meeting->report_documents[0]['url']);
    $this->assertNull($meeting->report_documents[1]['title']);
});

test('user can create a meeting with video links', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Video Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'technical_discussions')
        ->set('videoLinks', [
            ['title' => 'Session Recording', 'url' => 'https://youtu.be/session123'],
            ['title' => '', 'url' => 'https://youtu.be/incomplete'],
        ])
        ->call('saveMeeting')
        ->assertHasNoErrors();

    $meeting = Meeting::where('title', 'Video Meeting')->first();

    $this->assertNotNull($meeting);
    $this->assertCount(1, $meeting->video_documents);
    $this->assertSame('Session Recording', $meeting->video_documents[0]['title']);
    $this->assertSame('https://youtu.be/session123', $meeting->video_documents[0]['url']);
});

test('video link url must be a valid url', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Bad Video Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'technical_discussions')
        ->set('videoLinks', [
            ['title' => 'Bad', 'url' => 'not-a-url'],
        ])
        ->call('saveMeeting')
        ->assertHasErrors('videoLinks.0.url');
});

test('reports without a url are discarded', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Empty Report Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'technical_discussions')
        ->set('reportLinks', [
            ['title' => 'No link', 'description' => 'Missing url', 'url' => ''],
        ])
        ->call('saveMeeting')
        ->assertHasNoErrors();

    $meeting = Meeting::where('title', 'Empty Report Meeting')->first();

    $this->assertNotNull($meeting);
    $this->assertNull($meeting->reports);
});

test('report url must be a valid url', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Bad Link Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'technical_discussions')
        ->set('reportLinks', [
            ['title' => 'Bad', 'description' => 'Bad url', 'url' => 'not-a-url'],
        ])
        ->call('saveMeeting')
        ->assertHasErrors('reportLinks.0.url');
});

test('legacy report links are exposed as structured documents', function () {
    $meeting = Meeting::factory()->create([
        'reports' => ['https://example.com/legacy.pdf'],
    ]);

    $documents = $meeting->report_documents;

    $this->assertCount(1, $documents);
    $this->assertSame('https://example.com/legacy.pdf', $documents[0]['url']);
    $this->assertNull($documents[0]['title']);
    $this->assertNull($documents[0]['description']);
});

test('user can delete a meeting through the livewire alert confirmation', function () {
    $meeting = Meeting::factory()->create(['title' => 'Delete Me']);

    Livewire::test(Meetings::class)
        ->call('confirmDelete', $meeting->id)
        ->assertSet('deletingId', $meeting->id)
        ->call('deleteMeeting');

    $this->assertDatabaseMissing('meetings', [
        'id' => $meeting->id,
    ]);
});

test('validation requires meeting title', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', '')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->call('saveMeeting')
        ->assertHasErrors('title');
});

test('validation requires meeting date', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Test Meeting')
        ->set('date', '')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->call('saveMeeting')
        ->assertHasErrors('date');
});

test('validation requires valid time format', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Test Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', 'not-a-time')
        ->set('time_to', '10:00')
        ->call('saveMeeting')
        ->assertHasErrors('time_from');
});

test('validation requires valid type', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Test Meeting')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'invalid-type')
        ->call('saveMeeting')
        ->assertHasErrors('type');
});

test('search filters meetings', function () {
    Meeting::factory()->create(['title' => 'Alpha Meeting']);
    Meeting::factory()->create(['title' => 'Beta Meeting', 'venue' => 'Searchable Venue']);

    Livewire::test(Meetings::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Meeting')
        ->assertDontSee('Beta Meeting');
});

test('search by venue filters meetings', function () {
    Meeting::factory()->create(['title' => 'First Meeting', 'venue' => 'Room A']);
    Meeting::factory()->create(['title' => 'Second Meeting', 'venue' => 'Room B']);

    Livewire::test(Meetings::class)
        ->set('search', 'Room A')
        ->assertSee('First Meeting')
        ->assertDontSee('Second Meeting');
});

test('create modal resets form fields', function () {
    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'temp meeting')
        ->set('description', 'temp description')
        ->set('venue', 'temp venue')
        ->set('link', 'https://example.com')
        ->set('meeting_id', '12345')
        ->set('passcode', 'pass123')
        ->call('closeModal')
        ->assertSet('title', '')
        ->assertSet('editingId', null)
        ->assertSet('showModal', false)
        ->assertSet('existingImage', null);
});

test('createMeeting aborts for user without create permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->assertForbidden();
});

test('editMeeting aborts for user without edit permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();

    Livewire::test(Meetings::class)
        ->call('editMeeting', $meeting->id)
        ->assertForbidden();
});

test('confirmDelete aborts for user without delete permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();

    Livewire::test(Meetings::class)
        ->call('confirmDelete', $meeting->id)
        ->assertForbidden();
});

test('meetings page denied for user without view permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $this->get(route('meetings.index'))
        ->assertForbidden();
});

test('type distribution returns correct counts', function () {
    Meeting::factory()->count(3)->create(['type' => 'technical_discussions']);
    Meeting::factory()->count(2)->create(['type' => 'kick_off']);

    $component = Livewire::test(Meetings::class);

    expect($component->instance()->totalMeetings)->toBe(5);
});

test('statistics cards show correct counts', function () {
    $today = now()->toDateString();

    Meeting::factory()->create(['title' => 'Today Meeting', 'date' => $today]);
    Meeting::factory()->create(['title' => 'Future Meeting', 'date' => now()->addDays(5)->toDateString()]);
    Meeting::factory()->create(['title' => 'Past Meeting', 'date' => now()->subDays(5)->toDateString()]);

    $component = Livewire::test(Meetings::class);

    expect($component->instance()->totalMeetings)->toBe(3);
    expect($component->instance()->totalToday)->toBe(1);
    expect($component->instance()->totalUpcoming)->toBe(2);
    expect($component->instance()->totalPast)->toBe(1);
});

test('edit modal pre-fills existing meeting data', function () {
    $meeting = Meeting::factory()->create([
        'title' => 'Test Meeting',
        'date' => '2026-10-20',
        'time_from' => '14:00:00',
        'time_to' => '15:30:00',
        'type' => 'google_meet',
        'venue' => 'Online',
        'link' => 'https://meet.google.com/test',
        'meeting_id' => 'ABCDEF',
        'passcode' => 'pass123',
        'description' => 'A test meeting',
    ]);

    Livewire::test(Meetings::class)
        ->call('editMeeting', $meeting->id)
        ->assertSet('title', 'Test Meeting')
        ->assertSet('description', 'A test meeting')
        ->assertSet('date', '2026-10-20')
        ->assertSet('time_from', '14:00')
        ->assertSet('time_to', '15:30')
        ->assertSet('type', 'google_meet')
        ->assertSet('venue', 'Online')
        ->assertSet('link', 'https://meet.google.com/test')
        ->assertSet('meeting_id', 'ABCDEF')
        ->assertSet('passcode', 'pass123');
});

test('image upload stores file on public disk', function () {
    Storage::fake('public');

    Livewire::test(Meetings::class)
        ->call('createMeeting')
        ->set('title', 'Meeting with Image')
        ->set('date', '2026-10-15')
        ->set('time_from', '09:00')
        ->set('time_to', '10:00')
        ->set('type', 'technical_discussions')
        ->set('image', UploadedFile::fake()->image('meeting.png'))
        ->call('saveMeeting')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('meetings', [
        'title' => 'Meeting with Image',
    ]);

    $meeting = Meeting::where('title', 'Meeting with Image')->first();
    $this->assertNotNull($meeting->image);
    Storage::disk('public')->assertExists($meeting->image);
});

test('old image is deleted when replaced on edit', function () {
    Storage::fake('public');

    $meeting = Meeting::factory()->create([
        'image' => 'meetings/old-image.png',
    ]);

    Storage::disk('public')->put('meetings/old-image.png', 'fake-image-content');

    Livewire::test(Meetings::class)
        ->call('editMeeting', $meeting->id)
        ->set('image', UploadedFile::fake()->image('new-image.png'))
        ->call('saveMeeting')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing('meetings/old-image.png');
    $meeting->refresh();
    Storage::disk('public')->assertExists($meeting->image);
});

test('image is deleted when meeting is deleted', function () {
    Storage::fake('public');

    $meeting = Meeting::factory()->create([
        'image' => 'meetings/delete-test.png',
    ]);

    Storage::disk('public')->put('meetings/delete-test.png', 'fake-image-content');

    Livewire::test(Meetings::class)
        ->call('confirmDelete', $meeting->id)
        ->call('deleteMeeting');

    Storage::disk('public')->assertMissing('meetings/delete-test.png');
    $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
});
