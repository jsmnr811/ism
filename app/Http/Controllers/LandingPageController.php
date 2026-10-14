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
use App\Models\MissionRegistration;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LandingPageController extends Controller
{
    public function __invoke()
    {
        $activeMission = Mission::where('is_active', true)->first();

        $latestMission = Mission::orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $bannerMission = $activeMission ?? $latestMission;

        $missions = Mission::where('enable_registration', true)
            ->orderBy('start_date', 'desc')
            ->get();

        $meetings = Meeting::when($bannerMission, fn ($query) => $query->where('mission_id', $bannerMission->id))
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->orderBy('time_from', 'asc')
            ->get();

        $siteVisits = SiteVisit::when($bannerMission, fn ($query) => $query->where('mission_id', $bannerMission->id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('landing', [
            'activeMission' => $activeMission,
            'bannerMission' => $bannerMission,
            'missions' => $missions,
            'meetings' => $meetings,
            'siteVisits' => $siteVisits,
            'title' => $bannerMission ? $bannerMission->title : 'ISM Portal',
        ]);
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
