<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin account for testing the pharmacovigilance module
        User::create([
            'username' => 'admin',
            'name' => 'System Administrator',
            'email' => 'admin@pharma.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'username' => 'jsmith',
            'name' => 'John Smith',
            'email' => 'jsmith@pharma.local',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
        ]);

        User::create([
            'username' => 'mgarcia',
            'name' => 'Maria Garcia',
            'email' => 'mgarcia@pharma.local',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
        ]);
    }
}
