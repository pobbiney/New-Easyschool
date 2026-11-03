<?php

return [

    'default_password' => env('PARENT_DEFAULT_PASSWORD', 'Parent123'),

    'portal_name' => env('PARENT_PORTAL_NAME', 'Parent Portal'),

    'login_attempts' => 5,

    'lockout_minutes' => 10,

    'otp_expire_minutes' => 10,

    'otp_resend_seconds' => 60,

    'otp_max_attempts' => 5,

];
