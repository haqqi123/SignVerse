<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'current_streak' => fake()->numberBetween(0, 10),
            'longest_streak' => fake()->numberBetween(0, 20),
            'last_activity_date' => fake()->optional()->date(),
        ];
    }
}
