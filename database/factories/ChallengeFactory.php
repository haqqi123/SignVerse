<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    public function definition(): array
    {
        $lesson = Lesson::factory()->create();

        return [
            'lesson_id' => $lesson->id,
            'challenge_date' => fake()->unique()->date(),
            'title' => 'Challenge: '.$lesson->title,
            'description' => fake()->sentence(),
            'xp_reward' => 20,
        ];
    }
}
