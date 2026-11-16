<?php

namespace Database\Factories;

use App\Enums\RegistrationSource;
use App\Enums\Suffix;
use App\Models\Mission;
use App\Models\MissionRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionRegistration>
 */
class MissionRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'first_name' => fake()->firstName(),
            'middle_initial' => strtoupper(fake()->randomLetter()),
            'suffix' => fake()->randomElement(Suffix::cases()),
            'last_name' => fake()->lastName(),
            'office' => fake()->randomElement([
                'wb', 'eu', 'lgu', 'da_management', 'oversight_agency',
                'npab', 'rpab_contractor', 'propenet_group', 'prdp', 'guest',
            ]),
            'management_tier' => fake()->randomElement(['npco', 'pso', 'rpco']),
            'pso_cluster' => fake()->randomElement(['north_luzon', 'south_luzon', 'visayas', 'mindanao']),
            'rpco_office' => fake()->randomElement(['rpco_01', 'car', 'caraga', 'barmm', 'mimaropa']),
            'position' => fake()->jobTitle(),
            'component_unit' => fake()->randomElement([
                'office_of_national_project_director', 'admin_unit', 'procurement_unit',
                'ses_unit', 'finance_unit', 'gef_nrm_unit',
            ]),
            'contact_number' => fake()->phoneNumber(),
            'sex' => fake()->randomElement(['male', 'female', 'other']),
            'email' => fake()->email(),
            'source' => RegistrationSource::Landing->value,
            'registered_by' => null,
        ];
    }
}
