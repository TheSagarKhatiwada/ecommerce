<?php
$pageTitle = 'Server Error';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="error-page">
                <h1 class="display-1 text-danger">500</h1>
                <h2 class="h4 mb-4">Internal Server Error</h2>
                <p class="text-muted mb-4">Something went wrong on our end. Please try again later.</p>
                
                <div class="d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house"></i> Go Home
                    </a>
                    <button class="btn btn-outline-secondary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </div>
                
                <div class="mt-5">
                    <p class="text-muted">If the problem persists, please contact our support team at 
                        <a href="mailto:<?php echo htmlspecialchars($company['email']); ?>"><?php echo htmlspecialchars($company['email']); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
