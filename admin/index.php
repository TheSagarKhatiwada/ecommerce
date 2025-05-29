<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

$db = new Database();
$stats = $db->getDashboardStats();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Total Products</div>
            <div class="stat-icon">
                <i class="bi bi-box"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
        <div class="stat-change positive">
            <i class="bi bi-arrow-up"></i> Active inventory
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-header">
            <div class="stat-title">Total Orders</div>
            <div class="stat-icon">
                <i class="bi bi-cart"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
        <div class="stat-change positive">
            <i class="bi bi-arrow-up"></i> <?php echo $stats['recent_orders']; ?> this month
        </div>
    </div>
    
    <div class="stat-card info">
        <div class="stat-header">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
        <div class="stat-change positive">
            <i class="bi bi-arrow-up"></i> All time
        </div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-header">
            <div class="stat-title">Recent Orders</div>
            <div class="stat-icon">
                <i class="bi bi-clock"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['recent_orders']); ?></div>
        <div class="stat-change">
            <i class="bi bi-calendar"></i> Last 30 days
        </div>
    </div>
</div>

<!-- Charts and Recent Activity -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Products by Category -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Products by Category</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($stats['products_by_category'])): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($stats['products_by_category'] as $category): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 500;"><?php echo htmlspecialchars($category['name']); ?></span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="background: var(--light-bg); border-radius: 9999px; width: 100px; height: 8px; overflow: hidden;">
                                    <div style="background: var(--primary-color); height: 100%; width: <?php echo min(100, ($category['count'] / max(1, $stats['total_products'])) * 100); ?>%;"></div>
                                </div>
                                <span style="font-weight: 600; color: var(--primary-color); min-width: 30px;"><?php echo $category['count']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--light-text); text-align: center; padding: 2rem;">No categories found</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="products.php?action=add" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Add New Product
                </a>
                <a href="categories.php?action=add" class="btn btn-success">
                    <i class="bi bi-tag"></i>
                    Add New Category
                </a>
                <a href="orders.php" class="btn btn-info">
                    <i class="bi bi-list-ul"></i>
                    View All Orders
                </a>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="bi bi-gear"></i>
                    Site Settings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Orders</h3>
        <a href="orders.php" class="btn btn-outline btn-sm ml-auto">View All</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($stats['recent_orders_list'])): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_orders_list'] as $order): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary-color);">#<?php echo $order['id']; ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--light-text);"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600;"><?php echo formatPrice($order['total_amount']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status = $order['status'] ?? 'pending';
                                    $statusClass = 'secondary';
                                    switch($status) {
                                        case 'completed': $statusClass = 'success'; break;
                                        case 'processing': $statusClass = 'info'; break;
                                        case 'cancelled': $statusClass = 'danger'; break;
                                        case 'pending': $statusClass = 'warning'; break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                                <td>
                                    <div style="font-size: 0.875rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--light-text);"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
                                </td>
                                <td>
                                    <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: var(--light-text);">
                <i class="bi bi-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>No orders yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
