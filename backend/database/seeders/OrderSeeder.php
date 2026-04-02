<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // We need the flagged medications (lot 951357) to be part of several
        // recent orders so the pharmacovigilance search returns meaningful results
        $flaggedMeds = Medication::where('lot_number', '951357')->get();
        $otherMeds = Medication::where('lot_number', '!=', '951357')->get();

        // Orders from the last 30 days that include flagged lot medications
        // These are the ones the system should catch
        $recentOrders = [
            ['customer_id' => 1, 'days_ago' => 3,  'flagged' => true],
            ['customer_id' => 2, 'days_ago' => 5,  'flagged' => true],
            ['customer_id' => 3, 'days_ago' => 7,  'flagged' => true],
            ['customer_id' => 4, 'days_ago' => 10, 'flagged' => true],
            ['customer_id' => 5, 'days_ago' => 12, 'flagged' => true],
            ['customer_id' => 6, 'days_ago' => 15, 'flagged' => true],
            ['customer_id' => 7, 'days_ago' => 20, 'flagged' => true],
            ['customer_id' => 8, 'days_ago' => 25, 'flagged' => true],
            // Same customer bought twice — edge case worth testing
            ['customer_id' => 1, 'days_ago' => 18, 'flagged' => true],
        ];

        // Some orders with normal lots to make the data more realistic
        $normalOrders = [
            ['customer_id' => 9,  'days_ago' => 2,  'flagged' => false],
            ['customer_id' => 10, 'days_ago' => 8,  'flagged' => false],
            ['customer_id' => 11, 'days_ago' => 14, 'flagged' => false],
            ['customer_id' => 12, 'days_ago' => 22, 'flagged' => false],
            ['customer_id' => 13, 'days_ago' => 28, 'flagged' => false],
            ['customer_id' => 14, 'days_ago' => 4,  'flagged' => false],
            ['customer_id' => 15, 'days_ago' => 11, 'flagged' => false],
        ];

        // Older orders with flagged lot — should NOT appear in default 30-day search
        $oldOrders = [
            ['customer_id' => 9,  'days_ago' => 45, 'flagged' => true],
            ['customer_id' => 10, 'days_ago' => 60, 'flagged' => true],
        ];

        $allOrders = array_merge($recentOrders, $normalOrders, $oldOrders);

        foreach ($allOrders as $orderData) {
            $purchaseDate = Carbon::now()->subDays($orderData['days_ago'])->toDateString();

            $order = Order::create([
                'customer_id' => $orderData['customer_id'],
                'purchase_date' => $purchaseDate,
                'total' => 0, // will calculate after adding items
            ]);

            $total = 0;

            if ($orderData['flagged']) {
                // Add one or two flagged lot meds
                $med = $flaggedMeds->random();
                $qty = rand(1, 3);
                $price = rand(15, 85) + 0.99;
                OrderItem::create([
                    'order_id' => $order->id,
                    'medication_id' => $med->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                ]);
                $total += $qty * $price;
            }

            // Every order gets at least one normal medication too
            $normalMed = $otherMeds->random();
            $qty = rand(1, 2);
            $price = rand(10, 60) + 0.49;
            OrderItem::create([
                'order_id' => $order->id,
                'medication_id' => $normalMed->id,
                'quantity' => $qty,
                'unit_price' => $price,
            ]);
            $total += $qty * $price;

            $order->update(['total' => round($total, 2)]);
        }
    }
}
