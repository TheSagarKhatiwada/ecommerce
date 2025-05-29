<?php
$page_title = 'Products';
require_once 'includes/header.php';

$db = new Database();
$categoryModel = new Category();

// Handle actions
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = 'success';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $sql = "INSERT INTO products (name, description, price, category_id, image, featured, stock_quantity) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $params = [
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['category_id'],
                        $_POST['image'] ?: 'placeholder.svg',
                        isset($_POST['featured']) ? 1 : 0,
                        $_POST['stock_quantity'] ?? 0
                    ];
                    $db->execute($sql, $params);
                    $message = 'Product added successfully!';
                    $action = 'list';
                } catch (Exception $e) {
                    $message = 'Error adding product: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'edit':
                try {
                    $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, featured = ?, stock_quantity = ? WHERE id = ?";
                    $params = [
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['category_id'],
                        $_POST['image'] ?: 'placeholder.svg',
                        isset($_POST['featured']) ? 1 : 0,
                        $_POST['stock_quantity'] ?? 0,
                        $_POST['id']
                    ];
                    $db->execute($sql, $params);
                    $message = 'Product updated successfully!';
                    $action = 'list';
                } catch (Exception $e) {
                    $message = 'Error updating product: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Handle delete
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $db->execute("DELETE FROM products WHERE id = ?", [$_GET['id']]);
        $message = 'Product deleted successfully!';
        $action = 'list';
    } catch (Exception $e) {
        $message = 'Error deleting product: ' . $e->getMessage();
        $messageType = 'danger';
        $action = 'list';
    }
}

// Get data based on action
if ($action === 'list') {
    $page = $_GET['page'] ?? 1;
    $search = $_GET['search'] ?? '';
    $products = $db->getProductsPaginated($page, 20, $search);
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $product = $db->fetch("SELECT * FROM products WHERE id = ?", [$_GET['id']]);
    if (!$product) {
        $action = 'list';
        $message = 'Product not found!';
        $messageType = 'danger';
    }
}

$categories = $categoryModel->getAllCategories();
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1rem; border-radius: var(--border-radius); background: rgba(var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color-rgb), 0.1); color: var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color); border: 1px solid rgba(var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color-rgb), 0.2);">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Products List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Products Management</h3>
                <a href="?action=add" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Add Product
                </a>
            </div>
            
            <!-- Search -->
            <div style="margin-top: 1rem;">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="action" value="list">
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="max-width: 300px;">
                    <button type="submit" class="btn btn-outline">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if ($search): ?>
                        <a href="?action=list" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="card-body" style="padding: 0;">
            <?php if (!empty($products['data'])): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products['data'] as $product): ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 600; color: var(--primary-color);">#<?php echo $product['id']; ?></span>
                                    </td>
                                    <td>
                                        <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--border-radius-sm);"
                                             onerror="this.src='../assets/images/placeholder.svg'">
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></div>
                                            <div style="font-size: 0.75rem; color: var(--light-text);"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600;"><?php echo formatPrice($product['price']); ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500;"><?php echo $product['stock_quantity'] ?? 0; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge badge-warning">Featured</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-outline btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $product['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirmDelete('Are you sure you want to delete this product?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($products['total_pages'] > 1): ?>
                    <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="color: var(--light-text); font-size: 0.875rem;">
                                Showing <?php echo ($products['page'] - 1) * $products['per_page'] + 1; ?> to 
                                <?php echo min($products['page'] * $products['per_page'], $products['total']); ?> of 
                                <?php echo $products['total']; ?> products
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($products['page'] > 1): ?>
                                    <a href="?action=list&page=<?php echo $products['page'] - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-outline btn-sm">Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $products['page'] - 2); $i <= min($products['total_pages'], $products['page'] + 2); $i++): ?>
                                    <a href="?action=list&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="btn <?php echo $i === $products['page'] ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($products['page'] < $products['total_pages']): ?>
                                    <a href="?action=list&page=<?php echo $products['page'] + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-outline btn-sm">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="padding: 3rem; text-align: center; color: var(--light-text);">
                    <i class="bi bi-box" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3>No products found</h3>
                    <p>Start by adding your first product</p>
                    <a href="?action=add" class="btn btn-primary">Add Product</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Product Form -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title"><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h3>
                <a href="?action=list" class="btn btn-outline">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <form method="POST" id="productForm">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid cols-2">
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo $action === 'edit' ? htmlspecialchars($product['name']) : ''; ?>" 
                               required placeholder="Enter product name">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($action === 'edit' && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price" class="form-label">Price *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                               value="<?php echo $action === 'edit' ? $product['price'] : ''; ?>" 
                               required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0"
                               value="<?php echo $action === 'edit' ? ($product['stock_quantity'] ?? 0) : '0'; ?>" 
                               placeholder="0">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Enter product description"><?php echo $action === 'edit' ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    </div>
                      <div class="form-group">
                        <label for="image" class="form-label">Product Image</label>
                        <div class="image-upload-container">
                            <!-- Current Image Preview -->
                            <div class="current-image-preview">
                                <img id="image-preview" 
                                     src="../assets/images/<?php echo $action === 'edit' && $product['image'] ? htmlspecialchars($product['image']) : 'placeholder.svg'; ?>" 
                                     alt="Product Image" 
                                     style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0;">
                            </div>
                            
                            <!-- File Upload -->
                            <div style="margin-top: 1rem;">
                                <input type="file" id="image-file" name="image_file" class="form-control" 
                                       accept="image/*" onchange="handleImageUpload(this)">
                                <small class="text-muted">Upload a new image (JPEG, PNG, GIF, WebP - Max 5MB)</small>
                            </div>
                            
                            <!-- Hidden field for image path -->
                            <input type="hidden" id="image" name="image" 
                                   value="<?php echo $action === 'edit' ? htmlspecialchars($product['image']) : ''; ?>">
                            
                            <!-- Upload Progress -->
                            <div id="upload-progress" style="display: none; margin-top: 1rem;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Options</label>
                        <div style="margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                                <input type="checkbox" name="featured" value="1" 
                                       <?php echo ($action === 'edit' && $product['featured']) ? 'checked' : ''; ?>>
                                <span>Featured Product</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i>
                            <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                        </button>
                        <a href="?action=list" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function handleImageUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('image', file);
        
        // Show progress
        const progressDiv = document.getElementById('upload-progress');
        const progressBar = progressDiv.querySelector('.progress-bar');
        progressDiv.style.display = 'block';
        
        // Upload file
        fetch('ajax/upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            progressDiv.style.display = 'none';
            
            if (data.success) {
                // Update hidden input with new filename
                document.getElementById('image').value = data.filename;
                
                // Update preview image
                const preview = document.getElementById('image-preview');
                preview.src = '../assets/images/' + data.filename;
                
                // Show success message
                showAlert('Image uploaded successfully!', 'success');
            } else {
                showAlert('Upload failed: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            progressDiv.style.display = 'none';
            showAlert('Upload failed: ' + error.message, 'danger');
        });
    }
}

function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible`;
    alert.innerHTML = `
        <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
        ${message}
    `;
    
    // Insert at top of container
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alert, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.style.display = 'none';
        }
    }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>
