<?php

use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\LandingPageController;
use App\Livewire\Administration\Permissions;
use App\Livewire\Administration\Roles;
use App\Livewire\Administration\Users;
use App\Livewire\Meetings\Meetings;
use App\Livewire\MissionRegistrations\MissionRegistrationForm;
use App\Livewire\MissionRegistrations\MissionRegistrations;
use App\Livewire\Missions\Missions;
use App\Livewire\SiteVisits\SiteVisits;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, '__invoke'])->name('home');
Route::get('/landing/data', [LandingPageController::class, 'data'])->name('landing.data');
Route::get('/landing/section/{section}', [LandingPageController::class, 'section'])->name('landing.section');
Route::post('/landing/register', [LandingPageController::class, 'storeRegistration'])->name('landing.register');
Route::post('/landing/photos', [LandingPageController::class, 'storePhoto'])->name('landing.photos');

Route::get('missions/{mission}/register', MissionRegistrationForm::class)
    ->name('missions.register')
    ->middleware('mission.registration.enabled');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('permissions', Permissions::class)->name('permissions.index')->middleware('permission:view permissions');
    Route::livewire('roles', Roles::class)->name('roles.index')->middleware('permission:view roles');
    Route::livewire('users', Users::class)->name('users.index')->middleware('permission:view users');
    Route::livewire('meetings', Meetings::class)->name('meetings.index')->middleware('permission:view meetings');
    Route::livewire('missions', Missions::class)->name('missions.index')->middleware('permission:view missions');
    Route::livewire('mission-registrations', MissionRegistrations::class)->name('mission-registrations.index')->middleware('permission:view mission registrations');
    Route::livewire('site-visits', SiteVisits::class)->name('site-visits.index')->middleware('permission:view site visits');

    Route::post('impersonate/stop', [ImpersonateController::class, 'stop'])
        ->name('impersonate.stop');

    Route::post('impersonate/{user}', [ImpersonateController::class, 'impersonate'])
        ->name('impersonate')
        ->middleware('permission:impersonate users');
});

require __DIR__.'/settings.php';
