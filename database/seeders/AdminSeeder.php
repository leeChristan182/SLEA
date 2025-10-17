<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminProfile;
use App\Models\AdminPassword;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin Profile
        $admin = AdminProfile::updateOrCreate(
            ['admin_id' => 'ADMIN001'],
            [
                'email_address' => 'admin@usep.edu.ph',
                'name' => 'System Admin',
                'contact_number' => '09123456789',
                'position' => 'Super Admin',
                'date_upload' => now(),
            ]
        );

        // Create Admin Password (use password_hashed, not password)
        AdminPassword::updateOrCreate(
            ['admin_id' => $admin->admin_id],
            [
                'password_hashed'   => Hash::make('password123'),
                'date_pass_created' => now(),
            ]
        );
    }
}
