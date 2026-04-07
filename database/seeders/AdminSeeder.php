<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['phone' => '555555555'],
            [
                'name' => 'Admin',
                'email' => 'admin@mazad.com',
                'password' => bcrypt('123456789A'),
                'account_type' => 'partner', // assuming partner acts as highest or any mapped role
                'status' => 'active'
            ]
        );
    }
}
