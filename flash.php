<?php
/**
 * Flash message helpers.
 * Usage:
 *   setFlash('success', 'Client saved.');
 *   setFlash('error', 'Something went wrong.');
 *   // In the view:
 *   renderFlash();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('getFlashes')) {
    function getFlashes() {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }
}

if (!function_exists('renderFlash')) {
    function renderFlash() {
        $flashes = getFlashes();
        if (empty($flashes)) return;

        $styles = [
            'success' => 'bg-green-50 border-green-200 text-green-700',
            'error'   => 'bg-red-50 border-red-200 text-red-700',
            'info'    => 'bg-blue-50 border-blue-200 text-blue-700',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        ];

        foreach ($flashes as $f) {
            $type = $f['type'] ?? 'info';
            $cls  = $styles[$type] ?? $styles['info'];
            echo '<div class="mb-4 p-3 rounded-md border ' . $cls . ' text-sm">'
               . $f['message']
               . '</div>';
        }
    }
}
if (!function_exists('emptyState')) {
    /**
     * Render a friendly empty state.
     * @param string $icon    Lucide icon name (e.g. 'users')
     * @param string $title   Headline
     * @param string $message Supporting text
     * @param string $ctaText Optional button text
     * @param string $ctaHref Optional button URL
     */
    function emptyState($icon, $title, $message, $ctaText = '', $ctaHref = '') {
        echo '<div class="text-center py-12 px-6">';
        echo   '<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-4">';
        echo     '<i data-lucide="' . htmlspecialchars($icon) . '" class="w-8 h-8"></i>';
        echo   '</div>';
        echo   '<h3 class="text-lg font-semibold text-gray-800 mb-2">' . htmlspecialchars($title) . '</h3>';
        echo   '<p class="text-sm text-gray-500 max-w-md mx-auto mb-6">' . $message . '</p>';
        if ($ctaText && $ctaHref) {
            echo '<a href="' . htmlspecialchars($ctaHref) . '" class="btn-blue text-white font-semibold py-2 px-6 rounded-md inline-block">'
               . htmlspecialchars($ctaText) . '</a>';
        }
        echo '</div>';
    }
}