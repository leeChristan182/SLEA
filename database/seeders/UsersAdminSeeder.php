<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@usep.edu.ph'],
            [
                'first_name' => 'System',
                'last_name'  => 'Admin',
                'middle_name' => null,
                'contact'    => '09123456789',
                'password'   => Hash::make('password123'),
                'role'       => 'admin',
                'status'     => 'approved',
                'birth_date' => null,
                'profile_picture_path' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
