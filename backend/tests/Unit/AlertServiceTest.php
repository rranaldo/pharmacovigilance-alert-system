<?php

namespace Tests\Unit;

use App\Mail\PharmacovigilanceAlert;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    private AlertService $service;
    private User $user;
    private Customer $customerWithEmail;
    private Customer $customerWithoutEmail;
    private Order $order;
    private Order $orderNoEmail;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->service = app(AlertService::class);

        $this->user = User::create([
            'username' => 'pharmacist',
            'name' => 'Test Pharmacist',
            'email' => 'pharmacist@test.local',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
        ]);

        $this->customerWithEmail = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '555-0001',
        ]);

        $this->customerWithoutEmail = Customer::create([
            'name' => 'No Email Customer',
            'email' => null,
            'phone' => '555-0002',
        ]);

        $med = Medication::create([
            'name' => 'Lisinopril 10mg',
            'lot_number' => '951357',
            'expiration_date' => '2025-06-15',
        ]);

        $this->order = Order::create([
            'customer_id' => $this->customerWithEmail->id,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total' => 30.00,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'medication_id' => $med->id,
            'quantity' => 1,
            'unit_price' => 30.00,
        ]);

        $this->orderNoEmail = Order::create([
            'customer_id' => $this->customerWithoutEmail->id,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total' => 25.00,
        ]);

        OrderItem::create([
            'order_id' => $this->orderNoEmail->id,
            'medication_id' => $med->id,
            'quantity' => 1,
            'unit_price' => 25.00,
        ]);
    }

    public function test_send_alert_creates_record_and_sends_email(): void
    {
        $alert = $this->service->sendAlert(
            $this->customerWithEmail,
            $this->order,
            $this->user,
            '951357',
        );

        $this->assertEquals('sent', $alert->status);
        $this->assertNotNull($alert->sent_at);

        Mail::assertSent(PharmacovigilanceAlert::class, function ($mail) {
            return $mail->hasTo('john@test.com');
        });
    }

    public function test_duplicate_alert_returns_existing_without_new_record(): void
    {
        $first = $this->service->sendAlert(
            $this->customerWithEmail,
            $this->order,
            $this->user,
            '951357',
        );

        $second = $this->service->sendAlert(
            $this->customerWithEmail,
            $this->order,
            $this->user,
            '951357',
        );

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('alerts', 1);

        Mail::assertSent(PharmacovigilanceAlert::class, 1);
    }

    public function test_alert_to_customer_without_email_fails(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service->sendAlert(
            $this->customerWithoutEmail,
            $this->orderNoEmail,
            $this->user,
            '951357',
        );
    }

    public function test_failed_alert_is_marked_as_failed_in_db(): void
    {
        try {
            $this->service->sendAlert(
                $this->customerWithoutEmail,
                $this->orderNoEmail,
                $this->user,
                '951357',
            );
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertDatabaseHas('alerts', [
            'customer_id' => $this->customerWithoutEmail->id,
            'status' => 'failed',
        ]);
    }

    public function test_send_alert_creates_audit_log(): void
    {
        $this->service->sendAlert(
            $this->customerWithEmail,
            $this->order,
            $this->user,
            '951357',
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'alert.sent',
        ]);
    }

    public function test_failed_alert_creates_failure_audit_log(): void
    {
        try {
            $this->service->sendAlert(
                $this->customerWithoutEmail,
                $this->orderNoEmail,
                $this->user,
                '951357',
            );
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'alert.failed',
        ]);
    }

    public function test_bulk_send_mixed_results(): void
    {
        $results = $this->service->sendBulkAlerts(
            [$this->customerWithEmail->id, $this->customerWithoutEmail->id],
            '951357',
            $this->user,
        );

        $this->assertEquals(1, $results['sent']);
        $this->assertEquals(1, $results['failed']);
        $this->assertEquals(0, $results['skipped']);
    }

    public function test_bulk_send_skips_already_alerted(): void
    {
        $this->service->sendAlert(
            $this->customerWithEmail,
            $this->order,
            $this->user,
            '951357',
        );

        $results = $this->service->sendBulkAlerts(
            [$this->customerWithEmail->id],
            '951357',
            $this->user,
        );

        $this->assertEquals(0, $results['sent']);
        $this->assertEquals(1, $results['skipped']);
    }

    public function test_bulk_send_creates_audit_log(): void
    {
        $this->service->sendBulkAlerts(
            [$this->customerWithEmail->id],
            '951357',
            $this->user,
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'alert.bulk_sent',
        ]);
    }

    public function test_bulk_send_with_no_matching_orders_returns_zeros(): void
    {
        $results = $this->service->sendBulkAlerts(
            [$this->customerWithEmail->id],
            '000000',
            $this->user,
        );

        $this->assertEquals(0, $results['sent']);
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(0, $results['skipped']);
    }
}
