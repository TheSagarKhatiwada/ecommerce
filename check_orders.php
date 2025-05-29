<?php
require_once 'bootstrap.php';
$db = new Database();
$result = $db->fetchAll('DESCRIBE orders');
echo "Orders Table Structure:\n";
foreach($result as $column) {
    echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
}
?>
