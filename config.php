<?php
/**
 * Central configuration.
 *
 * Values come from .env (loaded via phpdotenv) with sane fallbacks.
 * Use createMutable so getenv() works — createImmutable only writes $_ENV.
 */

// Load .env (if it exists) — safe to require this many times
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';

    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createMutable(__DIR__);
        $dotenv->safeLoad();
    }
}

// env() helper — guarded against redeclaration, reads from all three sources
if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        if ($val === false || $val === '') {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        }
        if ($val === false || $val === null || $val === '') return $default;
        return $val;
    }
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
        'password'   => env('NCWSC_SMTP_PASS', ''),
        'from_email' => env('NCWSC_SMTP_FROM', 'vinniemariba2004@gmail.com'),
        'from_name'  => 'NCWSC Online Services',
    ],

    'app' => [
        'name'     => 'NCWSC Online Services',
        'base_url' => env('NCWSC_BASE_URL', 'http://localhost:8000'),
        'debug'    => env('NCWSC_DEBUG', 'false') === 'true',
    ],

    'paystack' => [
    'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
    'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
],

'mpesa' => [
    'env'             => env('MPESA_ENV', 'sandbox'),                 // 'sandbox' | 'production'
    'consumer_key'    => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    'shortcode'       => env('MPESA_SHORTCODE', '174379'),            // sandbox default
    'passkey'         => env('MPESA_PASSKEY', ''),
    'callback_url'    => env('MPESA_CALLBACK_URL', ''),               // your ngrok URL
],
];