<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\Mission;
use App\Models\MissionPhoto;
use App\Models\SiteVisit;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        Meeting::query()->delete();
        SiteVisit::query()->delete();
        Mission::query()->delete();

        $mission = Mission::create([
            'title' => 'Midterm Review and Fifth World Bank-Implementation Support Mission',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-23',
            'banner' => null,
            'agenda_link' => 'https://docs.google.com/spreadsheets/d/1snP9KLGu_bRl_Tp1dnQMem0-Mj0FJKDJrcCF0h3CLrI/edit?gid=1122830704#gid=1122830704',
            'virtual_site_visit_link' => null,
            'enable_registration' => false,
            'is_active' => true,
        ]);

        MissionPhoto::whereNull('mission_id')->update(['mission_id' => $mission->id]);

        Meeting::create([
            'mission_id' => $mission->id,
            'title' => 'Kick-off Meeting',
            'description' => '',
            'date' => '2026-04-07',
            'time_from' => '09:00:00',
            'time_to' => '12:00:00',
            'type' => 'kick_off',
            'other_type' => null,
            'venue' => 'FOS Conference Room, 4th Floor, DA OSEC Building',
            'link' => 'https://zoom.us/j/93887798986?pwd=hx3YbxQnHQLWJwINfKExMMK3YFRWNM.1',
            'meeting_id' => '938 8779 8986',
            'passcode' => '159304',
            'report_name' => null,
            'reports' => [
                [
                    'title' => 'Kick Off Presentation',
                    'description' => null,
                    'url' => 'https://drive.google.com/file/d/1mjJlcnjnFnRwATLpJ149a6ngqUqGY_Q3/view',
                ],
                [
                    'title' => 'PRDP Scale Up - 5th WB ISM Report',
                    'description' => null,
                    'url' => 'https://drive.google.com/file/d/15l7jpUxcwa1XKjkKCD_G3oytEREa4MPD/view',
                ],
            ],
            'image' => null,
        ]);

        $siteVisits = [
            [
                'sp_name' => 'Establishment of Common Service Facilities and Marketing Enterprise for Aromatic Rice',
                'cluster' => 'north_luzon',
                'component' => 'i_reap',
                'location' => 'Brgy. Sta. Barbara, Llanera, Nueva Ecija',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/7c035ef9-d84f-431e-a89e-0ecc8d504a88',
                'date' => '2026-04-13',
            ],
            [
                'sp_name' => 'Construction of Pangasinan Bangus Breeding and Hatchery',
                'cluster' => 'north_luzon',
                'component' => 'i_build',
                'location' => 'Bolinao Provincial Mangrove Information Center and Nursery, Brgy Arnedo, Bolinao,Pangasinan',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/92c5a896-cf94-486b-9edb-7fdb62a07ecd',
                'date' => '2026-04-13',
            ],
            [
                'sp_name' => 'Rehabilitation/Upgrading of Cumadcad to Canjela Farm-to-Market Road',
                'cluster' => 'south_luzon',
                'component' => 'i_build',
                'location' => 'Municipality of Castilla, Sorsogon',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/04c08896-7558-489d-925b-5a382e0f1218',
                'date' => '2026-04-15',
            ],
            [
                'sp_name' => 'Pilinio Taro Production and Marketing Enterprise',
                'cluster' => 'south_luzon',
                'component' => 'i_reap',
                'location' => 'Brgy. Cadlan, Pili, Camarines Sur',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/86def755-151f-4ae4-a72c-da30c860cd91',
                'date' => '2026-04-16',
            ],
            [
                'sp_name' => 'Eastern Visayas Banana Processing and Marketing Enterprise',
                'cluster' => 'visayas',
                'component' => 'i_reap',
                'location' => 'Brgy. Tinghub, Villaba, Leyte',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/7de59252-646e-41f3-b118-c5dcd6414e65',
                'date' => '2026-04-13',
            ],
            [
                'sp_name' => 'Baybay City Barangay Water System Development Project',
                'cluster' => 'visayas',
                'component' => 'i_build',
                'location' => 'Brgy. Bitanhuan, Baybay City, Leyte',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/81a62474-e816-42a1-b8c0-4a0485f2bcd0',
                'date' => '2026-04-13',
            ],
            [
                'sp_name' => 'Mangagoy Farmers Fisherfolks and Community Multi-Purpose Cooperative (MAFFISCO-MPC) Tuna Consolidation and Marketing with Cold Chain Facilities',
                'cluster' => 'mindanao',
                'component' => 'i_reap',
                'location' => 'Sitio Mainit, Brgy. San Isidro, Bislig City, Surigao Del Sur',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/430cabeb-1fcb-4d8f-b230-39453c81aa89#basic-info',
                'date' => '2026-04-16',
            ],
            [
                'sp_name' => 'Concreting of Kinabigtasan - Lindoy FMR with 1-unit concrete Bridge ',
                'cluster' => 'mindanao',
                'component' => 'i_build',
                'location' => 'Municipality of Tago, Surigao del Sur',
                'funding_source' => 'scale_up',
                'document_link' => '',
                'virtual_site_visit_link' => 'https://geomapping.da.gov.ph/prdp/project/profile/53ac877d-bebe-45ed-89f6-79717923a4e2',
                'date' => '2026-04-17',
            ],
        ];

        foreach ($siteVisits as $siteVisit) {
            SiteVisit::create(array_merge($siteVisit, [
                'mission_id' => $mission->id,
                'images' => null,
            ]));
        }
    }
}
