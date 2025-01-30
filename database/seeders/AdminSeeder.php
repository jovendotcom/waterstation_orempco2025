<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [   
                'emp_id' => 'Admin',
                'full_name' => 'Admin',
                'user_status' => 'Active',
                'username' => 'admin',
                'password' => Hash::make('@dmin_123'),
                'user_role' => 'admin', // Ensure your User model has a 'role' column or similar.
            ]
        );
    }
}
