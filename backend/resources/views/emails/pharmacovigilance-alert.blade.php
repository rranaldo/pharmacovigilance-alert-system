<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .warning-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .details { background: #f9fafb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .footer { padding: 20px; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠ Pharmacovigilance Alert</h1>
    </div>

    <div class="content">
        <p>Dear {{ $customer->name }},</p>

        <div class="warning-box">
            <strong>Important Safety Notice:</strong>
            <p>We are contacting you regarding a medication from <strong>Lot #{{ $lotNumber }}</strong> that has been flagged for a potential safety concern.</p>
        </div>

        @if($customMessage)
            <p>{{ $customMessage }}</p>
        @endif

        <div class="details">
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> #{{ $order->id }}</p>
            <p><strong>Purchase Date:</strong> {{ $order->purchase_date->format('F j, Y') }}</p>

            @if($order->items && $order->items->count())
                <p><strong>Affected Medication(s):</strong></p>
                <ul>
                    @foreach($order->items as $item)
                        @if($item->medication && $item->medication->lot_number === $lotNumber)
                            <li>{{ $item->medication->name }} (Qty: {{ $item->quantity }})</li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>

        <h3>Recommended Actions</h3>
        <ol>
            <li><strong>Stop using</strong> the affected medication immediately.</li>
            <li><strong>Contact us</strong> for a replacement or refund.</li>
            <li>If you experience any adverse effects, <strong>seek medical attention</strong> and report to your healthcare provider.</li>
        </ol>

        <p>We sincerely apologize for any inconvenience and are committed to your safety.</p>
    </div>

    <div class="footer">
        <p>This is an automated pharmacovigilance notification. Please do not reply directly to this email.</p>
        <p>If you have questions, contact our pharmacy at the number on your prescription label.</p>
    </div>
</body>
</html>
