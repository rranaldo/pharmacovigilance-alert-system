<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'username' => 'pharmacist',
            'name' => 'Test Pharmacist',
            'email' => 'pharmacist@test.local',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
        ]);
    }

    private function seedOrders(): array
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@test.com',
            'phone' => '555-0002',
        ]);

        $med = Medication::create([
            'name' => 'Lisinopril 10mg',
            'lot_number' => '951357',
            'expiration_date' => '2025-06-15',
        ]);

        $recentOrder = Order::create([
            'customer_id' => $customer->id,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total' => 35.99,
        ]);

        OrderItem::create([
            'order_id' => $recentOrder->id,
            'medication_id' => $med->id,
            'quantity' => 2,
            'unit_price' => 17.99,
        ]);

        $oldOrder = Order::create([
            'customer_id' => $customer->id,
            'purchase_date' => now()->subDays(60)->toDateString(),
            'total' => 25.00,
        ]);

        OrderItem::create([
            'order_id' => $oldOrder->id,
            'medication_id' => $med->id,
            'quantity' => 1,
            'unit_price' => 25.00,
        ]);

        return [$recentOrder, $oldOrder, $customer, $med];
    }

    public function test_list_orders_by_lot_number(): void
    {
        $this->seedOrders();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders?lot=951357');

        $response->assertOk()
            ->assertJsonPath('total', 1);
    }

    public function test_list_orders_with_custom_date_range(): void
    {
        $this->seedOrders();

        $start = now()->subDays(90)->toDateString();
        $end = now()->toDateString();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders?lot=951357&start_date={$start}&end_date={$end}");

        $response->assertOk()
            ->assertJsonPath('total', 2);
    }

    public function test_list_orders_includes_customer_and_items(): void
    {
        $this->seedOrders();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders?lot=951357');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'customer_id',
                        'purchase_date',
                        'customer' => ['id', 'name', 'email'],
                        'items',
                        'alerts_count',
                    ],
                ],
            ]);
    }

    public function test_show_order_detail(): void
    {
        [$recentOrder] = $this->seedOrders();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$recentOrder->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_id',
                    'purchase_date',
                    'customer' => ['id', 'name', 'email'],
                    'items',
                ],
            ]);
    }

    public function test_show_nonexistent_order_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    public function test_orders_require_lot_parameter(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders');

        $response->assertStatus(422);
    }

    public function test_export_csv(): void
    {
        $this->seedOrders();

        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/orders/export?lot=951357');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $lines = explode("\n", trim($content));

        $this->assertStringContainsString('Order ID', $lines[0]);
        $this->assertStringContainsString('Customer Name', $lines[0]);
        $this->assertGreaterThanOrEqual(2, count($lines));
    }

    public function test_export_csv_empty_results(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/orders/export?lot=000000');

        $response->assertOk();

        $content = $response->streamedContent();
        $lines = array_filter(explode("\n", trim($content)));

        $this->assertCount(1, $lines);
    }

    public function test_orders_support_pagination(): void
    {
        $this->seedOrders();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders?lot=951357&per_page=5');

        $response->assertOk()
            ->assertJsonStructure(['current_page', 'last_page', 'per_page', 'total', 'data']);
    }
}
