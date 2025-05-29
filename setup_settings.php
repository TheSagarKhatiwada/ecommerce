<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Create settings table
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Settings table created successfully.\n";
    
    // Insert default settings
    $defaults = [
        ['site_name', 'My eCommerce Store'],
        ['site_description', 'Your one-stop shop for quality products'],
        ['site_email', 'contact@example.com'],
        ['currency', 'USD'],
        ['tax_rate', '10.00'],
        ['shipping_cost', '10.00'],
        ['maintenance_mode', '0']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    
    foreach ($defaults as $setting) {
        $stmt->execute([$setting[0], $setting[1]]);
    }
    
    echo "Default settings inserted successfully.\n";
    echo "Settings table setup complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
