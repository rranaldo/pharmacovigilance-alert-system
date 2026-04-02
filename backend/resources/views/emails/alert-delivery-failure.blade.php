<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: #24364A; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; font-size: 13px; opacity: 0.8; }
        .content { padding: 24px; }
        .alert-box { background: #fef2f2; border: 1px solid #fecaca; padding: 16px; margin: 16px 0; }
        .alert-box strong { color: #b91c1c; }
        .details { background: #f9fafb; border: 1px solid #e5e7eb; padding: 16px; margin: 16px 0; }
        .details table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .details td { padding: 6px 0; vertical-align: top; }
        .details td:first-child { font-weight: 600; width: 140px; color: #374151; }
        .reason { background: #fff7ed; border: 1px solid #fed7aa; padding: 14px; margin: 16px 0; font-family: monospace; font-size: 13px; color: #92400e; word-break: break-all; }
        .footer { padding: 20px; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .badge { display: inline-block; padding: 2px 8px; font-size: 12px; font-weight: 600; background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠ Alert Delivery Failure</h1>
        <p>Pharmacovigilance System – Action Required</p>
    </div>

    <div class="content">
        <p>This is an automated notification. A pharmacovigilance alert <strong>could not be delivered</strong> to the customer listed below. Please follow up manually.</p>

        <div class="alert-box">
            <strong>Delivery failed for Lot #{{ $lotNumber }}</strong><br>
            The system attempted to notify the customer but the email was not delivered.
        </div>

        <div class="details">
            <h3 style="margin: 0 0 12px; font-size: 14px; color: #111827;">Customer & Order Details</h3>
            <table>
                <tr>
                    <td>Customer:</td>
                    <td>{{ $customer->name }} (ID: #{{ $customer->id }})</td>
                </tr>
                <tr>
                    <td>Customer Email:</td>
                    <td>{{ $customer->email ?: 'No email address on file' }}</td>
                </tr>
                <tr>
                    <td>Customer Phone:</td>
                    <td>{{ $customer->phone ?: 'No phone on file' }}</td>
                </tr>
                <tr>
                    <td>Order ID:</td>
                    <td>#{{ $order->id }}</td>
                </tr>
                <tr>
                    <td>Purchase Date:</td>
                    <td>{{ $order->purchase_date->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td>Lot Number:</td>
                    <td><strong>#{{ $lotNumber }}</strong></td>
                </tr>
            </table>
        </div>

        <p><strong>Failure reason:</strong></p>
        <div class="reason">{{ $failureReason }}</div>

        <h3 style="font-size: 14px;">Recommended Actions</h3>
        <ol style="font-size: 14px;">
            <li>Contact the customer <strong>by phone</strong> at {{ $customer->phone ?: 'N/A' }}.</li>
            <li>Update the customer email address in the system and re-send the alert.</li>
            <li>Document this manual notification in the alert audit log.</li>
        </ol>
    </div>

    <div class="footer">
        <p>This message was sent automatically by the Pharmacovigilance System because an alert delivery failed.<br>
        Do not reply to this email.</p>
    </div>
</body>
</html>
