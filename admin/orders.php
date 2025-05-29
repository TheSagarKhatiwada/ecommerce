<?php
$page_title = 'Orders';
require_once 'includes/header.php';

$db = new Database();

// Handle order status updates
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['order_id'], $_POST['status'])) {
        try {
            $db->execute("UPDATE orders SET status = ? WHERE id = ?", [$_POST['status'], $_POST['order_id']]);
            $message = "Order status updated successfully!";
        } catch (Exception $e) {
            $message = "Error updating order status: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = $_GET['search'] ?? '';

// Build where clause
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM orders o $where_clause";
$total_result = $db->fetch($count_query, $params);
$total_orders = $total_result['total'];
$total_pages = ceil($total_orders / $limit);

// Get orders with pagination
$orders_query = "
    SELECT o.*, 
           o.customer_name as username,
           o.customer_email as email,
           COUNT(oi.id) as item_count,
           o.total_amount as order_total
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    $where_clause
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT $offset, $limit
";

$query_params = $params;

$orders = $db->fetchAll($orders_query, $query_params);

// Get order statuses for filter
$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1rem; border-radius: var(--border-radius); background: rgba(var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color-rgb), 0.1); color: var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color); border: 1px solid rgba(var(--<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>-color-rgb), 0.2);">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Orders List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Orders Management</h3>
            <span class="badge badge-info"><?php echo $total_orders; ?> total orders</span>
        </div>
        
        <!-- Search and Filters -->
        <div style="margin-top: 1rem;">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="max-width: 300px;">
                <select name="status" class="form-control" style="max-width: 150px;">
                    <option value="">All Status</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                            <?php echo ucfirst($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline">
                    <i class="bi bi-search"></i>
                </button>
                <?php if ($search || $status_filter): ?>
                    <a href="orders.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($orders)): ?>
            <div class="table-container">
                <table class="table">                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary-color);">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($order['username'] ?: 'Guest'); ?></div>
                                        <?php if ($order['email']): ?>
                                            <div style="font-size: 0.75rem; color: var(--light-text);"><?php echo htmlspecialchars($order['email']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo (int)$order['item_count']; ?> items</span>
                                </td>
                                <td>
                                    <span style="font-weight: 600;"><?php echo formatPrice($order['order_total'] ?: 0); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo getStatusBadgeClass($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: var(--light-text); font-size: 0.875rem;">
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        <br>
                                        <small><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline btn-sm" 
                                                onclick="viewOrder(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm" 
                                                onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="color: var(--light-text); font-size: 0.875rem;">
                            Showing <?php echo ($page - 1) * $limit + 1; ?> to 
                            <?php echo min($page * $limit, $total_orders); ?> of
                            <?php echo $total_orders; ?> orders
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="btn btn-outline btn-sm">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="btn btn-outline btn-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>        <?php else: ?>
            <div style="padding: 3rem; text-align: center; color: var(--light-text);">
                <i class="bi bi-cart" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No orders found</h3>
                <p>No orders match your current filter criteria</p>
                <?php if ($search || $status_filter): ?>
                    <a href="orders.php" class="btn btn-primary">Clear Filters</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal modal-lg" id="orderDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner"></div>
                    <p>Loading order details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal" id="updateStatusModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" id="status_order_id">
                    
                    <div class="form-group">
                        <label for="new_status">Select New Status</label>
                        <select name="status" id="new_status" class="form-control" required>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functionality
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside or on close button
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        hideModal(e.target.id);
    }
    if (e.target.classList.contains('modal-close') || e.target.hasAttribute('data-dismiss')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            hideModal(modal.id);
        }
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const visibleModal = document.querySelector('.modal.show');
        if (visibleModal) {
            hideModal(visibleModal.id);
        }
    }
});

function viewOrder(orderId) {
    document.getElementById('orderDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner"></div>
            <p>Loading order details...</p>
        </div>
    `;
    
    showModal('orderDetailsModal');
    
    // Fetch order details via AJAX
    fetch('ajax/get_order_details.php?id=' + orderId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('orderDetailsContent').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error Loading Order Details</h6>
                    <p>Unable to load order details. Please try again.</p>
                    <button type="button" class="btn btn-outline btn-sm" onclick="viewOrder(${orderId})">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </div>
            `;
        });
}

function updateStatus(orderId, currentStatus) {
    document.getElementById('status_order_id').value = orderId;
    document.getElementById('new_status').value = currentStatus;
    showModal('updateStatusModal');
}
</script>

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

require_once 'includes/footer.php';
?>
