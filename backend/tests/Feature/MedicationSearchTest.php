<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationSearchTest extends TestCase
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

    private function seedTestData(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '555-0001',
        ]);

        $flaggedMed = Medication::create([
            'name' => 'Lisinopril 10mg',
            'lot_number' => '951357',
            'expiration_date' => '2025-06-15',
        ]);

        $normalMed = Medication::create([
            'name' => 'Metformin 500mg',
            'lot_number' => '999999',
            'expiration_date' => '2025-09-20',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total' => 45.99,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'medication_id' => $flaggedMed->id,
            'quantity' => 1,
            'unit_price' => 25.99,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'medication_id' => $normalMed->id,
            'quantity' => 1,
            'unit_price' => 20.00,
        ]);
    }

    public function test_search_returns_medications_by_lot(): void
    {
        $this->seedTestData();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/medications/search?lot=951357');

        $response->assertOk()
            ->assertJsonPath('data.filters.lot', '951357')
            ->assertJsonCount(1, 'data.medications');
    }

    public function test_search_requires_lot_number(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/medications/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lot']);
    }

    public function test_search_returns_empty_for_unknown_lot(): void
    {
        $this->seedTestData();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/medications/search?lot=000000');

        $response->assertOk()
            ->assertJsonCount(0, 'data.medications');
    }

    public function test_search_validates_date_range(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/medications/search?lot=951357&start_date=2025-03-01&end_date=2025-01-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }
}
