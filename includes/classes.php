<?php
// Set custom session save path to avoid Windows permission issues
$sessionPath = realpath(__DIR__ . '/../sessions');
if (!$sessionPath || !is_dir($sessionPath)) {
    $sessionPath = __DIR__ . '/../sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    chmod($sessionPath, 0777);
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session configuration before starting session
    ini_set('session.save_path', $sessionPath);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.gc_maxlifetime', 1440);
    
    session_start();
}
require_once __DIR__ . '/../config/database.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function addItem($productId, $quantity = 1) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
    
    public function removeItem($productId) {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }
    
    public function updateQuantity($productId, $quantity) {
        if ($quantity <= 0) {
            $this->removeItem($productId);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
    
    public function getItems() {
        if (empty($_SESSION['cart'])) {
            return [];
        }
        
        $productIds = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        
        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
        $products = $this->db->fetchAll($sql, $productIds);
        
        foreach ($products as &$product) {
            $product['quantity'] = $_SESSION['cart'][$product['id']];
            $product['total'] = $product['price'] * $product['quantity'];
        }
        
        return $products;
    }
    
    public function getTotal() {
        $total = 0;
        $items = $this->getItems();
        foreach ($items as $item) {
            $total += $item['total'];
        }
        return $total;
    }
    
    public function getItemCount() {
        return array_sum($_SESSION['cart']);
    }
    
    public function clear() {
        $_SESSION['cart'] = [];
    }
}

class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
      public function getAllProducts($limit = null, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $limit = (int)$limit; // Ensure it's an integer for security
            $offset = (int)$offset;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $this->db->fetchAll($sql);
    }
      public function getFeaturedProducts($limit = 8) {
        $limit = (int)$limit; // Ensure it's an integer for security
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.featured = 1 
                ORDER BY p.created_at DESC LIMIT $limit";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getProductById($id) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
      public function getProductsByCategory($categoryId, $limit = null) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $limit = (int)$limit; // Ensure it's an integer for security
            $sql .= " LIMIT $limit";
        }
        
        return $this->db->fetchAll($sql, [$categoryId]);
    }
    
    public function searchProducts($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE ? OR p.description LIKE ? 
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }
}

class Category {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAllCategories() {
        return $this->db->fetchAll("SELECT * FROM categories ORDER BY name");
    }
    
    public function getCategoryById($id) {
        return $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    }
}

class Order {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createOrder($customerData, $cartItems) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Insert order
            $sql = "INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['total'];
            }
              $this->db->execute($sql, [
                $customerData['name'],
                $customerData['email'],
                $customerData['phone'],
                $customerData['address'],
                $total
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Insert order items
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
              foreach ($cartItems as $item) {
                $this->db->execute($sql, [
                    $orderId,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $this->db->getConnection()->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    public function getOrderById($id) {
        return $this->db->fetch("SELECT * FROM orders WHERE id = ?", [$id]);
    }
}

class CompanyInfo {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getCompanyInfo() {
        return $this->db->fetch("SELECT * FROM company_info LIMIT 1");
    }
}
?>
