<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role' => User::ROLE_MANAGER,
            'password' => bcrypt('password'),
        ]);

        $rep = User::factory()->create([
            'name' => 'Sales Rep',
            'email' => 'rep@example.com',
            'role' => User::ROLE_SALESREP,
            'password' => bcrypt('password'),
        ]);

        $products = Product::factory()->count(20)->create();

        $shops = Shop::factory()->count(8)->create([
            'status' => Shop::STATUS_APPROVED,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // A few pending shop requests
        Shop::factory()->count(3)->create([
            'status' => Shop::STATUS_PENDING,
            'requested_by' => $rep->id,
        ]);

        foreach (range(1, 30) as $i) {
            $shop = $shops->random();
            $order = Order::create([
                'local_uuid' => Str::uuid(),
                'salesperson_id' => $rep->id,
                'shop_id' => $shop->id,
                'order_date' => now()->subDays(rand(0, 60)),
                'notes' => fake()->sentence(),
                'discount_type' => 'none',
                'discount_value' => 0,
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
                'sync_status' => Order::SYNC_SYNCED,
                'synced_at' => now(),
            ]);

            $lines = $products->random(rand(1, 5));
            $subtotal = 0;
            $taxTotal = 0;
            foreach ($lines as $product) {
                $qty = rand(1, 10);
                $price = $product->list_price;
                $lineTotal = round($qty * $price, 2);
                $lineTax = round($lineTotal * ($product->tax_rate / 100), 2);
                $subtotal += $lineTotal;
                $taxTotal += $lineTax;
                $order->lines()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'discount' => 0,
                    'tax_rate' => $product->tax_rate,
                    'line_total' => $lineTotal,
                ]);
            }
            $order->update([
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'grand_total' => round($subtotal + $taxTotal, 2),
            ]);
        }
    }
}
