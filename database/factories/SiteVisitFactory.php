<?php

namespace Database\Factories;

use App\Models\SiteVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteVisit>
 */
class SiteVisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sp_name' => $this->faker->sentence(3),
            'cluster' => $this->faker->randomElement(['north_luzon', 'south_luzon', 'visayas', 'mindanao']),
            'component' => $this->faker->randomElement(['i_build', 'i_reap']),
            'location' => $this->faker->city(),
            'funding_source' => $this->faker->randomElement(['original_funding', 'additional_funding', 'additional_funding_2', 'scale_up']),
            'document_link' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'virtual_site_visit_link' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'images' => null,
            'date' => $this->faker->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
        ];
    }
}
