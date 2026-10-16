<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'title' => ucwords($title),
            'slug' => str($title)->slug()->value(),
            'language' => fake()->randomElement([Material::LANGUAGE_SIBI, Material::LANGUAGE_BISINDO]),
            'difficulty' => fake()->numberBetween(1, 5),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(0, 20),
        ];
    }
}
