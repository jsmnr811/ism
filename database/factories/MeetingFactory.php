<?php

namespace Database\Factories;

use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['technical_discussions', 'kick_off', 'wrap_up', 'other'];
        $startHour = $this->faker->numberBetween(8, 17);
        $startMinute = $this->faker->randomElement(['00', '15', '30', '45']);
        $endHour = $startHour + $this->faker->numberBetween(1, 3);

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'date' => $this->faker->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
            'time_from' => sprintf('%02d:%s', $startHour, $startMinute),
            'time_to' => sprintf('%02d:%s', $endHour, $startMinute),
            'type' => $type = $this->faker->randomElement($types),
            'other_type' => $type === 'other' ? $this->faker->word() : null,
            'image' => null,
            'venue' => $this->faker->randomElement(['Room A', 'Room B', 'Online', null]),
            'link' => $this->faker->boolean(60) ? $this->faker->url() : null,
            'meeting_id' => $this->faker->boolean(60) ? $this->faker->numerify('#######') : null,
            'passcode' => $this->faker->boolean(60) ? $this->faker->numerify('######') : null,
            'reports' => $this->faker->boolean(50) ? [
                $this->faker->url(),
                $this->faker->url(),
            ] : null,
            'report_name' => $this->faker->boolean(50) ? $this->faker->sentence(3) : null,
        ];
    }
}
