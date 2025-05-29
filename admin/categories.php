<?php
$page_title = 'Categories';
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
                    $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
                    $params = [
                        $_POST['name'],
                        $_POST['description']
                    ];
                    $db->execute($sql, $params);
                    $message = 'Category added successfully!';
                    $action = 'list';
                } catch (Exception $e) {
                    $message = 'Error adding category: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'edit':
                try {
                    $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
                    $params = [
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['id']
                    ];
                    $db->execute($sql, $params);
                    $message = 'Category updated successfully!';
                    $action = 'list';
                } catch (Exception $e) {
                    $message = 'Error updating category: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Handle delete
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        // Check if category has products
        $productCount = $db->fetch("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$_GET['id']]);
        if ($productCount['count'] > 0) {
            $message = 'Cannot delete category. It contains ' . $productCount['count'] . ' products.';
            $messageType = 'danger';
        } else {
            $db->execute("DELETE FROM categories WHERE id = ?", [$_GET['id']]);
            $message = 'Category deleted successfully!';
        }
        $action = 'list';
    } catch (Exception $e) {
        $message = 'Error deleting category: ' . $e->getMessage();
        $messageType = 'danger';
        $action = 'list';
    }
}

// Get data based on action
if ($action === 'list') {
    $page = $_GET['page'] ?? 1;
    $search = $_GET['search'] ?? '';
    
    // Build search query
    $whereClause = '';
    $params = [];
    if (!empty($search)) {
        $whereClause = "WHERE c.name LIKE ? OR c.description LIKE ?";
        $params = ["%$search%", "%$search%"];
    }
    
    // Get categories with product count
    $sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            $whereClause 
            GROUP BY c.id 
            ORDER BY c.name";
    
    $categories = $db->fetchAll($sql, $params);
    
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $category = $db->fetch("SELECT * FROM categories WHERE id = ?", [$_GET['id']]);
    if (!$category) {
        $action = 'list';
        $message = 'Category not found!';
        $messageType = 'danger';
    }
}
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1rem; border-radius: var(--border-radius); background: rgba(var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color-rgb), 0.1); color: var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color); border: 1px solid rgba(var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color-rgb), 0.2);">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Categories List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Categories Management</h3>
                <a href="?action=add" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Add Category
                </a>
            </div>
            
            <!-- Search -->
            <div style="margin-top: 1rem;">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="action" value="list">
                    <input type="text" name="search" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="max-width: 300px;">
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
            <?php if (!empty($categories)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 600; color: var(--primary-color);">#<?php echo $category['id']; ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($category['name']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color: var(--light-text);">
                                            <?php echo htmlspecialchars(truncateText($category['description'], 80)); ?>
                                        </div>
                                    </td>                                    <td>
                                        <span class="badge <?php echo $category['product_count'] > 0 ? 'badge-info' : 'badge-secondary'; ?>">
                                            <?php echo $category['product_count']; ?> products
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: var(--light-text); font-size: 0.875rem;">
                                            <?php echo isset($category['created_at']) ? date('M d, Y', strtotime($category['created_at'])) : 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-outline btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($category['product_count'] == 0): ?>
                                            <a href="?action=delete&id=<?php echo $category['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <button class="btn btn-danger btn-sm" disabled title="Cannot delete category with products">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                            <a href="../category.php?id=<?php echo $category['id']; ?>" class="btn btn-outline btn-sm" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>            <?php else: ?>
                <div style="padding: 3rem; text-align: center;">
                    <div style="font-size: 3rem; color: var(--light-text); margin-bottom: 1rem;">
                        <i class="bi bi-tags"></i>
                    </div>
                    <h3 style="color: var(--dark-text); margin-bottom: 0.5rem;">No categories found</h3>
                    <p style="color: var(--light-text); margin-bottom: 2rem;">Start by creating your first category to organize your products.</p>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Category
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($action === 'add'): ?>
    <!-- Add Category Form -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Add New Category</h3>
                <a href="?action=list" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Categories
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid cols-2">
                    <div class="form-group">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="100" placeholder="Enter category name">
                        <small style="color: var(--light-text); font-size: 0.75rem;">Enter a unique name for this category</small>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" maxlength="500" placeholder="Describe what products belong in this category"></textarea>
                        <small style="color: var(--light-text); font-size: 0.75rem;">Describe what products belong in this category</small>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Add Category
                        </button>
                        <a href="?action=list" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($action === 'edit' && isset($category)): ?>
    <!-- Edit Category Form -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Edit Category</h3>
                <a href="?action=list" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Categories
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                
                <div class="form-grid cols-2">
                    <div class="form-group">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($category['name']); ?>" required maxlength="100" placeholder="Enter category name">
                        <small style="color: var(--light-text); font-size: 0.75rem;">Enter a unique name for this category</small>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" maxlength="500" placeholder="Describe what products belong in this category"><?php echo htmlspecialchars($category['description']); ?></textarea>
                        <small style="color: var(--light-text); font-size: 0.75rem;">Describe what products belong in this category</small>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Category
                        </button>
                        <a href="?action=list" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>