<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name' => 'CK Florist',
    'env' => Env::get('APP_ENV', 'production'),
    'url' => Env::get('APP_URL', 'https://ckflorist.my'),
    'key' => Env::get('APP_KEY', ''),
    'session' => [
        'name' => 'ckf_session',
        'secure' => Env::bool('SESSION_SECURE', true),
    ],
    'upload_max_bytes' => (int) Env::get('UPLOAD_MAX_BYTES', '5242880'),
    'statuses' => ['New', 'Contacted', 'Awaiting Confirmation', 'Confirmed', 'Deposit Pending', 'In Preparation', 'Ready', 'Completed', 'Cancelled'],
];

