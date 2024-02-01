<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'price' => fake()->randomFloat(2, 10, 500),
            'description' => fake()->text(),
            'image' => fake()->imageUrl(),
            'rating' => json_encode([
                'score' => fake()->randomFloat(1, 0, 5),
                'count' => fake()->numberBetween(1, 1_000),
            ]),
        ];
    }
}
