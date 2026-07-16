<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_catalog(): void
    {
        $rep = User::factory()->create([
            'email' => 'rep@example.com',
            'password' => bcrypt('secret123'),
            'role' => User::ROLE_SALESREP,
        ]);
        Product::factory()->count(3)->create(['active' => true]);
        Product::factory()->create(['active' => false]);

        $response = $this->postJson('/api/login', [
            'email' => 'rep@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);
        $token = $response->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(3, 'products');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/shops')
            ->assertOk();
    }

    public function test_shop_request_requires_approval(): void
    {
        $rep = User::factory()->create(['role' => User::ROLE_SALESREP]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($rep, 'sanctum')
            ->postJson('/api/shops', ['name' => 'New Shop', 'owner' => 'Bob'])
            ->assertCreated()
            ->assertJsonPath('shop.status', 'pending');

        $this->assertDatabaseHas('shops', ['name' => 'New Shop', 'status' => 'pending']);

        // Rep cannot see the pending shop as approved
        $this->actingAs($rep, 'sanctum')
            ->getJson('/api/shops')
            ->assertOk()
            ->assertJsonCount(0, 'shops');

        // Admin approves
        $shop = Shop::where('name', 'New Shop')->first();
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/shops/{$shop->id}/approve")
            ->assertOk()
            ->assertJsonPath('shop.status', 'approved');

        $this->assertDatabaseHas('shops', ['name' => 'New Shop', 'status' => 'approved']);

        // Now rep can see it
        $this->actingAs($rep, 'sanctum')
            ->getJson('/api/shops')
            ->assertOk()
            ->assertJsonCount(1, 'shops');
    }

    public function test_order_sync_recalculates_totals(): void
    {
        $rep = User::factory()->create(['role' => User::ROLE_SALESREP]);
        $product = Product::factory()->create(['list_price' => 100, 'tax_rate' => 10]);

        $this->actingAs($rep, 'sanctum')
            ->postJson('/api/orders', [
                'orders' => [[
                    'local_uuid' => 'uuid-1',
                    'order_date' => '2026-07-14',
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'lines' => [[
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'qty' => 2,
                        'unit_price' => 100,
                        'discount' => 0,
                        'tax_rate' => 10,
                    ]],
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('synced.0.local_uuid', 'uuid-1');

        $order = Order::where('local_uuid', 'uuid-1')->first();
        $this->assertEquals(200.0, (float) $order->subtotal);
        $this->assertEquals(20.0, (float) $order->tax_total);
        $this->assertEquals(220.0, (float) $order->grand_total);
    }

    public function test_order_discount_applied(): void
    {
        $rep = User::factory()->create(['role' => User::ROLE_SALESREP]);
        $product = Product::factory()->create(['list_price' => 100, 'tax_rate' => 10]);

        $this->actingAs($rep, 'sanctum')
            ->postJson('/api/orders', [
                'orders' => [[
                    'local_uuid' => 'uuid-2',
                    'order_date' => '2026-07-14',
                    'discount_type' => 'percent',
                    'discount_value' => 10,
                    'lines' => [[
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'qty' => 2,
                        'unit_price' => 100,
                        'discount' => 0,
                        'tax_rate' => 10,
                    ]],
                ]],
            ])
            ->assertCreated();

        $order = Order::where('local_uuid', 'uuid-2')->first();
        // subtotal 200, discount 10% => 180, tax 10% of 180 = 18, total 198
        $this->assertEquals(200.0, (float) $order->subtotal);
        $this->assertEquals(18.0, (float) $order->tax_total);
        $this->assertEquals(198.0, (float) $order->grand_total);
    }
}
