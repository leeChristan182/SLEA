<?php

return [

    /*
    |--------------------------------------------------------------------------
    | System Limits
    |--------------------------------------------------------------------------
    */

    // Maximum allowed admin accounts (adjustable via .env)
    'max_admin_accounts' => (int) env('SLEA_MAX_ADMINS', 3),


    /*
    |--------------------------------------------------------------------------
    | Email Domain Rules
    |--------------------------------------------------------------------------
    |
    | Acceptable email domain(s) for registration.
    | Supports single or multiple domains, comma-separated in .env.
    |
    | Example:
    |   SLEA_EMAIL_DOMAINS=usep.edu.ph,students.usep.edu.ph
    |
    */

    'email_domains' => array_filter(
        array_map('trim', explode(',', env('SLEA_EMAIL_DOMAINS', 'usep.edu.ph')))
    ),


    /*
    |--------------------------------------------------------------------------
    | Contact Number Validation
    |--------------------------------------------------------------------------
    */

    'phone_regex' => env(
        'SLEA_PHONE_REGEX',
        '/^(\+639|09|9)\d{9}$/'
    ),

    'phone_help' => env(
        'SLEA_PHONE_HELP',
        'Accepted formats: 09XXXXXXXXX, 9XXXXXXXXX, or +639XXXXXXXXX.'
    ),


    /*
    |--------------------------------------------------------------------------
    | Academic Rules
    |--------------------------------------------------------------------------
    */

    // Generic fallback for programs without defined duration
    'default_program_duration' => (int) env('SLEA_DEFAULT_PROGRAM_DURATION', 4),


    /*
    |--------------------------------------------------------------------------
    | Registration Defaults
    |--------------------------------------------------------------------------
    */

    'default_registration_role'   => env('SLEA_DEFAULT_REG_ROLE', 'student'),
    'default_registration_status' => env('SLEA_DEFAULT_REG_STATUS', 'pending'),


    /*
    |--------------------------------------------------------------------------
    | OTP Behaviour
    |--------------------------------------------------------------------------
    */

    'otp_max_attempts' => (int) env('SLEA_OTP_MAX_ATTEMPTS', 5),


    /*
    |--------------------------------------------------------------------------
    | Session Behaviour (Frontend)
    |--------------------------------------------------------------------------
    |
    | Used to configure the JS SessionTimeout warning.
    |
    */

    // Minutes before auto-logout when the warning dialog appears
    'session_warning_minutes' => (int) env('SLEA_SESSION_WARNING_MINUTES', 5),


    /*
    |--------------------------------------------------------------------------
    | Organization / Council Names
    |--------------------------------------------------------------------------
    |
    | Used by Admin, Reporting, and Student Leadership modules.
    |
    */

    'council_org_names' => [
        'University Student Government (USG)',
        'Obrero Student Council (OSC)',
        'Local Council (LC)',
        'Council of Clubs and Organizations (CCO)',
        'Student Clubs and Organizations (SCO)',
        'Local Government Unit (LGU)',
        'League of Class Mayors (LCM)',
        'Elective/Appointive Position (in organizations with approved/recognized Constitution and By-laws other than LGU)'
    ],

];
