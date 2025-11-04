<?php

namespace Database\Seeders;

use App\Models\AdminAccount;
use Illuminate\Database\Seeder;
use App\Models\AdminProfile;
use App\Models\AdminPassword;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update admin profile
        $admin = AdminAccount::updateOrCreate(
            ['email_address' => 'admin@usep.edu.ph'], // unique key for updateOrCreate
            [
                'first_name'     => 'System',
                'last_name'      => 'Admin',
                'contact_number' => '09123456789',
                'position'       => 'Super Admin',
                'status'         => 'approved',   // ✅ added status here
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        );

        // Create or update corresponding password
        AdminPassword::updateOrCreate(
            ['admin_id' => $admin->admin_id],
            [
                'password_hashed'   => Hash::make('password123'),
                'date_pass_created' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        // ✅ Optional confirmation message in console
        $this->command->info("✅ Admin account seeded: {$admin->email_address} (status: approved)");
    }
}
