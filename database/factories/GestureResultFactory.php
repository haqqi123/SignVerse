<?php

namespace Database\Factories;

use App\Models\GestureResult;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GestureResult>
 */
class GestureResultFactory extends Factory
{
    public function definition(): array
    {
        $expected = fake()->word();
        $correct = fake()->boolean(60);

        return [
            'lesson_id' => Lesson::factory(),
            'expected_gesture' => $expected,
            'recognized_gesture' => $correct ? $expected : fake()->word(),
            'confidence' => fake()->numberBetween(30, 99),
            'correct' => $correct,
        ];
    }
}
