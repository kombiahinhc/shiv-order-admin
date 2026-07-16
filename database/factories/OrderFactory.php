<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $tax = round($subtotal * 0.18, 2);

        return [
            'local_uuid' => Str::uuid(),
            'salesperson_id' => User::factory(),
            'shop_id' => Shop::factory(),
            'order_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->sentence(),
            'discount_type' => 'none',
            'discount_value' => 0,
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'grand_total' => round($subtotal + $tax, 2),
            'sync_status' => 'synced',
            'synced_at' => now(),
        ];
    }
}
