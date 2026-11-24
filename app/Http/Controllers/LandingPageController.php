<?php

namespace App\Http\Controllers;

use App\Enums\MissionComponentUnit;
use App\Enums\MissionManagementTier;
use App\Enums\MissionOffice;
use App\Enums\MissionPsoCluster;
use App\Enums\MissionRpcoOffice;
use App\Enums\RegistrationSource;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Models\Meeting;
use App\Models\Mission;
use App\Models\MissionPhoto;
use App\Models\MissionRegistration;
use App\Models\SiteVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LandingPageController extends Controller
{
    private const LAZY_SECTIONS = ['agenda', 'meetings', 'site-visits', 'recordings', 'registration', 'gallery', 'reports'];

    public function __invoke(): View
    {
        [$activeMission, $latestMission] = $this->resolveMissions();

        $bannerMission = $activeMission ?? $latestMission;

        $meetings = Meeting::when($bannerMission, fn ($query) => $query->where('mission_id', $bannerMission->id))->get();

        $reportDocuments = $meetings->map(fn (Meeting $meeting) => $meeting->report_documents);

        $reportsCount = $reportDocuments->sum(fn ($documents) => count($documents ?? []));
        $recordingsCount = $meetings->filter(fn (Meeting $meeting) => filled($meeting->recording_link))->count();

        return view('landing', [
            'bannerMission' => $bannerMission,
            'registrationMission' => $this->registrationMission(),
            'meetingsCount' => $reportDocuments->count(),
            'siteVisitsCount' => SiteVisit::when($bannerMission, fn ($query) => $query->where('mission_id', $bannerMission->id))->count(),
            'reportsCount' => $reportsCount,
            'hasReports' => $reportsCount > 0,
            'recordingsCount' => $recordingsCount,
            'title' => $bannerMission ? $bannerMission->title : 'ISM Portal',
        ]);
    }

    /**
     * Render a single landing page section on demand so the initial page stays light.
     */
    public function section(string $section)
    {
        abort_unless(in_array($section, self::LAZY_SECTIONS, true), 404);

        [$activeMission, $latestMission] = $this->resolveMissions();
        $mission = $activeMission ?? $latestMission;

        $data = match ($section) {
            'agenda' => [
                'bannerMission' => $mission,
            ],
            'meetings' => [
                'meetingsMission' => $mission,
                'meetings' => $this->missionMeetings($mission),
            ],
            'site-visits' => [
                'activeMission' => $activeMission,
                'siteVisitsMission' => $mission,
                'siteVisits' => $this->missionSiteVisits($mission),
            ],
            'recordings' => [
                'meetings' => $this->missionMeetings($mission),
            ],
            'registration' => [
                'registrationMission' => $this->registrationMission(),
                'meetings' => $this->missionMeetings($mission),
            ],
            'gallery' => [
                'activeMission' => $activeMission,
                'photosMission' => $mission,
                'photosUploadAllowed' => $this->photosUploadAllowed($activeMission, $latestMission),
            ],
            'reports' => [
                'meetings' => $this->missionMeetings($mission),
            ],
        };

        return response()
            ->view("landing.{$section}", $data)
            ->header('Cache-Control', 'no-store');
    }

    /**
     * JSON dataset consumed by the client-side agenda, meetings, site visit, and gallery views.
     */
    public function data(): JsonResponse
    {
        [$activeMission, $latestMission] = $this->resolveMissions();
        $mission = $activeMission ?? $latestMission;

        $meetings = $this->missionMeetings($mission)->map(fn (Meeting $meeting) => [
            'id' => $meeting->id,
            'title' => $meeting->title,
            'description' => $meeting->description ?? '',
            'date' => $meeting->date?->format('Y-m-d'),
            'time_from' => $meeting->time_from?->format('H:i'),
            'time_to' => $meeting->time_to?->format('H:i'),
            'type' => $meeting->type === 'other' && $meeting->other_type
                ? $meeting->other_type
                : ($meeting->types()[$meeting->type] ?? $meeting->type),
            'type_key' => $meeting->types()[$meeting->type] ?? 'Other',
            'venue' => $meeting->venue ?? '',
            'link' => $meeting->link ?? '',
            'meeting_id' => $meeting->meeting_id ?? '',
            'passcode' => $meeting->passcode ?? '',
            'reports' => $meeting->report_documents ?? [],
            'video_links' => $meeting->video_documents ?? [],
            'recording_link' => $meeting->recording_link ?? '',
            'report_name' => $meeting->report_name ?? '',
            'reports_count' => count($meeting->report_documents ?? []),
        ])->values();

        $siteVisits = $this->missionSiteVisits($mission)->map(fn (SiteVisit $visit) => [
            'id' => $visit->id,
            'sp_name' => $visit->sp_name,
            'cluster' => $visit->cluster?->label() ?? '',
            'component' => $visit->component?->label() ?? '',
            'location' => $visit->location ?? '',
            'funding_source' => $visit->funding_source?->label() ?? '',
            'document_link' => $visit->document_link ?? '#',
            'virtual_site_visit_link' => $visit->virtual_site_visit_link ?? '#',
            'date' => $visit->date?->format('Y-m-d') ?? '',
            'description' => '',
            'images' => collect($visit->images ?? [])->map(fn ($image) => is_array($image) ? [
                'url' => $image['url'] ?? '',
                'station' => $image['station'] ?? '',
                'coords' => $image['coords'] ?? '',
                'elev' => $image['elev'] ?? '',
                'time' => $image['time'] ?? '',
                'label' => $image['label'] ?? '',
            ] : [
                'url' => Storage::disk('public')->url($image),
                'station' => '',
                'coords' => '',
                'elev' => '',
                'time' => '',
                'label' => '',
            ])->values()->all(),
        ])->values();

        $photos = MissionPhoto::when($mission, fn ($query) => $query->where('mission_id', $mission->id))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (MissionPhoto $photo) => [
                'id' => "mp-{$photo->id}",
                'caption' => $photo->caption ?? '',
                'author' => '',
                'office' => '',
                'location' => $photo->location ?? '',
                'dateDisplay' => $photo->created_at?->format('M j, Y') ?? '',
                'timeDisplay' => $photo->created_at?->format('g:i A') ?? '',
                'device' => '',
                'exif' => '',
                'imageUrl' => $photo->image_url,
            ])->values();

        return response()->json([
            'meetings' => $meetings,
            'siteVisits' => $siteVisits,
            'photos' => $photos,
            'canUploadPhotos' => $this->photosUploadAllowed($activeMission, $latestMission),
        ]);
    }

    /**
     * Stream a mission photo from storage, tolerating legacy path formats.
     */
    public function showPhoto(MissionPhoto $missionPhoto): StreamedResponse|BinaryFileResponse
    {
        $path = $this->normalizePhotoPath($missionPhoto->image_path);
        $headers = ['Cache-Control' => 'public, max-age=31536000, immutable'];

        if ($path !== '') {
            foreach (['public', 'local'] as $diskName) {
                $disk = Storage::disk($diskName);

                if ($disk->exists($path)) {
                    return $disk->response($path, null, $headers);
                }
            }

            foreach ([
                public_path($path),
                public_path('storage/'.$path),
                storage_path('app/public/'.$path),
                storage_path('app/private/'.$path),
            ] as $absolutePath) {
                if (is_file($absolutePath)) {
                    return response()->file($absolutePath, $headers);
                }
            }
        }

        Log::warning('Mission photo file was not found.', [
            'mission_photo_id' => $missionPhoto->id,
            'image_path' => $missionPhoto->image_path,
            'normalized_path' => $path,
        ]);

        abort(404);
    }

    /**
     * Normalize a stored photo path into a disk-relative path.
     */
    protected function normalizePhotoPath(?string $path): string
    {
        $path = trim((string) $path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');

        foreach ([
            '#^storage/app/public/#',
            '#^storage/app/private/#',
            '#^storage/#',
            '#^public/#',
        ] as $prefix) {
            $path = (string) preg_replace($prefix, '', $path);
        }

        return $path;
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
     * Mission currently accepting delegate registrations.
     */
    protected function registrationMission(): ?Mission
    {
        return Mission::where('is_active', true)
            ->where('enable_registration', true)
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * @return Collection<int, Meeting>
     */
    protected function missionMeetings(?Mission $mission): Collection
    {
        return Meeting::when($mission, fn ($query) => $query->where('mission_id', $mission->id))
            ->orderBy('date', 'asc')
            ->orderBy('time_from', 'asc')
            ->get();
    }

    /**
     * @return Collection<int, SiteVisit>
     */
    protected function missionSiteVisits(?Mission $mission): Collection
    {
        return SiteVisit::when($mission, fn ($query) => $query->where('mission_id', $mission->id))
            ->orderBy('created_at', 'desc')
            ->get();
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
            'attendance_dates' => ['required', 'array', 'min:1'],
            'attendance_dates.*' => ['required', 'date'],
            'attendance_mode' => ['required', 'array'],
            'attendance_mode.*' => ['required', 'string', 'in:onsite,online'],
        ])->validate();

        $registration = MissionRegistration::create(array_merge($validated, [
            'mission_id' => $activeMission->id,
            'source' => RegistrationSource::Landing->value,
        ]));

        $suffix = $validated['suffix'] ?? null;
        $suffixLabel = $suffix ? (Suffix::labels()[$suffix] ?? $suffix) : '';
        $middleInitial = ! empty($validated['middle_initial']) ? strtoupper($validated['middle_initial']).'.' : '';
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
