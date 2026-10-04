<?php
/**
 * Calculate a water bill based on consumption and the tariff table.
 * Returns ['consumption', 'water_charge', 'sewer_charge', 'meter_rent', 'subtotal', 'vat', 'total', 'breakdown']
 */
function calculateBill($consumption, $conn) {
    $consumption = (float) $consumption;
    $breakdown = [];

    // Fetch tariff tiers
    $tiers = [];
    $res = mysqli_query($conn, "SELECT tier_min, tier_max, rate FROM tariff ORDER BY tier_min ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $tiers[] = $row;
    }

    // Water charge — tiered
    $water = 0.0;
    $remaining = $consumption;

    foreach ($tiers as $tier) {
        if ($remaining <= 0) break;

        $tier_min = (float) $tier['tier_min'];
        $tier_max = (float) $tier['tier_max'];
        $rate     = (float) $tier['rate'];

        $tier_span = $tier_max - $tier_min + 1;
        $used_in_tier = min($remaining, $tier_span);

        $charge = $used_in_tier * $rate;
        $water += $charge;
        $remaining -= $used_in_tier;

        $breakdown[] = [
            'range' => $tier_min . ' – ' . $tier_max . ' m³',
            'units' => $used_in_tier,
            'rate'  => $rate,
            'cost'  => $charge,
        ];
    }

    // Fixed & proportional charges
    $get = function($key, $default) use ($conn) {
        $stmt = mysqli_prepare($conn, "SELECT setting_value FROM settings WHERE setting_key = ?");
        mysqli_stmt_bind_param($stmt, "s", $key);
        mysqli_stmt_execute($stmt);
        $r = mysqli_stmt_get_result($stmt);
        $v = $r ? mysqli_fetch_assoc($r)['setting_value'] ?? $default : $default;
        mysqli_stmt_close($stmt);
        return (float) $v;
    };

    $sewer_rate  = $get('sewer_rate', 0.75);
    $meter_rent  = $get('meter_rent', 200);
    $vat_rate    = $get('vat_rate', 0.16);

    $sewer   = $water * $sewer_rate;
    $subtotal = $water + $sewer + $meter_rent;
    $vat     = $subtotal * $vat_rate;
    $total   = $subtotal + $vat;

    return [
        'consumption'   => $consumption,
        'water_charge'  => round($water, 2),
        'sewer_charge'  => round($sewer, 2),
        'meter_rent'    => round($meter_rent, 2),
        'subtotal'      => round($subtotal, 2),
        'vat'           => round($vat, 2),
        'total'         => round($total, 2),
        'breakdown'     => $breakdown,
    ];
}

/**
 * Generate a bill month label like "Oct 2026"
 */
function currentBillMonth() {
    return date('M Y');
}

/**
 * Format KES
 */
function kes($amount) {
    return 'KES ' . number_format((float) $amount, 2);
}