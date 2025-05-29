<?php
$page_title = 'Customers';
require_once 'includes/header.php';

$db = Database::getInstance();

// Handle customer actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'toggle_status':
            if (isset($_POST['user_id'])) {
                $stmt = $db->prepare("UPDATE users SET active = !active WHERE id = ?");
                $stmt->bind_param("i", $_POST['user_id']);
                
                if ($stmt->execute()) {
                    $success_message = "Customer status updated successfully!";
                } else {
                    $error_message = "Error updating customer status: " . $db->error;
                }
            }
            break;
            
        case 'delete_customer':
            if (isset($_POST['user_id'])) {
                // Check if customer has orders
                $stmt = $db->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
                $stmt->bind_param("i", $_POST['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['order_count'] > 0) {
                    $error_message = "Cannot delete customer: They have " . $row['order_count'] . " order(s).";
                } else {
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $_POST['user_id']);
                    
                    if ($stmt->execute()) {
                        $success_message = "Customer deleted successfully!";
                    } else {
                        $error_message = "Error deleting customer: " . $db->error;
                    }
                }
            }
            break;
    }
}

// Pagination and search setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM users u $where_clause";
if (!empty($params)) {
    $stmt = $db->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $count_result = $stmt->get_result();
} else {
    $count_result = $db->query($count_query);
}
$total_customers = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_customers / $limit);

// Get customers with order statistics
$customers_query = "
    SELECT u.*, 
           COUNT(o.id) as order_count,
           SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
           MAX(o.created_at) as last_order_date,
           COALESCE(SUM(oi.quantity * oi.price), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    $where_clause
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";

$query_params = $params;
$query_params[] = $limit;
$query_params[] = $offset;
$query_types = $types . 'ii';

$stmt = $db->prepare($customers_query);
$stmt->bind_param($query_types, ...$query_params);
$stmt->execute();
$customers_result = $stmt->get_result();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Customer Management</h2>
                <p class="text-muted">Manage registered customers and their accounts</p>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex gap-3 align-items-end">
                <div class="form-group flex-grow-1">
                    <label for="search">Search Customers</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name, username, or email...">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="customers.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                Customers 
                <span class="badge badge-secondary"><?php echo $total_customers; ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if ($customers_result && $customers_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Last Order</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = $customers_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php echo strtoupper(substr($customer['username'] ?: $customer['email'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($customer['username'] ?: 'N/A'); ?></strong>
                                                <?php if ($customer['first_name'] || $customer['last_name']): ?>
                                                    <br><small class="text-muted">
                                                        <?php echo htmlspecialchars(trim($customer['first_name'] . ' ' . $customer['last_name'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo (int)$customer['order_count']; ?></span>
                                        <?php if ($customer['completed_orders'] > 0): ?>
                                            <br><small class="text-success"><?php echo (int)$customer['completed_orders']; ?> completed</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($customer['total_spent'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($customer['last_order_date']): ?>
                                            <?php echo date('M j, Y', strtotime($customer['last_order_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($customer['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($customer['active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="toggleStatus(<?php echo $customer['id']; ?>, <?php echo $customer['active'] ? 'true' : 'false'; ?>)">
                                                <i class="bi bi-toggle-<?php echo $customer['active'] ? 'on' : 'off'; ?>"></i>
                                                <?php echo $customer['active'] ? 'Disable' : 'Enable'; ?>
                                            </button>
                                            <?php if ($customer['order_count'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['username'] ?: $customer['email']); ?>')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h4>No Customers Found</h4>
                    <p><?php echo !empty($search) ? 'No customers match your search criteria.' : 'No customers have registered yet.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal modal-lg" id="customerDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="customerDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner"></div>
                    <p>Loading customer details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Forms -->
<form id="toggleStatusForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="user_id" id="toggle_user_id">
</form>

<form id="deleteCustomerForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_customer">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<style>
.customer-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}
</style>

<script>
function viewCustomer(customerId) {
    document.getElementById('customerDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner"></div>
            <p>Loading customer details...</p>
        </div>
    `;
    
    showModal('customerDetailsModal');
    
    // Fetch customer details via AJAX
    fetch('ajax/get_customer_details.php?id=' + customerId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('customerDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('customerDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error Loading Customer Details</h6>
                    <p>Unable to load customer details. Please try again.</p>
                </div>
            `;
        });
}

function toggleStatus(userId, isActive) {
    const action = isActive ? 'disable' : 'enable';
    if (confirm('Are you sure you want to ' + action + ' this customer?')) {
        document.getElementById('toggle_user_id').value = userId;
        document.getElementById('toggleStatusForm').submit();
    }
}

function deleteCustomer(userId, customerName) {
    if (confirm('Are you sure you want to delete the customer "' + customerName + '"?\n\nThis action cannot be undone.')) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('deleteCustomerForm').submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
