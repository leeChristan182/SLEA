<?php

return [
    // Cap the total number of admin accounts.
    'max_admin_accounts' => (int) env('SLEA_MAX_ADMINS', 3),

    // How many items per page in different views
    'pagination' => [
        'manage_accounts'    => 5,
        'approve_reject'     => 5,   // ⭐ add this
        'revalidation_queue' => 20,
        'award_report_list'  => 20,
        'award_report_page'  => 10,
    ],

    // Award thresholds (percentage)
    'award_thresholds' => [
        'gold'          => 90,
        'silver'        => 85,
        'qualified'     => 80,
        'tracking'      => 70,
        'not_qualified' => 0,
    ],
];
