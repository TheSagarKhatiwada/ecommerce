<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';

$cart = new Cart();
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';
    
    if (empty($errors)) {
        try {
            $orderModel = new Order();
            $orderId = $orderModel->createOrder([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address
            ], $cartItems);
            
            // Clear cart
            $cart->clear();
            
            // Redirect to confirmation page
            header("Location: order_confirmation.php?id=$orderId");
            exit;
            
        } catch (Exception $e) {
            $errors[] = 'There was an error processing your order. Please try again.';
        }
    }
}

$shipping = $cartTotal >= 50 ? 0 : 9.99;
$tax = $cartTotal * 0.08;
$total = $cartTotal + $shipping + $tax;
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>
    
    <h1 class="mb-4">Checkout</h1>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <h6>Please fix the following errors:</h6>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Billing Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Payment Information</h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> This is a demo checkout. No actual payment will be processed.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card_number" 
                                               placeholder="1234 5678 9012 3456" value="1234 5678 9012 3456" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="expiry" class="form-label">Expiry</label>
                                        <input type="text" class="form-control" id="expiry" 
                                               placeholder="MM/YY" value="12/25" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" 
                                               placeholder="123" value="123" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="cart.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Cart
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card"></i> Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <!-- Order Items -->
                    <?php foreach ($cartItems as $item): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></small>
                        </div>
                        <span class="fw-bold"><?php echo formatPrice($item['total']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($cartTotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span><?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <strong>Total:</strong>
                            <strong><?php echo formatPrice($total); ?></strong>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex align-items-center text-success small">
                            <i class="bi bi-shield-check me-2"></i>
                            Secure checkout with SSL encryption
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
