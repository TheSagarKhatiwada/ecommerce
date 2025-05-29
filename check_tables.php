<?php
require_once 'bootstrap.php';
$db = new Database();
$result = $db->fetchAll('SHOW TABLES');
echo "Database Tables:\n";
foreach($result as $table) {
    echo "- " . array_values($table)[0] . "\n";
}
?>
