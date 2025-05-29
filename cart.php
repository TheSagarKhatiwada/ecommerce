<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

$cart = new Cart();
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </nav>
    
    <h1 class="mb-4">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3">Your cart is empty</h4>
        <p class="text-muted">Add some products to your cart to get started.</p>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="row align-items-center py-3 border-bottom" id="cart-item-<?php echo $item['id']; ?>">
                        <div class="col-md-2">
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 style="height: 80px; object-fit: cover;"
                                 onerror="this.src='assets/images/placeholder.jpg'">
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <small class="text-muted"><?php echo formatPrice($item['price']); ?> each</small>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       onchange="updateCartQuantity(<?php echo $item['id']; ?>, this.value)">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <strong><?php echo formatPrice($item['total']); ?></strong>
                        </div>
                        <div class="col-md-1 text-center">
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="removeFromCart(<?php echo $item['id']; ?>)"
                                    title="Remove item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal"><?php echo formatPrice($cartTotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span><?php echo $cartTotal >= 50 ? 'FREE' : formatPrice(9.99); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span><?php echo formatPrice($cartTotal * 0.08); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between h5">
                        <strong>Total:</strong>
                        <strong id="cart-total">
                            <?php 
                            $shipping = $cartTotal >= 50 ? 0 : 9.99;
                            $tax = $cartTotal * 0.08;
                            $total = $cartTotal + $shipping + $tax;
                            echo formatPrice($total); 
                            ?>
                        </strong>
                    </div>
                    
                    <?php if ($cartTotal >= 50): ?>
                    <div class="alert alert-success small mb-3">
                        <i class="bi bi-check-circle"></i> You qualify for FREE shipping!
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle"></i> Add <?php echo formatPrice(50 - $cartTotal); ?> more for FREE shipping!
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid">
                        <a href="checkout.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateCartQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update totals
        } else {
            showAlert('Error updating cart', 'danger');
        }
    });
}

function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to update cart
            } else {
                showAlert('Error removing item from cart', 'danger');
            }
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
