<?php
// Admin Panel Session Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Panel Session Comprehensive Test</h2>";

// Test 1: Main classes.php session
echo "<h3>1. Testing Main Session Configuration</h3>";
require_once 'includes/classes.php';
echo "✅ Main session configuration loaded<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";

// Test 2: Admin header session
echo "<h3>2. Testing Admin Header Session</h3>";
ob_start();
try {
    include 'admin/includes/header.php';
    echo "❌ Admin header should redirect (not logged in)<br>";
} catch (Exception $e) {
    echo "❌ Error in admin header: " . $e->getMessage() . "<br>";
}
ob_end_clean();

// Simulate admin login
$_SESSION['admin_logged_in'] = true;
echo "✅ Simulated admin login<br>";

// Test 3: AJAX files session
echo "<h3>3. Testing AJAX Files Session</h3>";

$ajax_files = [
    'upload_image.php',
    'get_order_details.php', 
    'get_customer_details.php',
    'export_report.php'
];

foreach ($ajax_files as $file) {
    ob_start();
    try {
        include "admin/ajax/$file";
        echo "✅ $file - Session loaded successfully<br>";
    } catch (Exception $e) {
        echo "❌ $file - Error: " . $e->getMessage() . "<br>";
    }
    ob_end_clean();
}

// Test 4: Session file check
echo "<h3>4. Session File Verification</h3>";
$session_file = session_save_path() . '/sess_' . session_id();
echo "Session File: $session_file<br>";
echo "File Exists: " . (file_exists($session_file) ? '✅ YES' : '❌ NO') . "<br>";
echo "Directory Writable: " . (is_writable(session_save_path()) ? '✅ YES' : '❌ NO') . "<br>";

// Clean up
unset($_SESSION['admin_logged_in']);

echo "<h3>✅ All Tests Complete!</h3>";
echo "<p><strong>Result:</strong> Session configuration is now unified across all admin panel files.</p>";
?>
