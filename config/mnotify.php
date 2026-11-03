<?php

return [
    'api_key' => env('MNOTIFY_API_KEY'),
    'sender_id' => env('MNOTIFY_SENDER_ID', 'EASYSCHOOL'),
    'base_url' => env('MNOTIFY_BASE_URL', 'https://api.mnotify.com/api'),
    'enabled' => env('MNOTIFY_ENABLED', true),

    // Some school/Windows networks reset TLS to api.mnotify.com. Retry over HTTP if HTTPS fails.
    'allow_http_fallback' => env('MNOTIFY_ALLOW_HTTP_FALLBACK', true),
];
