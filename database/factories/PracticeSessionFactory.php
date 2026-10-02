<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\PracticeSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeSession>
 */
class PracticeSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'lesson_id' => Lesson::factory(),
            'language' => fake()->randomElement([PracticeSession::LANGUAGE_SIBI, PracticeSession::LANGUAGE_BISINDO]),
            'status' => PracticeSession::STATUS_COMPLETED,
            'score' => fake()->numberBetween(40, 100),
            'xp_earned' => fake()->numberBetween(0, 25),
            'attempts' => fake()->numberBetween(1, 5),
            'started_at' => fake()->dateTimeBetween('-30 days'),
            'completed_at' => now(),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => PracticeSession::STATUS_IN_PROGRESS,
            'score' => null,
            'xp_earned' => 0,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => PracticeSession::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
