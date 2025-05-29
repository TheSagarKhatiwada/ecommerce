<?php
// Test session functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Session Test</h2>";

// Include our session configuration
require_once 'includes/classes.php';

echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

// Test session variables
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
}
$_SESSION['test_counter']++;

echo "<p>Session Test Counter: " . $_SESSION['test_counter'] . "</p>";

// Check if session file exists
$session_file = session_save_path() . '/sess_' . session_id();
echo "<p>Session File: " . $session_file . "</p>";
echo "<p>Session File Exists: " . (file_exists($session_file) ? 'YES' : 'NO') . "</p>";

if (file_exists($session_file)) {
    echo "<p>Session File Size: " . filesize($session_file) . " bytes</p>";
    echo "<p>Session File Permissions: " . substr(sprintf('%o', fileperms($session_file)), -4) . "</p>";
}

// Display session directory permissions
$session_dir = session_save_path();
echo "<p>Session Directory: " . $session_dir . "</p>";
echo "<p>Session Directory Writable: " . (is_writable($session_dir) ? 'YES' : 'NO') . "</p>";
echo "<p>Session Directory Permissions: " . substr(sprintf('%o', fileperms($session_dir)), -4) . "</p>";

echo "<p><a href='test_session.php'>Refresh to test session persistence</a></p>";
?>
