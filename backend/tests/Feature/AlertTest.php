<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlertTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->user = User::create([
            'username' => 'pharmacist',
            'name' => 'Test Pharmacist',
            'email' => 'pharmacist@test.local',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
        ]);

        $this->customer = Customer::create([
            'name' => 'Alert Test Customer',
            'email' => 'alert-test@email.com',
            'phone' => '555-0099',
        ]);

        $med = Medication::create([
            'name' => 'Lisinopril 10mg',
            'lot_number' => '951357',
            'expiration_date' => '2025-06-15',
        ]);

        $this->order = Order::create([
            'customer_id' => $this->customer->id,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total' => 30.00,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'medication_id' => $med->id,
            'quantity' => 1,
            'unit_price' => 30.00,
        ]);
    }

    public function test_send_individual_alert(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/alerts/send', [
                'customer_id' => $this->customer->id,
                'order_id' => $this->order->id,
                'lot_number' => '951357',
                'message' => 'Please stop using this medication.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('alerts', [
            'customer_id' => $this->customer->id,
            'order_id' => $this->order->id,
            'lot_number' => '951357',
            'status' => 'sent',
        ]);
    }

    public function test_duplicate_alert_is_not_created(): void
    {
        Alert::create([
            'customer_id' => $this->customer->id,
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'method' => 'email',
            'status' => 'sent',
            'lot_number' => '951357',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/alerts/send', [
                'customer_id' => $this->customer->id,
                'order_id' => $this->order->id,
                'lot_number' => '951357',
            ]);

        $response->assertOk();
        $this->assertDatabaseCount('alerts', 1);
    }

    public function test_send_bulk_alerts(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/alerts/send-bulk', [
                'customer_ids' => [$this->customer->id],
                'lot_number' => '951357',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.sent', 1);
    }

    public function test_bulk_alerts_skip_already_sent(): void
    {
        Alert::create([
            'customer_id' => $this->customer->id,
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'method' => 'email',
            'status' => 'sent',
            'lot_number' => '951357',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/alerts/send-bulk', [
                'customer_ids' => [$this->customer->id],
                'lot_number' => '951357',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.skipped', 1)
            ->assertJsonPath('data.sent', 0);
    }

    public function test_alert_history_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/alerts');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_alert_history_filters_by_lot(): void
    {
        Alert::create([
            'customer_id' => $this->customer->id,
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'method' => 'email',
            'status' => 'sent',
            'lot_number' => '951357',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/alerts?lot_number=951357');

        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/alerts?lot_number=000000');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_send_alert_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/alerts/send', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'order_id', 'lot_number']);
    }

    public function test_send_alert_validates_existing_customer(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/alerts/send', [
                'customer_id' => 9999,
                'order_id' => $this->order->id,
                'lot_number' => '951357',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }
}
