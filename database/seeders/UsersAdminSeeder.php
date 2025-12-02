<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ===============================
        // ADMIN ACCOUNT
        // ===============================
        User::updateOrCreate(
            ['email' => 'admin@usep.edu.ph'],
            [
                'user_code'           => 'ADM-0001', // fixed code for main admin
                'first_name'          => 'System',
                'last_name'           => 'Admin',
                'middle_name'         => null,
                'contact'             => '09123456789',
                'password'            => Hash::make('password123'),
                'role'                => User::ROLE_ADMIN,
                'status'              => User::STATUS_APPROVED,
                'birth_date'          => null,
                'profile_picture_path' => null,
            ]
        );

        // ===============================
        // DEFAULT ASSESSOR
        // ===============================
        User::updateOrCreate(
            ['email' => 'assessor@usep.edu.ph'],
            [
                'user_code'           => 'ASC-0001', // fixed code for default assessor
                'first_name'          => 'Default',
                'last_name'           => 'Assessor',
                'middle_name'         => null,
                'contact'             => '09999999999',
                'password'            => Hash::make('password123'),
                'role'                => User::ROLE_ASSESSOR,
                'status'              => User::STATUS_APPROVED,
                'birth_date'          => null,
                'profile_picture_path' => null,
            ]
        );
    }
}