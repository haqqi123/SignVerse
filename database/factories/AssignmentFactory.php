<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'lesson_id' => Lesson::factory(),
            'title' => 'Tugas '.fake()->words(2, true),
            'description' => fake()->sentence(),
            'min_score' => 60,
            'xp_reward' => 30,
            'available_at' => now()->subDay(),
            'due_at' => now()->addDays(7),
        ];
    }
}
