<?php
require_once 'config/database.php';

echo "Setting up eCommerce Database...\n\n";

try {
    // Read the schema file
    $schemaFile = 'database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    if ($sql === false) {
        throw new Exception("Could not read schema file");
    }
    
    // Split SQL statements (simple approach)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Connect to MySQL server (without specific database first)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Connected to MySQL server\n";
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                
                // Show progress for important operations
                if (stripos($statement, 'CREATE DATABASE') !== false) {
                    echo "✓ Database created\n";
                } elseif (stripos($statement, 'CREATE TABLE') !== false) {
                    preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches);
                    if ($matches) {
                        echo "✓ Table '{$matches[1]}' created\n";
                    }
                } elseif (stripos($statement, 'INSERT INTO categories') !== false) {
                    echo "✓ Sample categories inserted\n";
                } elseif (stripos($statement, 'INSERT INTO products') !== false) {
                    echo "✓ Sample products inserted\n";
                } elseif (stripos($statement, 'INSERT INTO company_info') !== false) {
                    echo "✓ Company information inserted\n";
                }
            } catch (PDOException $e) {
                // Ignore "database exists" errors
                if (stripos($e->getMessage(), 'database exists') === false) {
                    throw $e;
                }
            }
        }
    }
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Start your web server\n";
    echo "2. Navigate to your eCommerce site\n";
    echo "3. Browse products and test the shopping cart\n\n";
    
    echo "Default company: TechStore Pro\n";
    echo "Sample categories: Electronics, Clothing, Books, Home & Garden, Sports\n";
    echo "Sample products: 15+ products across all categories\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error setting up database: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL server is running\n";
    echo "2. Database credentials in config/database.php are correct\n";
    echo "3. User has permission to create databases\n\n";
    exit(1);
}
?>
