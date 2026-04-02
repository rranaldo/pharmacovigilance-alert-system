<?php

namespace App\Services;

use App\Models\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function exportOrdersCsv(
        string $lotNumber,
        ?string $startDate = null,
        ?string $endDate = null
    ): StreamedResponse {
        $filename = "orders_lot_{$lotNumber}_" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($lotNumber, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Purchase Date',
                'Total',
                'Medications',
            ]);

            Order::withLotNumber($lotNumber)
                ->inDateRange($startDate, $endDate)
                ->with(['customer', 'items.medication'])
                ->cursor()
                ->each(function ($order) use ($handle) {
                    $medications = $order->items
                        ->map(fn($item) => "{$item->medication->name} (x{$item->quantity})")
                        ->implode('; ');

                    fputcsv($handle, [
                        $order->id,
                        $order->customer->name,
                        $order->customer->email ?? 'N/A',
                        $order->customer->phone ?? 'N/A',
                        $order->purchase_date->format('Y-m-d'),
                        number_format($order->total, 2),
                        $medications,
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
