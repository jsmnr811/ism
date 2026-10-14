<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\Mission;
use App\Models\SiteVisit;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $activeMission = Mission::factory()->create([
            'title' => 'Annual Development Planning Session',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'agenda_link' => 'https://example.com/agenda',
            'virtual_site_visit_link' => 'https://example.com/virtual',
            'enable_registration' => true,
            'is_active' => true,
        ]);

        Mission::factory()->create([
            'title' => 'Quarterly Project Review',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'agenda_link' => 'https://example.com/agenda-2',
            'virtual_site_visit_link' => null,
            'enable_registration' => false,
            'is_active' => false,
        ]);

        Meeting::factory()->create([
            'title' => 'Kick-Off Meeting',
            'description' => 'Project kick-off and orientation session.',
            'date' => '2026-09-24',
            'time_from' => '09:00:00',
            'time_to' => '12:00:00',
            'type' => 'kick_off',
            'venue' => 'Conference Room A',
            'mission_id' => $activeMission->id,
        ]);

        Meeting::factory()->create([
            'title' => 'Technical Discussion - I-BUILD',
            'description' => 'Technical discussions on I-BUILD component implementation.',
            'date' => '2026-09-28',
            'time_from' => '10:00:00',
            'time_to' => '16:00:00',
            'type' => 'technical_discussions',
            'venue' => 'Online',
            'link' => 'https://meet.google.com/abc-defg-hij',
            'meeting_id' => '123456789',
            'passcode' => 'secure123',
            'mission_id' => $activeMission->id,
        ]);

        Meeting::factory()->create([
            'title' => 'Wrap-Up Session',
            'description' => 'End-of-project wrap-up and evaluation.',
            'date' => '2026-10-05',
            'time_from' => '13:00:00',
            'time_to' => '17:00:00',
            'type' => 'wrap_up',
            'venue' => 'Room B',
            'reports' => [
                'https://example.com/report-1',
                'https://example.com/report-2',
            ],
            'mission_id' => $activeMission->id,
        ]);

        SiteVisit::factory()->create([
            'sp_name' => 'Irrigation System - Phase 1',
            'cluster' => 'north_luzon',
            'component' => 'i_build',
            'location' => 'Baguio City',
            'funding_source' => 'original_funding',
            'document_link' => 'https://example.com/irrigation-phase1',
            'virtual_site_visit_link' => 'https://example.com/virtual/irrigation',
            'mission_id' => $activeMission->id,
        ]);

        SiteVisit::factory()->create([
            'sp_name' => 'Farm-to-Market Road Project',
            'cluster' => 'visayas',
            'component' => 'i_reap',
            'location' => 'Iloilo City',
            'funding_source' => 'additional_funding',
            'document_link' => null,
            'virtual_site_visit_link' => 'https://example.com/virtual/fmr',
            'mission_id' => $activeMission->id,
        ]);

        SiteVisit::factory()->create([
            'sp_name' => 'Multi-Purpose Cooperative Facility',
            'cluster' => 'mindanao',
            'component' => 'i_build',
            'location' => 'Davao City',
            'funding_source' => 'scale_up',
            'document_link' => 'https://example.com/coop-facility',
            'virtual_site_visit_link' => null,
            'mission_id' => $activeMission->id,
        ]);
    }
}
