<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersAdminSeeder extends Seeder
{
    public function run(): void
    {
        $seedingConfig = config('seeding');
        $defaultPassword = $seedingConfig['default_password'];
        $adminConfig = $seedingConfig['admin'];
        $assessorConfig = $seedingConfig['assessor'];

        // ===============================
        // ADMIN ACCOUNT
        // ===============================
        User::updateOrCreate(
            ['email' => $adminConfig['email']],
            [
                'user_code'           => $adminConfig['user_code'], // fixed code for main admin
                'first_name'          => $adminConfig['first_name'],
                'last_name'           => $adminConfig['last_name'],
                'middle_name'         => null,
                'contact'             => $adminConfig['contact'],
                'password'            => Hash::make($defaultPassword),
                'role'                => User::ROLE_ADMIN,
                'status'              => User::STATUS_APPROVED,
                'birth_date'          => null,
                'profile_picture_path' => null,
                'otp_last_verified_at' => now(), // Skip OTP for pre-created admin accounts
            ]
        );

        // ===============================
        // DEFAULT ASSESSOR
        // ===============================
        User::updateOrCreate(
            ['email' => $assessorConfig['email']],
            [
                'user_code'           => $assessorConfig['user_code'], // fixed code for default assessor
                'first_name'          => $assessorConfig['first_name'],
                'last_name'           => $assessorConfig['last_name'],
                'middle_name'         => null,
                'contact'             => $assessorConfig['contact'],
                'password'            => Hash::make($defaultPassword),
                'role'                => User::ROLE_ASSESSOR,
                'status'              => User::STATUS_APPROVED,
                'birth_date'          => null,
                'profile_picture_path' => null,
                'otp_last_verified_at' => now(), // Skip OTP for pre-created assessor accounts
            ]
        );
    }
}
