<?php
// Include session configuration from main classes file
require_once __DIR__ . '/../../includes/classes.php';

// Simple authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin.php');
    exit;
}

require_once __DIR__ . '/../../bootstrap.php';

// Helper functions
function formatPrice($price) {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - <?php echo SITE_NAME; ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Admin Styles -->
    <link href="assets/admin.css" rel="stylesheet">
    
    <!-- Additional page styles -->
    <?php if (isset($additional_styles)) echo $additional_styles; ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-brand">
                    <i class="bi bi-speedometer2"></i>
                    Admin Panel
                </a>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php" class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                        <i class="bi bi-house nav-icon"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="products.php" class="nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>">
                        <i class="bi bi-box nav-icon"></i>
                        Products
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="categories.php" class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>">
                        <i class="bi bi-tags nav-icon"></i>
                        Categories
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="orders.php" class="nav-link <?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                        <i class="bi bi-cart nav-icon"></i>
                        Orders
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="customers.php" class="nav-link <?php echo $current_page === 'customers' ? 'active' : ''; ?>">
                        <i class="bi bi-people nav-icon"></i>
                        Customers
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="settings.php" class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                        <i class="bi bi-gear nav-icon"></i>
                        Settings
                    </a>
                </div>
                
                <div class="nav-item" style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                    <a href="../index.php" class="nav-link">
                        <i class="bi bi-globe nav-icon"></i>
                        View Website
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="../admin.php?action=logout" class="nav-link">
                        <i class="bi bi-box-arrow-right nav-icon"></i>
                        Logout
                    </a>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="d-flex align-items-center gap-3">
                    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="header-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>
                
                <div class="header-actions">
                    <div class="user-menu">
                        <div class="user-avatar">A</div>
                        <div>
                            <div style="font-weight: 500; font-size: 0.875rem;">Admin</div>
                            <div style="font-size: 0.75rem; color: var(--light-text);">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <main class="content">
