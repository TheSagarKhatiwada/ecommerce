<?php
require_once __DIR__ . '/../bootstrap.php';

$companyInfo = new CompanyInfo();
$company = $companyInfo->getCompanyInfo();

function formatPrice($price) {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo htmlspecialchars($company['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .product-card {
            transition: transform 0.2s;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            padding: 4px 8px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            font-size: 0.75rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        
        .category-card {
            transition: transform 0.2s;
        }
        
        .category-card:hover {
            transform: scale(1.05);
        }
        
        .footer {
            background-color: #2c3e50;
            color: #ecf0f1;
        }
        
        .search-form {
            max-width: 500px;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> <?php echo htmlspecialchars($company['name']); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $categoryModel = new Category();
                            $categories = $categoryModel->getAllCategories();
                            foreach ($categories as $cat): ?>
                                <li><a class="dropdown-item" href="category.php?id=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form class="d-flex me-3 search-form" method="GET" action="search.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search products..." 
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                
                <!-- Cart -->
                <a href="cart.php" class="btn btn-outline-light position-relative">
                    <i class="bi bi-cart3"></i>
                    <?php 
                    $cart = new Cart();
                    $itemCount = $cart->getItemCount();
                    if ($itemCount > 0): ?>
                        <span class="cart-badge"><?php echo $itemCount; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>
