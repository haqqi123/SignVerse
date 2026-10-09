<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/*
 * Factory User SignTeach — kolom sesuai schema (username/password_hash/role),
 * bukan kolom email/password bawaan Laravel.
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make(static::$password ??= 'password'),
            'role' => 'student',
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'student']);
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'teacher']);
    }
}
