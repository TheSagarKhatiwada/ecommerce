<?php
// Include session configuration from main classes file
require_once __DIR__ . '/../../includes/classes.php';

// Simple authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Access denied</div>';
    exit;
}

require_once __DIR__ . '/../../bootstrap.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid customer ID</div>';
    exit;
}

$user_id = (int)$_GET['id'];
$db = Database::getInstance();

// Get customer details
$customer_query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($customer_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    echo '<div class="alert alert-danger">Customer not found</div>';
    exit;
}

// Get customer's orders
$orders_query = "
    SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.quantity * oi.price) as order_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 10
";
$stmt = $db->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

// Get customer statistics
$stats_query = "
    SELECT 
        COUNT(o.id) as total_orders,
        SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        COALESCE(SUM(oi.quantity * oi.price), 0) as total_spent,
        AVG(oi.quantity * oi.price) as avg_order_value,
        MAX(o.created_at) as last_order_date,
        MIN(o.created_at) as first_order_date
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
";
$stmt = $db->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<div class="customer-details">
    <!-- Customer Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="customer-avatar-large">
                    <?php echo strtoupper(substr($customer['username'] ?: $customer['email'], 0, 1)); ?>
                </div>
                <div>
                    <h4><?php echo htmlspecialchars($customer['username'] ?: 'N/A'); ?></h4>
                    <?php if ($customer['first_name'] || $customer['last_name']): ?>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars(trim($customer['first_name'] . ' ' . $customer['last_name'])); ?></p>
                    <?php endif; ?>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($customer['email']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-<?php echo $customer['active'] ? 'success' : 'secondary'; ?> badge-lg">
                <?php echo $customer['active'] ? 'Active' : 'Inactive'; ?>
            </span>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>Personal Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td><?php echo htmlspecialchars($customer['username'] ?: 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>First Name:</strong></td>
                            <td><?php echo htmlspecialchars($customer['first_name'] ?: 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Name:</strong></td>
                            <td><?php echo htmlspecialchars($customer['last_name'] ?: 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?: 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Joined:</strong></td>
                            <td><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>Order Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-primary"><?php echo (int)$stats['total_orders']; ?></h4>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-success">$<?php echo number_format($stats['total_spent'], 2); ?></h4>
                                <small class="text-muted">Total Spent</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-info"><?php echo (int)$stats['completed_orders']; ?></h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-warning">$<?php echo number_format($stats['avg_order_value'] ?: 0, 2); ?></h4>
                                <small class="text-muted">Avg Order</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h6>Recent Orders</h6>
        </div>
        <div class="card-body">
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-light"><?php echo (int)$order['item_count']; ?> items</span>
                                    </td>
                                    <td>$<?php echo number_format($order['order_total'] ?: 0, 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo getStatusBadgeClass($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($stats['total_orders'] > 10): ?>
                    <div class="text-center mt-3">
                        <a href="orders.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary btn-sm">
                            View All Orders (<?php echo (int)$stats['total_orders']; ?>)
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-3">
                    <i class="bi bi-cart text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No orders found for this customer.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.customer-avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
}

.stat-item {
    padding: 1rem 0;
}

.stat-item h4 {
    margin-bottom: 0.25rem;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.customer-details .card {
    margin-bottom: 1rem;
}
</style>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>
