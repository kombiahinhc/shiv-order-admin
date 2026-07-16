<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'sku' => strtoupper(fake()->bothify('SKU-####')),
            'category' => fake()->randomElement(['Beverages', 'Snacks', 'Household', 'Personal Care']),
            'unit' => fake()->randomElement(['pcs', 'box', 'kg', 'pack']),
            'list_price' => fake()->randomFloat(2, 10, 500),
            'tax_rate' => fake()->randomElement([0, 5, 12, 18]),
            'active' => true,
        ];
    }
}
