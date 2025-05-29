<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_db');

class Database {
    private $connection;
    
    public function __construct() {
        try {
            // Try MySQL first
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            // Fallback to SQLite for development
            try {
                $sqliteFile = __DIR__ . '/../database/ecommerce.db';
                $this->connection = new PDO(
                    "sqlite:" . $sqliteFile,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                // Create tables if they don't exist
                $this->createTables();
                
            } catch (PDOException $e2) {
                throw new Exception("Database connection failed: " . $e2->getMessage());
            }
        }
    }
    
    private function createTables() {
        // Create tables for SQLite
        $tables = [
            "CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                category_id INTEGER,
                image VARCHAR(255),
                stock_quantity INTEGER DEFAULT 0,
                featured BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )",
            
            "CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_name VARCHAR(255) NOT NULL,
                customer_email VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(20),
                shipping_address TEXT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )",
            
            "CREATE TABLE IF NOT EXISTS company_info (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                address TEXT,
                phone VARCHAR(20),
                email VARCHAR(255),
                website VARCHAR(255),
                about TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables as $sql) {
            $this->connection->exec($sql);
        }
        
        // Insert sample data if tables are empty
        $this->insertSampleData();
    }
    
    private function insertSampleData() {
        // Check if data already exists
        $count = $this->fetch("SELECT COUNT(*) as count FROM categories");
        if ($count['count'] > 0) {
            return; // Data already exists
        }
        
        // Insert categories
        $categories = [
            ['Smartphones', 'Latest smartphones and mobile devices'],
            ['Laptops', 'High-performance laptops and notebooks'],
            ['Tablets', 'Tablets and e-readers'],
            ['Accessories', 'Tech accessories and peripherals'],
            ['Gaming', 'Gaming devices and accessories']
        ];
        
        foreach ($categories as $cat) {
            $this->query("INSERT INTO categories (name, description) VALUES (?, ?)", $cat);
        }
        
        // Insert products
        $products = [
            ['iPhone 14 Pro', 'Latest iPhone with advanced camera system', 999.99, 1, 'iphone14pro.jpg', 50, 1],
            ['Samsung Galaxy S23', 'Powerful Android smartphone', 799.99, 1, 'galaxys23.jpg', 30, 1],
            ['MacBook Pro 16"', 'Professional laptop for creators', 2499.99, 2, 'macbookpro.jpg', 20, 1],
            ['Dell XPS 13', 'Ultra-portable Windows laptop', 1299.99, 2, 'dellxps13.jpg', 25, 1],
            ['iPad Pro 12.9"', 'Professional tablet with M2 chip', 1099.99, 3, 'ipadpro.jpg', 35, 1],
            ['Surface Pro 9', 'Versatile 2-in-1 tablet', 999.99, 3, 'surfacepro.jpg', 40, 0],
            ['AirPods Pro 2', 'Wireless earbuds with noise cancellation', 249.99, 4, 'airpodspro.jpg', 100, 0],
            ['Magic Mouse', 'Wireless mouse for Mac', 79.99, 4, 'magicmouse.jpg', 80, 0],
            ['PlayStation 5', 'Next-gen gaming console', 499.99, 5, 'ps5.jpg', 15, 1],
            ['Xbox Series X', 'Powerful gaming console', 499.99, 5, 'xboxseriesx.jpg', 18, 1],
            ['Nintendo Switch', 'Portable gaming console', 299.99, 5, 'switch.jpg', 45, 0],
            ['Gaming Headset', 'Professional gaming headset', 149.99, 5, 'headset.jpg', 60, 0],
            ['Wireless Charger', 'Fast wireless charging pad', 39.99, 4, 'charger.jpg', 120, 0],
            ['USB-C Hub', 'Multi-port USB-C hub', 69.99, 4, 'usbhub.jpg', 75, 0],
            ['Bluetooth Speaker', 'Portable Bluetooth speaker', 129.99, 4, 'speaker.jpg', 55, 0]
        ];
        
        foreach ($products as $product) {
            $this->query("INSERT INTO products (name, description, price, category_id, image, stock_quantity, featured) VALUES (?, ?, ?, ?, ?, ?, ?)", $product);
        }
        
        // Insert company info
        $this->query("INSERT INTO company_info (name, address, phone, email, website, about) VALUES (?, ?, ?, ?, ?, ?)", [
            'TechStore Pro',
            '123 Tech Avenue, Silicon Valley, CA 94025',
            '+1 (555) 123-4567',
            'contact@techstorepro.com',
            'https://techstorepro.com',
            'Your trusted partner for the latest technology products. We offer high-quality electronics, competitive prices, and exceptional customer service.'
        ]);
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
?>
