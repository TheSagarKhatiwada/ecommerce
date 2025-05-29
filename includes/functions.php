<?php
// Common functions for the admin panel

// Set custom session save path to avoid Windows permission issues
function setupSession() {
    $sessionPath = realpath(__DIR__ . '/../sessions');
    if (!$sessionPath || !is_dir($sessionPath)) {
        $sessionPath = __DIR__ . '/../sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0777, true);
        }
        chmod($sessionPath, 0777);
    }

    // Set session configuration before starting session
    ini_set('session.save_path', $sessionPath);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.gc_maxlifetime', 1440);
}

// Format currency
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

// Format price (alias for formatCurrency for consistency)
function formatPrice($amount) {
    return formatCurrency($amount);
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
