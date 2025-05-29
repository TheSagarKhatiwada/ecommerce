<?php
require_once 'includes/classes.php';

$productModel = new Product();
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($searchTerm) {
    $products = $productModel->searchProducts($searchTerm);
}

$pageTitle = $searchTerm ? "Search results for \"$searchTerm\"" : 'Search';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Search</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <?php if ($searchTerm): ?>
                <h1>Search Results</h1>
                <p class="text-muted">Showing results for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"</p>
            <?php else: ?>
                <h1>Search Products</h1>
                <p class="text-muted">Enter a search term to find products</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-md-end">
            <?php if ($searchTerm): ?>
                <p class="text-muted"><?php echo count($products); ?> products found</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Search Form -->
    <div class="row mb-5">
        <div class="col-lg-6 mx-auto">
            <form method="GET" action="search.php" class="d-flex">
                <input type="text" class="form-control form-control-lg me-2" name="q" 
                       placeholder="Search for products..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>
    
    <?php if ($searchTerm): ?>
        <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No products found</h4>
            <p class="text-muted">Try searching with different keywords or browse our categories.</p>
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
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
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
    <?php else: ?>
    <!-- Popular Categories for empty search -->
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">Popular Categories</h4>
        </div>
        <?php
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategories();
        foreach ($categories as $category): ?>
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
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
