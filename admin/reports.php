<?php
$page_title = 'Reports & Analytics';
require_once 'includes/header.php';

$db = new Database();

// Date range filter
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Sales summary
$sales_summary = $db->fetch("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        COUNT(DISTINCT customer_email) as unique_customers
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? 
    AND status != 'cancelled'
", [$start_date, $end_date]);

// Sales by status
$sales_by_status = $db->fetchAll("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
    ORDER BY count DESC
", [$start_date, $end_date]);

// Top selling products
$top_products = $db->fetchAll("
    SELECT 
        p.name,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.status != 'cancelled'
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 10
", [$start_date, $end_date]);

// Daily sales chart data
$daily_sales = $db->fetchAll("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
", [$start_date, $end_date]);

// Category performance
$category_performance = $db->fetchAll("
    SELECT 
        c.name as category,
        COUNT(oi.id) as items_sold,
        SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.status != 'cancelled'
    GROUP BY c.id, c.name
    ORDER BY revenue DESC
", [$start_date, $end_date]);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Reports & Analytics</h2>
                <p class="text-muted">Sales performance and business insights</p>
            </div>
            <div class="d-flex gap-2">                <div class="btn-group">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> Export Report
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportReport('sales', 'csv')">Sales Report (CSV)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('sales', 'pdf')">Sales Report (PDF)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('products', 'csv')">Products Report (CSV)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('products', 'pdf')">Products Report (PDF)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('customers', 'csv')">Customers Report (CSV)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('customers', 'pdf')">Customers Report (PDF)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('orders', 'csv')">Orders Report (CSV)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('orders', 'pdf')">Orders Report (PDF)</a></li>
                    </ul>
                </div>
                <button class="btn btn-primary" onclick="printReport()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex gap-3 align-items-end">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Update Report</button>
                    <button type="button" class="btn btn-secondary" onclick="setDateRange('today')">Today</button>
                    <button type="button" class="btn btn-secondary" onclick="setDateRange('week')">This Week</button>
                    <button type="button" class="btn btn-secondary" onclick="setDateRange('month')">This Month</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Revenue</div>
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo formatPrice($sales_summary['total_revenue'] ?? 0); ?></div>
            <div class="stat-change">
                <?php echo number_format($sales_summary['total_orders'] ?? 0); ?> orders
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-title">Average Order Value</div>
                <div class="stat-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo formatPrice($sales_summary['avg_order_value'] ?? 0); ?></div>
            <div class="stat-change">
                Per order average
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-header">
                <div class="stat-title">Unique Customers</div>
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($sales_summary['unique_customers'] ?? 0); ?></div>
            <div class="stat-change">
                Customer base
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-title">Conversion Rate</div>
                <div class="stat-icon">
                    <i class="bi bi-percent"></i>
                </div>
            </div>
            <div class="stat-value"><?php 
                $conversion = $sales_summary['unique_customers'] > 0 ? 
                    ($sales_summary['total_orders'] / $sales_summary['unique_customers']) * 100 : 0;
                echo number_format($conversion, 1) . '%';
            ?></div>
            <div class="stat-change">
                Orders per customer
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daily Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Order Status Breakdown -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Orders by Status</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($sales_by_status as $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-<?php echo getStatusBadgeClass($status['status']); ?>">
                                <?php echo ucfirst($status['status']); ?>
                            </span>
                            <div class="text-end">
                                <strong><?php echo $status['count']; ?></strong>
                                <small class="text-muted">(<?php echo formatPrice($status['revenue']); ?>)</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($top_products)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><span class="badge badge-info"><?php echo $product['total_sold']; ?></span></td>
                                            <td><strong><?php echo formatPrice($product['revenue']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-box"></i>
                            <p>No product sales data available for this period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Category Performance -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Category Performance</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($category_performance)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Items</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_performance as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['category']); ?></td>
                                            <td><span class="badge badge-primary"><?php echo $category['items_sold']; ?></span></td>
                                            <td><strong><?php echo formatPrice($category['revenue']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-grid"></i>
                            <p>No category sales data available for this period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesData = <?php echo json_encode($daily_sales); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesData.map(item => new Date(item.date).toLocaleDateString()),
        datasets: [{
            label: 'Revenue',
            data: salesData.map(item => parseFloat(item.revenue)),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
        }, {
            label: 'Orders',
            data: salesData.map(item => parseInt(item.orders)),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Orders'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

function setDateRange(period) {
    const today = new Date();
    let startDate, endDate = today.toISOString().split('T')[0];
    
    switch(period) {
        case 'today':
            startDate = endDate;
            break;
        case 'week':
            const weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
            startDate = weekStart.toISOString().split('T')[0];
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            break;
    }
    
    document.getElementById('start_date').value = startDate;
    document.getElementById('end_date').value = endDate;
    document.querySelector('form').submit();
}

function exportReport(reportType = 'sales', format = 'csv') {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!startDate || !endDate) {
        showAlert('Please select both start and end dates.', 'warning');
        return;
    }
    
    const url = `ajax/export_report.php?type=${format}&report=${reportType}&start_date=${startDate}&end_date=${endDate}`;
    window.open(url, '_blank');
}

function printReport() {
    window.print();
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
