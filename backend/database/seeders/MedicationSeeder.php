<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        $medications = [
            // Lot 951357 — this is the flagged lot we're investigating
            ['name' => 'Lisinopril 10mg Compound', 'lot_number' => '951357', 'expiration_date' => '2025-06-15'],
            ['name' => 'Omeprazole 20mg Capsule', 'lot_number' => '951357', 'expiration_date' => '2025-06-15'],

            // Other lots — these should NOT show up in pharmacovigilance searches
            ['name' => 'Metformin 500mg Tablet', 'lot_number' => '842190', 'expiration_date' => '2025-09-20'],
            ['name' => 'Amlodipine 5mg Compound', 'lot_number' => '842190', 'expiration_date' => '2025-09-20'],
            ['name' => 'Atorvastatin 20mg Tablet', 'lot_number' => '763842', 'expiration_date' => '2025-12-01'],
            ['name' => 'Levothyroxine 50mcg Capsule', 'lot_number' => '763842', 'expiration_date' => '2025-12-01'],
            ['name' => 'Gabapentin 300mg Compound', 'lot_number' => '618425', 'expiration_date' => '2025-08-10'],
            ['name' => 'Prednisone 10mg Tablet', 'lot_number' => '618425', 'expiration_date' => '2025-08-10'],
        ];

        foreach ($medications as $med) {
            Medication::create($med);
        }
    }
}
