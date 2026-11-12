<?php

return [
    'paths' => ['*'],
    'allowed_methods' => ['GET','HEAD','POST','PUT','PATCH','DELETE','OPTIONS'],
    'allowed_origins' => \App\Support\CorsOrigins::parse((string) env('CORS_ALLOWED_ORIGINS', '')),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept','Content-Type','Authorization','X-Requested-With','X-CSRF-TOKEN','X-XSRF-TOKEN'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
