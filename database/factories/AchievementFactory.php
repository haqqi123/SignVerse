<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['🔥', '⭐', '🎖️', '🚀']),
            'type' => fake()->randomElement([Achievement::TYPE_STREAK, Achievement::TYPE_XP, Achievement::TYPE_PRACTICE]),
            'threshold' => fake()->randomElement([7, 100, 500]),
        ];
    }
}
