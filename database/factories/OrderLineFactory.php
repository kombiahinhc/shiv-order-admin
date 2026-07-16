<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OrderLine>
 */
class OrderLineFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();
        $qty = fake()->randomFloat(2, 1, 20);
        $price = $product->list_price;
        $discount = 0;
        $lineTotal = round($qty * $price - $discount, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => $qty,
            'unit_price' => $price,
            'discount' => $discount,
            'tax_rate' => $product->tax_rate,
            'line_total' => $lineTotal,
        ];
    }
}
