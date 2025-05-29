<?php
// Include session configuration from main classes file
require_once __DIR__ . '/../../includes/classes.php';
require_once '../../bootstrap.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = new Database();

// Get export parameters
$type = $_GET['type'] ?? 'csv';
$report = $_GET['report'] ?? 'sales';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

try {
    $data = [];
    $filename = '';
    
    switch ($report) {        case 'sales':
            $query = "SELECT 
                        DATE(o.created_at) as date,
                        COUNT(*) as orders_count,
                        SUM(o.total_amount) as total_sales,
                        AVG(o.total_amount) as avg_order_value
                      FROM orders o 
                      WHERE DATE(o.created_at) BETWEEN ? AND ?
                      GROUP BY DATE(o.created_at)
                      ORDER BY date DESC";
            
            $data = $db->fetchAll($query, [$start_date, $end_date]);
            $filename = "sales_report_{$start_date}_to_{$end_date}";
            break;
              case 'products':
            $query = "SELECT 
                        p.name,
                        p.price,
                        p.stock_quantity,
                        c.name as category,
                        COALESCE(SUM(oi.quantity), 0) as total_sold,
                        COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN order_items oi ON p.id = oi.product_id
                      LEFT JOIN orders o ON oi.order_id = o.id
                      WHERE o.created_at IS NULL OR DATE(o.created_at) BETWEEN ? AND ?
                      GROUP BY p.id, p.name, p.price, p.stock_quantity, c.name
                      ORDER BY total_sold DESC";
            
            $data = $db->fetchAll($query, [$start_date, $end_date]);
            $filename = "products_report_{$start_date}_to_{$end_date}";
            break;        case 'customers':
            $query = "SELECT 
                        o.customer_name,
                        o.customer_email,
                        o.customer_phone,
                        COUNT(o.id) as total_orders,
                        SUM(o.total_amount) as total_spent,
                        MAX(o.created_at) as last_order_date,
                        MIN(o.created_at) as first_order_date
                      FROM orders o
                      WHERE DATE(o.created_at) BETWEEN ? AND ?
                      GROUP BY o.customer_email, o.customer_name, o.customer_phone
                      ORDER BY total_spent DESC";
            
            $data = $db->fetchAll($query, [$start_date, $end_date]);
            $filename = "customers_report_{$start_date}_to_{$end_date}";
            break;        case 'orders':
            $query = "SELECT 
                        o.id,
                        o.customer_name,
                        o.customer_email,
                        o.customer_phone,
                        o.total_amount,
                        o.status,
                        o.created_at,
                        COUNT(oi.id) as items_count
                      FROM orders o
                      LEFT JOIN order_items oi ON o.id = oi.order_id
                      WHERE DATE(o.created_at) BETWEEN ? AND ?
                      GROUP BY o.id
                      ORDER BY o.created_at DESC";
            
            $data = $db->fetchAll($query, [$start_date, $end_date]);
            $filename = "orders_report_{$start_date}_to_{$end_date}";
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    if ($type === 'csv') {
        exportCSV($data, $filename);
    } elseif ($type === 'pdf') {
        exportPDF($data, $filename, $report);
    } else {
        throw new Exception('Invalid export type');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function exportCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

function exportPDF($data, $filename, $reportType) {
    // Simple HTML to PDF conversion (basic implementation)
    // In a real application, you might want to use a library like TCPDF or DomPDF
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    // Start output buffering
    ob_start();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>" . ucfirst($reportType) . " Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <h1>" . ucfirst($reportType) . " Report</h1>
        <p><strong>Generated:</strong> " . date('Y-m-d H:i:s') . "</p>";
    
    if (!empty($data)) {
        echo "<table>";
        echo "<tr>";
        foreach (array_keys($data[0]) as $header) {
            echo "<th>" . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . "</th>";
        }
        echo "</tr>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data available for the selected date range.</p>";
    }
    
    echo "<div class='footer'>
            <p>Generated by eCommerce Admin Panel</p>
          </div>
    </body>
    </html>";
    
    $html = ob_get_clean();
    
    // For a simple PDF, we'll output HTML and let the browser handle it
    // In production, you'd want to use a proper PDF library
    header('Content-Type: text/html');
    header('Content-Disposition: inline; filename="' . $filename . '.html"');
    
    echo $html;
    exit;
}
?>
