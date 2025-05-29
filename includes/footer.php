    <!-- Footer -->
    <footer class="footer mt-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><i class="bi bi-shop"></i> <?php echo htmlspecialchars($company['name']); ?></h5>
                    <p class="text-muted">Your trusted partner for quality products and exceptional service.</p>
                    <div class="d-flex">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>Contact Info</h5>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($company['address']); ?>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($company['phone']); ?>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($company['email']); ?>
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-globe"></i> <?php echo htmlspecialchars($company['website']); ?>
                    </p>
                </div>
                
                <div class="col-lg-4 col-md-12 mb-4">
                    <h5>Opening Hours</h5>
                    <div class="text-muted">
                        <?php echo nl2br(htmlspecialchars($company['opening_hours'])); ?>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['name']); ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted me-3">Privacy Policy</a>
                    <a href="#" class="text-muted me-3">Terms of Service</a>
                    <a href="#" class="text-muted">Support</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add to cart functionality
        function addToCart(productId, quantity = 1) {
            fetch('cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart badge
                    updateCartBadge();
                    
                    // Show success message
                    showAlert('Product added to cart!', 'success');
                } else {
                    showAlert('Error adding product to cart', 'danger');
                }
            })
            .catch(error => {
                showAlert('Error adding product to cart', 'danger');
            });
        }
        
        // Update cart badge
        function updateCartBadge() {
            fetch('cart_handler.php?action=count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.cart-badge');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                    } else {
                        // Create badge if it doesn't exist
                        const cartButton = document.querySelector('a[href="cart.php"]');
                        const newBadge = document.createElement('span');
                        newBadge.className = 'cart-badge';
                        newBadge.textContent = data.count;
                        cartButton.appendChild(newBadge);
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
        }
        
        // Show alert message
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.top = '80px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 3000);
        }
    </script>
</body>
</html>
