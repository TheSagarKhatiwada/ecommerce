<?php
require_once __DIR__ . '/bootstrap.php';

$productModel = new Product();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product = $productModel->getProductById($_GET['id']);

if (!$product) {
    header('Location: index.php');
    exit;
}

$pageTitle = $product['name'];
require_once 'includes/header.php';

// Get related products from same category
$relatedProducts = $productModel->getProductsByCategory($product['category_id'], 4);
// Remove current product from related products
$relatedProducts = array_filter($relatedProducts, function($p) use ($product) {
    return $p['id'] != $product['id'];
});
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0">
                <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     style="height: 400px; object-fit: cover;"
                     onerror="this.src='assets/images/placeholder.jpg'">
            </div>
        </div>
        
        <div class="col-lg-6">
            <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="mb-3">
                <span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span>
            </div>
            
            <div class="mb-4">
                <span class="h3 text-primary"><?php echo formatPrice($product['price']); ?></span>
            </div>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <div class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Stock:</strong> 
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="text-success"><?php echo $product['stock_quantity']; ?> available</span>
                        <?php else: ?>
                            <span class="text-danger">Out of stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>SKU:</strong> <span class="text-muted">PRD-<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($product['stock_quantity'] > 0): ?>
            <form class="mb-4" onsubmit="return false;">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <div class="col-md-8">
                        <button type="button" class="btn btn-primary btn-lg w-100" onclick="addToCartWithQuantity(<?php echo $product['id']; ?>)">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> This product is currently out of stock.
            </div>
            <?php endif; ?>
            
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" onclick="window.history.back()">
                    <i class="bi bi-arrow-left"></i> Go Back
                </button>
                <button class="btn btn-outline-primary">
                    <i class="bi bi-heart"></i> Add to Wishlist
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-share"></i> Share
                </button>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="mt-5">
        <h3 class="mb-4">Related Products</h3>
        <div class="row">
            <?php foreach (array_slice($relatedProducts, 0, 4) as $relatedProduct): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card product-card border-0 shadow-sm">
                    <img src="assets/images/products/<?php echo htmlspecialchars($relatedProduct['image']); ?>" 
                         class="card-img-top product-image" 
                         alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>"
                         onerror="this.src='assets/images/placeholder.jpg'">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($relatedProduct['name']); ?></h6>
                        <p class="card-text text-muted small"><?php echo truncateText($relatedProduct['description'], 60); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h6 text-primary mb-0"><?php echo formatPrice($relatedProduct['price']); ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <div class="d-grid gap-2">
                            <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                            <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $relatedProduct['id']; ?>)">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    addToCart(productId, quantity);
}
</script>

<?php require_once 'includes/footer.php'; ?>
