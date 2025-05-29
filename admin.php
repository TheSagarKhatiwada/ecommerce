<?php
// Simple admin interface for managing company information
session_start();

// Simple password protection (in production, use proper authentication)
$admin_password = 'admin123';
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $is_logged_in = true;
    } else {
        $login_error = 'Invalid password';
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($is_logged_in) {
    require_once 'includes/classes.php';
    
    $companyInfo = new CompanyInfo();
    $company = $companyInfo->getCompanyInfo();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company'])) {
        $db = new Database();
        
        $sql = "UPDATE company_info SET 
                name = ?, 
                address = ?, 
                phone = ?, 
                email = ?, 
                website = ?, 
                opening_hours = ?
                WHERE id = ?";
        
        try {
            $db->query($sql, [
                $_POST['name'],
                $_POST['address'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['website'],
                $_POST['opening_hours'],
                $company['id']
            ]);
            $success_message = 'Company information updated successfully!';
            // Refresh company data
            $company = $companyInfo->getCompanyInfo();
        } catch (Exception $e) {
            $error_message = 'Error updating company information: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Company Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php if (!$is_logged_in): ?>
    <!-- Login Form -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="bi bi-shield-lock"></i> Admin Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Default password: admin123</small>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Admin Dashboard -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-gear"></i> Admin Panel
            </span>
            <div class="d-flex">
                <a href="index.php" class="btn btn-outline-light me-2" target="_blank">
                    <i class="bi bi-eye"></i> View Site
                </a>
                <form method="POST" class="d-inline">
                    <button type="submit" name="logout" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1>Company Information Management</h1>
        <p class="text-muted">Update your company details that appear throughout the website.</p>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Company Details</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Company Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($company['name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="text" class="form-control" id="website" name="website" 
                                   value="<?php echo htmlspecialchars($company['website']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($company['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone *</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($company['phone']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($company['address']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="opening_hours" class="form-label">Opening Hours</label>
                        <textarea class="form-control" id="opening_hours" name="opening_hours" rows="4"><?php echo htmlspecialchars($company['opening_hours']); ?></textarea>
                        <small class="text-muted">Enter each day on a new line, e.g., "Monday - Friday: 9:00 AM - 8:00 PM"</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Site
                        </a>
                        <button type="submit" name="update_company" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Quick Stats</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $db = new Database();
                        $productCount = $db->fetch("SELECT COUNT(*) as count FROM products")['count'];
                        $categoryCount = $db->fetch("SELECT COUNT(*) as count FROM categories")['count'];
                        $orderCount = $db->fetch("SELECT COUNT(*) as count FROM orders")['count'];
                        ?>
                        <p class="mb-2"><strong>Products:</strong> <?php echo $productCount; ?></p>
                        <p class="mb-2"><strong>Categories:</strong> <?php echo $categoryCount; ?></p>
                        <p class="mb-0"><strong>Orders:</strong> <?php echo $orderCount; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-question-circle"></i> Help</h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">• Company information appears in the header, footer, and contact page</p>
                        <p class="small mb-2">• Changes take effect immediately on the website</p>
                        <p class="small mb-0">• All fields marked with * are required</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
