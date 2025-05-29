<?php
require_once 'includes/classes.php';

$productModel = new Product();
$categoryModel = new Category();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category = $categoryModel->getCategoryById($_GET['id']);

if (!$category) {
    header('Location: index.php');
    exit;
}

$products = $productModel->getProductsByCategory($_GET['id']);

$pageTitle = $category['name'];
require_once 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($category['name']); ?></h1>
            <?php if ($category['description']): ?>
            <p class="text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-md-end">
            <p class="text-muted"><?php echo count($products); ?> products found</p>
        </div>
    </div>
    
    <?php if (empty($products)): ?>
    <div class="text-center py-5">
        <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3">No products found</h4>
        <p class="text-muted">There are currently no products in this category.</p>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card product-card border-0 shadow-sm">
                <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                     class="card-img-top product-image" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     onerror="this.src='assets/images/placeholder.jpg'">
                <div class="card-body">
                    <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                    <p class="card-text text-muted small"><?php echo truncateText($product['description'], 80); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 text-primary mb-0"><?php echo formatPrice($product['price']); ?></span>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <small class="text-success">In Stock</small>
                        <?php else: ?>
                            <small class="text-danger">Out of Stock</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <div class="d-grid gap-2">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                        <?php if ($product['stock_quantity'] > 0): ?>
                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="bi bi-x-circle"></i> Out of Stock
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
