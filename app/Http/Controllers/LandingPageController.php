<?php

namespace App\Http\Controllers;

use App\Enums\MissionComponentUnit;
use App\Enums\MissionManagementTier;
use App\Enums\MissionOffice;
use App\Enums\MissionPsoCluster;
use App\Enums\MissionRpcoOffice;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Models\Meeting;
use App\Models\Mission;
use App\Models\MissionPhoto;
use App\Models\MissionRegistration;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LandingPageController extends Controller
{
    public function __invoke()
    {
        [$activeMission, $latestMission] = $this->resolveMissions();

        $bannerMission = $activeMission ?? $latestMission;
        $photosMission = $activeMission ?? $latestMission;
        $siteVisitsMission = $activeMission ?? $latestMission;
        $meetingsMission = $activeMission ?? $latestMission;

        $registrationMission = Mission::where('is_active', true)
            ->where('enable_registration', true)
            ->orderBy('start_date', 'desc')
            ->first();

        $missions = Mission::where('enable_registration', true)
            ->orderBy('start_date', 'desc')
            ->get();

        $meetings = Meeting::when($meetingsMission, fn ($query) => $query->where('mission_id', $meetingsMission->id))
            ->orderBy('date', 'asc')
            ->orderBy('time_from', 'asc')
            ->get();

        $siteVisits = SiteVisit::when($siteVisitsMission, fn ($query) => $query->where('mission_id', $siteVisitsMission->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $missionPhotos = MissionPhoto::when($photosMission, fn ($query) => $query->where('mission_id', $photosMission->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $reportsCount = $meetings->sum(fn ($meeting) => count($meeting->report_documents));

        return view('landing', [
            'activeMission' => $activeMission,
            'registrationMission' => $registrationMission,
            'bannerMission' => $bannerMission,
            'photosMission' => $photosMission,
            'photosUploadAllowed' => $this->photosUploadAllowed($activeMission, $latestMission),
            'siteVisitsMission' => $siteVisitsMission,
            'meetingsMission' => $meetingsMission,
            'missions' => $missions,
            'meetings' => $meetings,
            'siteVisits' => $siteVisits,
            'missionPhotos' => $missionPhotos,
            'reportsCount' => $reportsCount,
            'title' => $bannerMission ? $bannerMission->title : 'ISM Portal',
        ]);
    }

    /**
     * Store one or more photos uploaded to the mission photo stream.
     */
    public function storePhoto(Request $request)
    {
        [$activeMission, $latestMission] = $this->resolveMissions();

        if (! $this->photosUploadAllowed($activeMission, $latestMission)) {
            return response()->json([
                'message' => 'Photo uploads are closed. Uploads reopen when a new mission starts or within two weeks after the latest mission ends.',
            ], 403);
        }

        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $mission = $activeMission ?? $latestMission;

        $photos = collect($validated['images'])->map(function ($image) use ($mission, $validated) {
            $path = $image->store('mission-photos', 'public');

            $photo = MissionPhoto::create([
                'mission_id' => $mission?->id,
                'caption' => $validated['caption'] ?? null,
                'location' => $validated['location'] ?? null,
                'image_path' => $path,
            ]);

            return [
                'id' => $photo->id,
                'caption' => $photo->caption ?? '',
                'location' => $photo->location ?? '',
                'imageUrl' => $photo->image_url,
                'dateDisplay' => $photo->created_at?->format('M j, Y') ?? '',
                'timeDisplay' => $photo->created_at?->format('g:i A') ?? '',
                'author' => '',
                'office' => '',
                'device' => '',
                'exif' => '',
            ];
        })->all();

        return response()->json([
            'message' => count($photos) === 1
                ? 'Photo published.'
                : count($photos).' photos published.',
            'photos' => $photos,
        ]);
    }

    /**
     * Resolve the currently active mission and the most recent mission by date.
     *
     * @return array{0: Mission|null, 1: Mission|null}
     */
    protected function resolveMissions(): array
    {
        $now = now()->toDateString();

        $activeMission = Mission::where(function ($query) use ($now) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '<=', $now);
        })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $latestMission = Mission::orderBy('end_date', 'desc')
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return [$activeMission, $latestMission];
    }

    /**
     * Determine whether photo uploads are currently accepted.
     */
    protected function photosUploadAllowed(?Mission $activeMission, ?Mission $latestMission): bool
    {
        if ($activeMission !== null) {
            return true;
        }

        if ($latestMission === null) {
            return false;
        }

        return $latestMission->end_date === null
            || now()->lte($latestMission->end_date->copy()->addWeeks(2));
    }

    public function storeRegistration(Request $request)
    {
        Log::info('Landing page registration submission attempt.', [
            'ip' => $request->ip(),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'office' => $request->input('office'),
        ]);

        $activeMission = Mission::where('is_active', true)
            ->where('enable_registration', true)
            ->first();

        if (! $activeMission) {
            Log::warning('Registration attempted but no active mission with registration enabled.');

            return response()->json(['message' => 'Registration is not currently open.'], 403);
        }

        $validated = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'middle_initial' => ['nullable', 'string', 'max:1'],
            'suffix' => ['nullable', 'string', 'in:'.implode(',', array_keys(Suffix::labels()))],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'office' => ['required', 'in:'.implode(',', array_keys(MissionOffice::labels()))],
            'management_tier' => ['nullable', 'in:'.implode(',', array_keys(MissionManagementTier::labels()))],
            'pso_cluster' => ['nullable', 'required_if:management_tier,'.MissionManagementTier::PSO->value, 'in:'.implode(',', array_keys(MissionPsoCluster::labels()))],
            'rpco_office' => ['nullable', 'required_if:management_tier,'.MissionManagementTier::RPCO->value, 'in:'.implode(',', array_keys(MissionRpcoOffice::labels()))],
            'position' => ['required', 'string', 'min:2', 'max:100'],
            'component_unit' => ['nullable', 'required_if:office,'.MissionOffice::PRDP->value, 'in:'.implode(',', array_keys(MissionComponentUnit::labels()))],
            'contact_number' => ['required', 'string', 'min:7', 'max:20'],
            'sex' => ['required', 'in:'.implode(',', array_keys(Sex::labels()))],
            'email' => ['required', 'string', 'email', 'max:255'],
            'attendance_dates' => ['nullable', 'array'],
            'attendance_dates.*' => ['date'],
            'attendance_mode' => ['nullable', 'array'],
            'attendance_mode.*' => ['string', 'in:onsite,online'],
        ])->validate();

        $registration = MissionRegistration::create(array_merge($validated, [
            'mission_id' => $activeMission->id,
        ]));

        $suffixLabel = $validated['suffix'] ? (Suffix::labels()[$validated['suffix']] ?? $validated['suffix']) : '';
        $middleInitial = $validated['middle_initial'] ? strtoupper($validated['middle_initial']).'.' : '';
        $fullName = trim("{$validated['first_name']} {$middleInitial} {$validated['last_name']}".($suffixLabel ? ' '.$suffixLabel : ''));

        Log::info('Mission registration created.', [
            'registration_id' => $registration->id,
            'mission_id' => $activeMission->id,
            'name' => $fullName,
            'office' => $validated['office'],
        ]);

        Log::info('Returning JSON response for registration.', ['registration_id' => $registration->id]);

        return response()->json([
            'message' => 'Registration submitted successfully.',
            'registration_id' => $registration->id,
            'name' => $fullName,
            'office_label' => MissionOffice::labels()[$validated['office']] ?? $validated['office'],
        ]);
    }
}
