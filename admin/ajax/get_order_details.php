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
    echo '<div class="alert alert-danger">Invalid order ID</div>';
    exit;
}

$order_id = (int)$_GET['id'];
$db = Database::getInstance();

// Get order details
$order_query = "
    SELECT o.*, u.username, u.email, u.phone
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
";
$stmt = $db->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    echo '<div class="alert alert-danger">Order not found</div>';
    exit;
}

// Get order items
$items_query = "
    SELECT oi.*, p.name as product_name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
";
$stmt = $db->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$items = [];
while ($item = $items_result->fetch_assoc()) {
    $item_total = $item['quantity'] * $item['price'];
    $subtotal += $item_total;
    $item['total'] = $item_total;
    $items[] = $item;
}

$tax = $subtotal * 0.10; // 10% tax
$shipping = 10.00; // Fixed shipping
$total = $subtotal + $tax + $shipping;
?>

<div class="order-details">
    <!-- Order Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h4>
            <p class="text-muted">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <div class="col-md-6 text-right">
            <span class="badge badge-<?php echo getStatusBadgeClass($order['status']); ?> badge-lg">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>Customer Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['username'] ?: 'Guest Customer'); ?></p>
                    <?php if ($order['email']): ?>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <?php endif; ?>
                    <?php if ($order['phone']): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>Shipping Address</h6>
                </div>
                <div class="card-body">
                    <?php if ($order['shipping_address']): ?>
                        <address>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </address>
                    <?php else: ?>
                        <p class="text-muted">No shipping address provided</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header">
            <h6>Order Items</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($items)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                                <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="Product" class="product-thumb me-3">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['product_name'] ?: 'Product #' . $item['product_id']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No items found for this order.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="row">
        <div class="col-md-6 offset-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Shipping:</span>
                        <span>$<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.order-details .card {
    margin-bottom: 1rem;
}

.order-details address {
    margin-bottom: 0;
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
