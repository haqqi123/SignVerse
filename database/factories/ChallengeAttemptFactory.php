<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChallengeAttempt>
 */
class ChallengeAttemptFactory extends Factory
{
    public function definition(): array
    {
        $score = fake()->numberBetween(20, 100);

        return [
            'challenge_id' => Challenge::factory(),
            'student_id' => Student::factory(),
            'score' => $score,
            'passed' => $score >= 60,
            'xp_earned' => $score >= 60 ? 20 : 0,
            'attempted_at' => fake()->dateTimeBetween('-7 days'),
        ];
    }
}
