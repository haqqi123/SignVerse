<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->word();

        return [
            'material_id' => Material::factory(),
            'title' => ucfirst($title),
            'gesture_label' => strtolower($title),
            'video_path' => null,
            'xp_reward' => fake()->randomElement([10, 15, 20]),
            'difficulty' => fake()->numberBetween(1, 3),
            'order' => fake()->numberBetween(0, 30),
        ];
    }
}
