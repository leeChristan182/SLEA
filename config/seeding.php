<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seeding Credentials
    |--------------------------------------------------------------------------
    |
    | Default credentials used for database seeding. These should be overridden
    | in your .env file for security. NEVER commit actual passwords to the
    | repository - only commit defaults for development environments.
    |
    */

    'default_password' => env('SEEDER_DEFAULT_PASSWORD', 'password123'),

    'admin' => [
        'email' => 'admin@usep.edu.ph',
        'user_code' => 'ADM-0001',
        'first_name' => 'System',
        'last_name' => 'Admin',
        'contact' => '09123456789',
    ],

    'assessor' => [
        'email' => 'assessor@usep.edu.ph',
        'user_code' => 'ASC-0001',
        'first_name' => 'Default',
        'last_name' => 'Assessor',
        'contact' => '09999999999',
    ],
];
