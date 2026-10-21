<?php

namespace Database\Factories;

use App\Models\MissionPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionPhoto>
 */
class MissionPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => null,
            'caption' => $this->faker->boolean(70) ? $this->faker->sentence(4) : null,
            'location' => $this->faker->boolean(70) ? $this->faker->city() : null,
            'image_path' => 'mission-photos/'.$this->faker->uuid().'.jpg',
        ];
    }
}
