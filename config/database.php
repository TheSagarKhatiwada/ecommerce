<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'rootUser');
define('DB_PASS', 'Sagar');
define('DB_NAME', 'ecommerce_db');

class Database {
    private $connection;
    private static $instance = null;
    
    public function __construct() {
        try {
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
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    // Admin-specific methods
    public function getDashboardStats() {
        $stats = [];
        
        // Total products
        $result = $this->fetch("SELECT COUNT(*) as count FROM products");
        $stats['total_products'] = $result['count'];
        
        // Total orders
        $result = $this->fetch("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $result['count'];
        
        // Total revenue
        $result = $this->fetch("SELECT SUM(total_amount) as total FROM orders");
        $stats['total_revenue'] = $result['total'] ?? 0;
        
        // Recent orders count (last 30 days)
        $result = $this->fetch("SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['recent_orders'] = $result['count'];
        
        // Products by category
        $stats['products_by_category'] = $this->fetchAll("
            SELECT c.name, COUNT(p.id) as count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id, c.name
        ");
        
        // Recent orders
        $stats['recent_orders_list'] = $this->fetchAll("
            SELECT * FROM orders 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        return $stats;
    }
    
    public function getProductsPaginated($page = 1, $perPage = 20, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE p.name LIKE ? OR p.description LIKE ?";
            $params = ["%$search%", "%$search%"];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
        $totalResult = $this->fetch($countSql, $params);
        $total = $totalResult['total'];
        
        // Get products
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $whereClause
                ORDER BY p.created_at DESC 
                LIMIT $perPage OFFSET $offset";
        
        $products = $this->fetchAll($sql, $params);
        
        return [
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    public function getOrdersPaginated($page = 1, $perPage = 20, $status = '') {
        $offset = ($page - 1) * $perPage;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($status)) {
            $whereClause = "WHERE status = ?";
            $params = [$status];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM orders $whereClause";
        $totalResult = $this->fetch($countSql, $params);
        $total = $totalResult['total'];
        
        // Get orders
        $sql = "SELECT * FROM orders 
                $whereClause
                ORDER BY created_at DESC 
                LIMIT $perPage OFFSET $offset";
        
        $orders = $this->fetchAll($sql, $params);
        
        return [
            'data' => $orders,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    public function getOrderItems($orderId) {
        return $this->fetchAll("
            SELECT oi.*, p.name as product_name, p.image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$orderId]);
    }
}
?>