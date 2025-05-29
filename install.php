<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eCommerce Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .install-container { max-width: 800px; margin: 50px auto; }
        .step { padding: 20px; margin: 20px 0; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container install-container">
        <h1 class="text-center mb-4">eCommerce Website Installation</h1>
        
        <?php
        $installationCompleted = false;
        $errors = [];
        $messages = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
            try {
                // Get database connection details from form
                $dbHost = $_POST['db_host'] ?? 'localhost';
                $dbUser = $_POST['db_user'] ?? 'root';
                $dbPass = $_POST['db_pass'] ?? '';
                $dbName = $_POST['db_name'] ?? 'ecommerce_db';
                
                // Update database configuration
                $configContent = "<?php
// Database configuration
define('DB_HOST', '" . addslashes($dbHost) . "');
define('DB_USER', '" . addslashes($dbUser) . "');
define('DB_PASS', '" . addslashes($dbPass) . "');
define('DB_NAME', '" . addslashes($dbName) . "');

class Database {
    private \$connection;
    
    public function __construct() {
        try {
            \$this->connection = new PDO(
                \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException \$e) {
            throw new Exception(\"Database connection failed: \" . \$e->getMessage());
        }
    }
    
    public function getConnection() {
        return \$this->connection;
    }
    
    public function fetchAll(\$sql, \$params = []) {
        \$stmt = \$this->connection->prepare(\$sql);
        \$stmt->execute(\$params);
        return \$stmt->fetchAll();
    }
    
    public function fetch(\$sql, \$params = []) {
        \$stmt = \$this->connection->prepare(\$sql);
        \$stmt->execute(\$params);
        return \$stmt->fetch();
    }
    
    public function execute(\$sql, \$params = []) {
        \$stmt = \$this->connection->prepare(\$sql);
        return \$stmt->execute(\$params);
    }
    
    public function lastInsertId() {
        return \$this->connection->lastInsertId();
    }
}
?>";
                
                file_put_contents('config/database.php', $configContent);
                $messages[] = "✓ Database configuration updated";
                
                // Test database connection
                $pdo = new PDO(
                    "mysql:host=$dbHost",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $messages[] = "✓ Connected to MySQL server";
                
                // Read and execute schema
                $schemaFile = 'database/schema.sql';
                if (!file_exists($schemaFile)) {
                    throw new Exception("Schema file not found: $schemaFile");
                }
                
                $sql = file_get_contents($schemaFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        try {
                            $pdo->exec($statement);
                            
                            if (stripos($statement, 'CREATE DATABASE') !== false) {
                                $messages[] = "✓ Database '$dbName' created";
                            } elseif (stripos($statement, 'CREATE TABLE') !== false) {
                                preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches);
                                if ($matches) {
                                    $messages[] = "✓ Table '{$matches[1]}' created";
                                }
                            } elseif (stripos($statement, 'INSERT INTO categories') !== false) {
                                $messages[] = "✓ Sample categories inserted";
                            } elseif (stripos($statement, 'INSERT INTO products') !== false) {
                                $messages[] = "✓ Sample products inserted";
                            } elseif (stripos($statement, 'INSERT INTO company_info') !== false) {
                                $messages[] = "✓ Company information inserted";
                            }
                        } catch (PDOException $e) {
                            if (stripos($e->getMessage(), 'database exists') === false) {
                                throw $e;
                            }
                        }
                    }
                }
                
                $installationCompleted = true;
                $messages[] = "✅ Installation completed successfully!";
                
            } catch (Exception $e) {
                $errors[] = "❌ Error: " . $e->getMessage();
            }
        }
        ?>
        
        <?php if (!$installationCompleted): ?>
        <div class="step">
            <h3>Database Configuration</h3>
            <p>Please provide your MySQL database connection details:</p>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="db_host" class="form-label">Database Host</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="db_name" class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" value="ecommerce_db" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="db_user" class="form-label">Database Username</label>
                            <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="db_pass" class="form-label">Database Password</label>
                            <input type="password" class="form-control" id="db_pass" name="db_pass" placeholder="Leave empty if no password">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="install" class="btn btn-primary btn-lg">Install eCommerce Website</button>
            </form>
        </div>
        
        <div class="step">
            <h4>Requirements Check</h4>
            <ul class="list-unstyled">
                <li class="<?php echo extension_loaded('pdo') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('pdo') ? '✓' : '✗'; ?> PDO Extension
                </li>
                <li class="<?php echo extension_loaded('pdo_mysql') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('pdo_mysql') ? '✓' : '✗'; ?> PDO MySQL Extension
                </li>
                <li class="<?php echo is_writable('config/') ? 'success' : 'error'; ?>">
                    <?php echo is_writable('config/') ? '✓' : '✗'; ?> Config directory writable
                </li>
                <li class="<?php echo file_exists('database/schema.sql') ? 'success' : 'error'; ?>">
                    <?php echo file_exists('database/schema.sql') ? '✓' : '✗'; ?> Database schema file exists
                </li>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="step">
            <h4 class="error">Installation Errors</h4>
            <ul class="list-unstyled">
                <?php foreach ($errors as $error): ?>
                <li class="error"><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            
            <p><strong>Troubleshooting:</strong></p>
            <ul>
                <li>Make sure MySQL server is running</li>
                <li>Verify database credentials are correct</li>
                <li>Ensure the database user has permission to create databases</li>
                <li>Check that PDO and PDO MySQL extensions are installed</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($messages)): ?>
        <div class="step">
            <h4 class="success">Installation Progress</h4>
            <ul class="list-unstyled">
                <?php foreach ($messages as $message): ?>
                <li class="success"><?php echo htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($installationCompleted): ?>
        <div class="step text-center">
            <h3 class="success">🎉 Installation Complete!</h3>
            <p>Your eCommerce website is now ready to use.</p>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5>What's Next?</h5>
                    <ul class="text-start">
                        <li>Visit your <a href="index.php" class="btn btn-success btn-sm">website homepage</a></li>
                        <li>Access the <a href="admin.php" class="btn btn-info btn-sm">admin panel</a> (password: admin123)</li>
                        <li>Browse sample products</li>
                        <li>Test the shopping cart functionality</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Sample Data Included</h5>
                    <ul class="text-start">
                        <li>5 product categories</li>
                        <li>15+ sample products</li>
                        <li>Company information</li>
                        <li>All necessary database tables</li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-warning mt-4">
                <strong>Security Note:</strong> Remember to change the admin password in <code>admin.php</code> for production use!
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
