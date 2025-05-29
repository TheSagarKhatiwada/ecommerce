<?php
// Database connection using mysqli
require_once __DIR__ . '/../config/config.php';

// Database connection
$servername = "localhost";
$username = "rootUser";
$password = "Sagar";
$dbname = "ecommerce_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");
?>
