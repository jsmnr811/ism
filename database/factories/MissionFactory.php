<?php

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mission>
 */
class MissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 weeks', '+2 weeks');
        $endDate = $this->faker->dateTimeBetween($startDate, '+4 weeks');

        return [
            'title' => $this->faker->sentence(3),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'banner' => null,
            'agenda_link' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'virtual_site_visit_link' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'enable_registration' => $this->faker->boolean(40),
            'is_active' => false,
        ];
    }
}
