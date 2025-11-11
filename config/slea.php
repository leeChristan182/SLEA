<?php

return [
    // Cap the total number of admin accounts.
    // Change to 5 if you prefer. You can also set via .env: SLEA_MAX_ADMINS=5
    'max_admin_accounts' => (int) env('SLEA_MAX_ADMINS', 3),
];
