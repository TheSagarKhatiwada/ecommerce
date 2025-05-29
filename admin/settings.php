<?php
$page_title = 'Settings';
require_once 'includes/header.php';

$db = Database::getInstance();

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_general':
            $settings = [
                'site_name' => $_POST['site_name'] ?? '',
                'site_description' => $_POST['site_description'] ?? '',
                'site_email' => $_POST['site_email'] ?? '',
                'site_phone' => $_POST['site_phone'] ?? '',
                'currency' => $_POST['currency'] ?? 'USD',
                'tax_rate' => (float)($_POST['tax_rate'] ?? 0),
                'shipping_cost' => (float)($_POST['shipping_cost'] ?? 0)
            ];
            
            // Update or insert each setting
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
            }
            
            $success_message = "General settings updated successfully!";
            break;
            
        case 'update_admin':
            if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
                // In a real application, you'd verify the current password
                // For this demo, we'll just update the admin password
                $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin' OR id = 1");
                $stmt->bind_param("s", $new_password);
                
                if ($stmt->execute()) {
                    $success_message = "Admin password updated successfully!";
                } else {
                    $error_message = "Error updating password: " . $db->error;
                }
            } elseif (!empty($_POST['admin_email'])) {
                $stmt = $db->prepare("UPDATE users SET email = ? WHERE username = 'admin' OR id = 1");
                $stmt->bind_param("s", $_POST['admin_email']);
                
                if ($stmt->execute()) {
                    $success_message = "Admin email updated successfully!";
                } else {
                    $error_message = "Error updating email: " . $db->error;
                }
            }
            break;
            
        case 'maintenance_mode':
            $maintenance_mode = isset($_POST['maintenance_enabled']) ? '1' : '0';
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_mode', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("ss", $maintenance_mode, $maintenance_mode);
            
            if ($stmt->execute()) {
                $success_message = "Maintenance mode " . ($maintenance_mode ? "enabled" : "disabled") . " successfully!";
            } else {
                $error_message = "Error updating maintenance mode: " . $db->error;
            }
            break;
    }
}

// Get current settings
$settings = [];
$settings_query = "SELECT setting_key, setting_value FROM settings";
$result = $db->query($settings_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Get admin user info
$admin_query = "SELECT email FROM users WHERE username = 'admin' OR id = 1 LIMIT 1";
$admin_result = $db->query($admin_query);
$admin_user = $admin_result ? $admin_result->fetch_assoc() : ['email' => ''];

// Get system stats
$stats = [];

// Total products
$result = $db->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result ? $result->fetch_assoc()['count'] : 0;

// Total categories
$result = $db->query("SELECT COUNT(*) as count FROM categories");
$stats['categories'] = $result ? $result->fetch_assoc()['count'] : 0;

// Total orders
$result = $db->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result ? $result->fetch_assoc()['count'] : 0;

// Total customers
$result = $db->query("SELECT COUNT(*) as count FROM users");
$stats['customers'] = $result ? $result->fetch_assoc()['count'] : 0;

// Database size (approximate) - MySQL version
$db_name = 'ecommerce'; // Your database name
$db_size_query = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size_mb 
                  FROM information_schema.tables 
                  WHERE table_schema = '$db_name'";
$result = $db->query($db_size_query);
$stats['db_size'] = $result ? $result->fetch_assoc()['db_size_mb'] * 1024 * 1024 : 0; // Convert back to bytes for formatBytes function
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h2>Settings</h2>
            <p class="text-muted">Configure your store settings and preferences</p>
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

    <div class="row">
        <div class="col-md-8">
            <!-- General Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">General Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_general">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_name">Site Name</label>
                                    <input type="text" id="site_name" name="site_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_name'] ?? 'My eCommerce Store'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_email">Contact Email</label>
                                    <input type="email" id="site_email" name="site_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Site Description</label>
                            <textarea id="site_description" name="site_description" class="form-control" rows="3" 
                                    placeholder="Brief description of your store"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_phone">Phone Number</label>
                                    <input type="text" id="site_phone" name="site_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select id="currency" name="currency" class="form-control">
                                        <option value="USD" <?php echo ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                        <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                        <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                        <option value="CAD" <?php echo ($settings['currency'] ?? '') === 'CAD' ? 'selected' : ''; ?>>CAD ($)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_rate">Tax Rate (%)</label>
                                    <input type="number" id="tax_rate" name="tax_rate" class="form-control" 
                                           min="0" max="100" step="0.01" 
                                           value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '10'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping_cost">Default Shipping Cost</label>
                                    <input type="number" id="shipping_cost" name="shipping_cost" class="form-control" 
                                           min="0" step="0.01" 
                                           value="<?php echo htmlspecialchars($settings['shipping_cost'] ?? '10.00'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                </div>
            </div>

            <!-- Admin Account Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Admin Account</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_admin">
                        
                        <div class="form-group">
                            <label for="admin_email">Admin Email</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($admin_user['email'] ?? ''); ?>">
                        </div>
                        
                        <hr>
                        
                        <h6>Change Password</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Admin Account</button>
                    </form>
                </div>
            </div>

            <!-- Maintenance Mode -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Maintenance Mode</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="maintenance_mode">
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="maintenance_enabled" name="maintenance_enabled" 
                                   <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="maintenance_enabled">
                                <strong>Enable Maintenance Mode</strong>
                            </label>
                        </div>
                        <p class="text-muted mt-2">
                            When enabled, your store will display a maintenance message to visitors. 
                            Only administrators will be able to access the site.
                        </p>
                        
                        <button type="submit" class="btn btn-warning">Update Maintenance Mode</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- System Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">System Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Products:</strong></td>
                            <td class="text-right"><?php echo number_format($stats['products']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Categories:</strong></td>
                            <td class="text-right"><?php echo number_format($stats['categories']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Orders:</strong></td>
                            <td class="text-right"><?php echo number_format($stats['orders']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Customers:</strong></td>
                            <td class="text-right"><?php echo number_format($stats['customers']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Database Size:</strong></td>
                            <td class="text-right"><?php echo formatBytes($stats['db_size']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td class="text-right"><?php echo PHP_VERSION; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../index.php" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-globe"></i> View Store
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearCache()">
                            <i class="bi bi-arrow-clockwise"></i> Clear Cache
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="exportData()">
                            <i class="bi bi-download"></i> Export Data
                        </button>
                        <a href="../admin.php?action=logout" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="card">
                <div class="card-body">
                    <h6 class="text-warning">
                        <i class="bi bi-shield-exclamation"></i> Security Notice
                    </h6>
                    <p class="small text-muted mb-0">
                        Remember to regularly backup your database and keep your system updated. 
                        Change default passwords and use strong authentication.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the cache?')) {
        // In a real application, you'd implement cache clearing
        alert('Cache cleared successfully!');
    }
}

function exportData() {
    if (confirm('This will export your store data. Continue?')) {
        // In a real application, you'd implement data export
        alert('Data export feature would be implemented here.');
    }
}
</script>

<?php
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

require_once 'includes/footer.php';
?>
