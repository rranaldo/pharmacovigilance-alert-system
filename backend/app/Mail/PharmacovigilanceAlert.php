<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PharmacovigilanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Order $order,
        public string $lotNumber,
        public ?string $customMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Important Safety Notice - Medication Lot #{$this->lotNumber}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pharmacovigilance-alert',
        );
    }
}
