<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertDeliveryFailure extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Order $order,
        public string $lotNumber,
        public string $failureReason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Alert Delivery Failed – Customer #{$this->customer->id} / Lot #{$this->lotNumber}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alert-delivery-failure',
        );
    }
}
