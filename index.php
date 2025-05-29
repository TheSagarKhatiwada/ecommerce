<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

$productModel = new Product();
$categoryModel = new Category();

$featuredProducts = $productModel->getFeaturedProducts(8);
$categories = $categoryModel->getAllCategories();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to <?php echo htmlspecialchars($company['name']); ?></h1>
                <p class="lead mb-4">Discover amazing products at unbeatable prices. Shop with confidence and enjoy fast, reliable delivery.</p>
                <a href="#featured-products" class="btn btn-light btn-lg">Shop Now</a>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-cart-check-fill" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Shop by Category</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
            <div class="col-lg-2 col-md-4 col-6 mb-4">
                <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="card category-card border-0 shadow-sm h-100 text-center">
                        <div class="card-body py-4">
                            <i class="bi bi-grid-3x3-gap-fill text-primary mb-3" style="font-size: 2rem;"></i>
                            <h6 class="card-title text-dark"><?php echo htmlspecialchars($category['name']); ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured-products" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Featured Products</h2>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
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
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <div class="d-grid gap-2">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                            <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-item">
                    <i class="bi bi-truck text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5>Free Shipping</h5>
                    <p class="text-muted">Free shipping on orders over $50</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-item">
                    <i class="bi bi-shield-check text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5>Secure Payment</h5>
                    <p class="text-muted">100% secure payment processing</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-item">
                    <i class="bi bi-arrow-clockwise text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5>Easy Returns</h5>
                    <p class="text-muted">30-day return policy</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-item">
                    <i class="bi bi-headset text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Round-the-clock customer support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
