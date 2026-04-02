<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed order matters — customers and medications must exist before orders.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
            MedicationSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
