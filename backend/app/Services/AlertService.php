<?php

namespace App\Services;

use App\Mail\AlertDeliveryFailure;
use App\Mail\PharmacovigilanceAlert;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Send a pharmacovigilance alert to a single customer about a specific order.
     * Returns the created Alert model or throws on failure.
     */
    public function sendAlert(
        Customer $customer,
        Order $order,
        User $triggeredBy,
        string $lotNumber,
        ?string $customMessage = null
    ): Alert {
        // Don't send duplicate alerts for the same order+customer combo
        $existing = Alert::where('customer_id', $customer->id)
            ->where('order_id', $order->id)
            ->where('lot_number', $lotNumber)
            ->where('status', 'sent')
            ->first();

        if ($existing) {
            return $existing;
        }

        $alert = Alert::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'user_id' => $triggeredBy->id,
            'method' => 'email',
            'status' => 'pending',
            'lot_number' => $lotNumber,
            'message' => $customMessage,
        ]);

        try {
            $this->dispatchEmail($customer, $order, $lotNumber, $customMessage);
            $alert->markAsSent();

            $this->auditService->log(
                $triggeredBy,
                'alert.sent',
                Alert::class,
                $alert->id,
                [
                    'customer_id' => $customer->id,
                    'lot_number' => $lotNumber,
                    'method' => 'email',
                ]
            );
        } catch (\Exception $e) {
            $alert->markAsFailed();
            Log::error('Failed to send pharmacovigilance alert', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);

            $this->sendFallbackEmail($customer, $order, $lotNumber, $e->getMessage());

            $this->auditService->log(
                $triggeredBy,
                'alert.failed',
                Alert::class,
                $alert->id,
                ['error' => $e->getMessage()]
            );

            throw $e;
        }

        return $alert;
    }

    /**
     * Bulk send alerts to multiple customers. Wraps individual sends
     * so one failure doesn't block the rest of the batch.
     */
    public function sendBulkAlerts(
        array $customerIds,
        string $lotNumber,
        User $triggeredBy,
        ?string $customMessage = null
    ): array {
        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []];

        // Grab all orders for these customers that match the lot
        $orders = Order::withLotNumber($lotNumber)
            ->whereIn('customer_id', $customerIds)
            ->with('customer')
            ->get();

        if ($orders->isEmpty()) {
            return $results;
        }

        foreach ($orders as $order) {
            try {
                $alert = $this->sendAlert(
                    $order->customer,
                    $order,
                    $triggeredBy,
                    $lotNumber,
                    $customMessage
                );

                // sendAlert returns existing alert if already sent
                if ($alert->wasRecentlyCreated) {
                    $results['sent']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->auditService->log(
            $triggeredBy,
            'alert.bulk_sent',
            null,
            null,
            [
                'lot_number' => $lotNumber,
                'total_sent' => $results['sent'],
                'total_failed' => $results['failed'],
                'total_skipped' => $results['skipped'],
            ]
        );

        return $results;
    }

    private function dispatchEmail(
        Customer $customer,
        Order $order,
        string $lotNumber,
        ?string $customMessage
    ): void {
        if (!$customer->email) {
            throw new \RuntimeException("Customer #{$customer->id} has no email address on file.");
        }

        Mail::to($customer->email)->send(
            new PharmacovigilanceAlert($customer, $order, $lotNumber, $customMessage)
        );
    }

    /**
     * Send a failure notification to the configured DEFAULT_ALERT_MAIL address
     * when a customer alert cannot be delivered. Failures here are swallowed and
     * only logged so they never block the main error flow.
     */
    private function sendFallbackEmail(
        Customer $customer,
        Order $order,
        string $lotNumber,
        string $failureReason
    ): void {
        $defaultMail = config('app.default_alert_mail');

        if (!$defaultMail) {
            Log::warning('DEFAULT_ALERT_MAIL is not configured; skipping fallback notification.');
            return;
        }

        try {
            Mail::to($defaultMail)->send(
                new AlertDeliveryFailure($customer, $order, $lotNumber, $failureReason)
            );

            Log::info('Fallback alert failure notification sent', [
                'to' => $defaultMail,
                'customer_id' => $customer->id,
                'lot_number' => $lotNumber,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send fallback alert failure notification', [
                'to' => $defaultMail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
