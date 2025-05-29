<?php
// Site configuration
define('SITE_NAME', 'TechStore Pro');
define('SITE_URL', 'http://localhost');
define('SITE_EMAIL', 'contact@techstorepro.com');

// Currency settings
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '$');
}
if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'USD');
}

// Shipping settings
define('FREE_SHIPPING_THRESHOLD', 50.00);
define('SHIPPING_COST', 9.99);

// Tax settings
define('TAX_RATE', 0.08); // 8%

// Pagination settings
define('PRODUCTS_PER_PAGE', 12);
define('FEATURED_PRODUCTS_COUNT', 8);

// Image settings
define('UPLOAD_PATH', 'assets/images/products/');
define('DEFAULT_IMAGE', 'assets/images/placeholder.svg');

// Email settings (for future implementation)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Development settings
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', DISPLAY_ERRORS ? 1 : 0);
}
?>
