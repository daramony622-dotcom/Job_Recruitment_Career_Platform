<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter(explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:3000,http://localhost:5173,http://127.0.0.1:5173'
    ))),

    'allowed_headers' => [
        'Content-Type', 'Authorization',
        'X-CSRF-TOKEN', 'X-XSRF-TOKEN', 'X-Requested-With',
        'Accept', 'Origin',
    ],

    'exposed_headers' => ['Content-Length', 'X-JSON-Response'],

    'max_age' => 86400,

    'supports_credentials' => true,
];