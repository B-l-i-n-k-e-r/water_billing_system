<?php
/**
 * Daraja (Safaricom M-Pesa) helpers.
 */

/**
 * Get an OAuth access token from Daraja.
 */
function mpesaAccessToken(array $cfg) {
    $url = $cfg['env'] === 'production'
        ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => $cfg['consumer_key'] . ':' . $cfg['consumer_secret'],
        CURLOPT_TIMEOUT        => 20,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$resp) return null;

    $json = json_decode($resp, true);
    return $json['access_token'] ?? null;
}

/**
 * Initiate an STK Push.
 * Returns ['ok' => bool, 'raw' => array, 'error' => string?, 'CheckoutRequestID' => string?].
 */
function mpesaStkPush(array $cfg, string $phone, float $amountKes, string $accountRef, string $description) {
    $token = mpesaAccessToken($cfg);
    if (!$token) {
        return ['ok' => false, 'error' => 'auth_failed'];
    }

    $url = $cfg['env'] === 'production'
        ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $timestamp = date('YmdHis');
    $password  = base64_encode($cfg['shortcode'] . $cfg['passkey'] . $timestamp);

    // Phone must be in 2547XXXXXXXX format
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) === 10 && $phone[0] === '0') {
        $phone = '254' . substr($phone, 1);
    } elseif (strlen($phone) === 9 && $phone[0] === '7') {
        $phone = '254' . $phone;
    }

    $payload = [
        'BusinessShortCode' => $cfg['shortcode'],
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => (int) ceil($amountKes),
        'PartyA'            => $phone,
        'PartyB'            => $cfg['shortcode'],
        'PhoneNumber'       => $phone,
        'CallBackURL'       => $cfg['callback_url'],
        'AccountReference'  => substr($accountRef, 0, 12),
        'TransactionDesc'   => substr($description, 0, 13),
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($resp, true);

    if ($code !== 200 || empty($json['CheckoutRequestID'])) {
        return ['ok' => false, 'error' => 'stk_failed', 'raw' => $json];
    }

    return [
        'ok'               => true,
        'raw'              => $json,
        'CheckoutRequestID'=> $json['CheckoutRequestID'],
        'MerchantRequestID'=> $json['MerchantRequestID'] ?? null,
    ];
}