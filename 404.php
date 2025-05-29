<?php
$pageTitle = 'Page Not Found';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="error-page">
                <h1 class="display-1 text-primary">404</h1>
                <h2 class="h4 mb-4">Page Not Found</h2>
                <p class="text-muted mb-4">Sorry, the page you are looking for doesn't exist.</p>
                
                <div class="d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house"></i> Go Home
                    </a>
                    <button class="btn btn-outline-secondary" onclick="history.back()">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </button>
                </div>
                
                <div class="mt-5">
                    <h5>You might be looking for:</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-decoration-none">Homepage</a></li>
                        <li><a href="search.php" class="text-decoration-none">Search Products</a></li>
                        <li><a href="contact.php" class="text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
