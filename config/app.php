<?php

return [
    'name' => env('APP_NAME', 'Yippee Learning Platform'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://ingilizce.test'),
    'timezone' => env('APP_TIMEZONE', 'Europe/Istanbul'),
    'uploads_path' => dirname(__DIR__) . '/uploads',
];
