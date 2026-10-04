<?php
/**
 * Central configuration. Reads from environment variables first,
 * falls back to hardcoded defaults for local dev.
 *
 * To set in production, add to your .env or Apache/Nginx config:
 *   NCWSC_SMTP_USER, NCWSC_SMTP_PASS, NCWSC_SMTP_FROM, NCWSC_BASE_URL
 */

function env($key, $default = null) {
    $val = getenv($key);
    if ($val === false || $val === '') return $default;
    return $val;
}

return [
    'db' => [
        'host' => env('NCWSC_DB_HOST', '127.0.0.1'),
        'user' => env('NCWSC_DB_USER', 'root'),
        'pass' => env('NCWSC_DB_PASS', ''),
        'name' => env('NCWSC_DB_NAME', 'waterbilling'),
    ],

    'smtp' => [
        'host'       => env('NCWSC_SMTP_HOST', 'smtp.gmail.com'),
        'port'       => (int) env('NCWSC_SMTP_PORT', 587),
        'encryption' => env('NCWSC_SMTP_ENC', 'tls'),
        'username'   => env('NCWSC_SMTP_USER', 'vinniemariba2004@gmail.com'),
        'password'   => env('NCWSC_SMTP_PASS', 'xxxx xxxx xxxx xxxx'),
        'from_email' => env('NCWSC_SMTP_FROM', 'vinniemariba2004@gmail.com'),
        'from_name'  => 'NCWSC Online Services',
    ],

    'app' => [
        'name'     => 'NCWSC Online Services',
        'base_url' => env('NCWSC_BASE_URL', 'http://localhost:8000'),
        'debug'    => env('NCWSC_DEBUG', 'true') === 'true',
    ],
];