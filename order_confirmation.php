<?php
require_once 'includes/classes.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$orderModel = new Order();
$order = $orderModel->getOrderById($_GET['id']);

if (!$order) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Order Confirmation';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="text-success mb-3">
                    <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                </div>
                <h1 class="text-success">Order Confirmed!</h1>
                <p class="lead">Thank you for your order. We've received your purchase and will process it shortly.</p>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p class="mb-1"><strong>Order Number:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($order['status']); ?></span></p>
                            <p class="mb-0"><strong>Total Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <p class="mb-0"><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> What's Next?</h6>
                        <ul class="mb-0">
                            <li>You will receive an email confirmation shortly</li>
                            <li>We'll notify you when your order ships</li>
                            <li>Expected delivery: 3-5 business days</li>
                            <li>Questions? Contact us at <?php echo htmlspecialchars($company['email']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary btn-lg me-3">
                    <i class="bi bi-house"></i> Continue Shopping
                </a>
                <button class="btn btn-outline-primary btn-lg" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
