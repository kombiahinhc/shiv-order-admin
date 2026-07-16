<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'owner' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'address' => fake()->address(),
            'status' => fake()->randomElement(['pending', 'approved']),
            'requested_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
