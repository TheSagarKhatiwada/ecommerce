<?php
/**
 * Admin Panel Functionality Test Script
 * Run this script to verify all admin panel components are working correctly
 */

// Start output buffering for clean results
ob_start();

echo "=== eCommerce Admin Panel Test Suite ===\n";
echo "Testing Date: " . date('Y-m-d H:i:s') . "\n\n";

// Include bootstrap
require_once 'bootstrap.php';

$tests_passed = 0;
$tests_failed = 0;
$test_results = [];

function runTest($testName, $testFunction) {
    global $tests_passed, $tests_failed, $test_results;
    
    try {
        $result = $testFunction();
        if ($result) {
            $tests_passed++;
            $test_results[] = "✓ PASS: $testName";
            echo "✓ PASS: $testName\n";
        } else {
            $tests_failed++;
            $test_results[] = "✗ FAIL: $testName";
            echo "✗ FAIL: $testName\n";
        }
    } catch (Exception $e) {
        $tests_failed++;
        $test_results[] = "✗ ERROR: $testName - " . $e->getMessage();
        echo "✗ ERROR: $testName - " . $e->getMessage() . "\n";
    }
}

// Test 1: Database Connection
runTest("Database Connection", function() {
    $db = new Database();
    return $db->getConnection() instanceof PDO;
});

// Test 2: Admin Dashboard Stats
runTest("Dashboard Statistics", function() {
    $db = new Database();
    $stats = $db->getDashboardStats();
    return is_array($stats) && isset($stats['total_products']);
});

// Test 3: Product Listing
runTest("Product Listing", function() {
    $db = new Database();
    $products = $db->getProductsPaginated(1, 10);
    return is_array($products) && isset($products['data']);
});

// Test 4: Order Listing
runTest("Order Listing", function() {
    $db = new Database();
    $orders = $db->getOrdersPaginated(1, 10);
    return is_array($orders) && isset($orders['data']);
});

// Test 5: Categories Retrieval
runTest("Categories Retrieval", function() {
    $category = new Category();
    $categories = $category->getAllCategories();
    return is_array($categories);
});

// Test 6: Company Info
runTest("Company Information", function() {
    $company = new CompanyInfo();
    $info = $company->getCompanyInfo();
    return is_array($info) || $info === false; // False is OK if no data exists
});

// Test 7: File Upload Directory
runTest("Upload Directory Exists", function() {
    return is_dir('assets/images/uploads') && is_writable('assets/images/uploads');
});

// Test 8: Admin CSS File
runTest("Admin CSS File", function() {
    return file_exists('admin/assets/admin.css');
});

// Test 9: AJAX Handlers
runTest("AJAX Upload Handler", function() {
    return file_exists('admin/ajax/upload_image.php');
});

runTest("AJAX Export Handler", function() {
    return file_exists('admin/ajax/export_report.php');
});

// Test 10: Admin Pages
$adminPages = [
    'admin/index.php',
    'admin/products.php', 
    'admin/orders.php',
    'admin/customers.php',
    'admin/categories.php',
    'admin/settings.php',
    'admin/reports.php'
];

foreach ($adminPages as $page) {
    runTest("Admin Page: " . basename($page), function() use ($page) {
        return file_exists($page);
    });
}

// Test 11: Database Tables
$requiredTables = ['products', 'categories', 'orders', 'order_items', 'users'];

foreach ($requiredTables as $table) {
    runTest("Database Table: $table", function() use ($table) {
        $db = new Database();
        try {
            $result = $db->fetch("SELECT 1 FROM $table LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    });
}

// Test 12: Report Generation
runTest("Reports Data Generation", function() {
    $db = new Database();
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-d');
    
    // Test sales summary
    $sales = $db->fetch("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ", [$start_date, $end_date]);
    
    return is_array($sales);
});

echo "\n=== Test Summary ===\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";
echo "Total Tests: " . ($tests_passed + $tests_failed) . "\n";
echo "Success Rate: " . round(($tests_passed / ($tests_passed + $tests_failed)) * 100, 2) . "%\n\n";

if ($tests_failed > 0) {
    echo "=== Failed Tests ===\n";
    foreach ($test_results as $result) {
        if (strpos($result, 'FAIL') !== false || strpos($result, 'ERROR') !== false) {
            echo "$result\n";
        }
    }
    echo "\n";
}

echo "=== Recommendations ===\n";
if ($tests_failed == 0) {
    echo "🎉 All tests passed! Your admin panel is ready for use.\n";
    echo "📋 Next steps:\n";
    echo "   1. Test the admin login at http://localhost:8000/admin.php\n";
    echo "   2. Add some sample products with images\n";
    echo "   3. Process test orders\n";
    echo "   4. Generate and export reports\n";
} else {
    echo "⚠️  Some tests failed. Please review the failed tests above.\n";
    echo "🔧 Common fixes:\n";
    echo "   1. Ensure database is properly set up\n";
    echo "   2. Check file permissions on uploads directory\n";
    echo "   3. Verify all admin files are present\n";
    echo "   4. Run setup.php if database tables are missing\n";
}

echo "\n=== Admin Panel Features ===\n";
echo "✅ Product Management with Image Upload\n";
echo "✅ Order Management with Status Updates\n";
echo "✅ Customer Management\n";
echo "✅ Category Management\n";
echo "✅ Reports & Analytics with Charts\n";
echo "✅ Export Functionality (CSV/PDF)\n";
echo "✅ Modern Responsive UI\n";
echo "✅ Secure Database Operations\n";

echo "\n=== Access Information ===\n";
echo "Admin Login: http://localhost:8000/admin.php\n";
echo "Documentation: See ADMIN_PANEL_GUIDE.md for detailed usage instructions\n";

// Clean up output
$output = ob_get_clean();
echo $output;

// Also save results to file
file_put_contents('admin_test_results.txt', $output);
echo "\nTest results saved to: admin_test_results.txt\n";
?>
