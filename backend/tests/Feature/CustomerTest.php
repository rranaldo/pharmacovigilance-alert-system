<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    public function test_show_customer_with_orders_and_alerts(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Smith',
            'email' => 'jane@test.com',
            'phone' => '555-1234',
        ]);

        $med = Medication::create([
            'name' => 'Lisinopril 10mg',
            'lot_number' => '951357',
            'expiration_date' => '2025-06-15',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total' => 40.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'medication_id' => $med->id,
            'quantity' => 2,
            'unit_price' => 20.00,
        ]);

        Alert::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'user_id' => $this->user->id,
            'method' => 'email',
            'status' => 'sent',
            'lot_number' => '951357',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/customers/{$customer->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'orders' => [
                        '*' => ['id', 'purchase_date', 'items'],
                    ],
                    'alerts' => [
                        '*' => ['id', 'lot_number', 'status'],
                    ],
                ],
            ])
            ->assertJsonPath('data.name', 'Jane Smith')
            ->assertJsonCount(1, 'data.orders')
            ->assertJsonCount(1, 'data.alerts');
    }

    public function test_show_nonexistent_customer_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/customers/99999');

        $response->assertStatus(404);
    }

    public function test_customer_endpoint_requires_auth(): void
    {
        $customer = Customer::create([
            'name' => 'Test',
            'email' => 'test@test.com',
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(401);
    }
}
