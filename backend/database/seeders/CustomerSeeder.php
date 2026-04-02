<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Robert Johnson', 'email' => 'robert.johnson@email.com', 'phone' => '555-0101'],
            ['name' => 'Emily Davis', 'email' => 'emily.davis@email.com', 'phone' => '555-0102'],
            ['name' => 'Michael Brown', 'email' => 'michael.brown@email.com', 'phone' => '555-0103'],
            ['name' => 'Sarah Wilson', 'email' => 'sarah.wilson@email.com', 'phone' => '555-0104'],
            ['name' => 'David Martinez', 'email' => 'david.martinez@email.com', 'phone' => '555-0105'],
            ['name' => 'Jennifer Taylor', 'email' => 'jennifer.taylor@email.com', 'phone' => '555-0106'],
            ['name' => 'James Anderson', 'email' => 'james.anderson@email.com', 'phone' => '555-0107'],
            ['name' => 'Lisa Thomas', 'email' => 'lisa.thomas@email.com', 'phone' => '555-0108'],
            ['name' => 'Christopher Lee', 'email' => 'christopher.lee@email.com', 'phone' => '555-0109'],
            ['name' => 'Amanda White', 'email' => 'amanda.white@email.com', 'phone' => '555-0110'],
            ['name' => 'Daniel Harris', 'email' => 'daniel.harris@email.com', 'phone' => '555-0111'],
            ['name' => 'Jessica Clark', 'email' => 'jessica.clark@email.com', 'phone' => null],
            ['name' => 'Matthew Lewis', 'email' => null, 'phone' => '555-0113'],
            ['name' => 'Ashley Robinson', 'email' => 'ashley.robinson@email.com', 'phone' => '555-0114'],
            ['name' => 'Andrew Walker', 'email' => 'andrew.walker@email.com', 'phone' => '555-0115'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
