<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        $success = true;
    }
}
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="text-center mb-5">Contact Us</h1>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle"></i> Message Sent Successfully!</h6>
                <p class="mb-0">Thank you for contacting us. We'll get back to you within 24 hours.</p>
            </div>
            <?php endif; ?>
            
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
                            <h5 class="mb-0">Send us a Message</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-envelope"></i> Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Get in Touch</h5>
                        </div>
                        <div class="card-body">
                            <div class="contact-info">
                                <div class="mb-4">
                                    <h6><i class="bi bi-geo-alt text-primary"></i> Address</h6>
                                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($company['address'])); ?></p>
                                </div>
                                
                                <div class="mb-4">
                                    <h6><i class="bi bi-telephone text-primary"></i> Phone</h6>
                                    <p class="text-muted mb-0">
                                        <a href="tel:<?php echo htmlspecialchars($company['phone']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($company['phone']); ?>
                                        </a>
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <h6><i class="bi bi-envelope text-primary"></i> Email</h6>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:<?php echo htmlspecialchars($company['email']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($company['email']); ?>
                                        </a>
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <h6><i class="bi bi-globe text-primary"></i> Website</h6>
                                    <p class="text-muted mb-0">
                                        <a href="http://<?php echo htmlspecialchars($company['website']); ?>" class="text-decoration-none" target="_blank">
                                            <?php echo htmlspecialchars($company['website']); ?>
                                        </a>
                                    </p>
                                </div>
                                
                                <div>
                                    <h6><i class="bi bi-clock text-primary"></i> Opening Hours</h6>
                                    <div class="text-muted small">
                                        <?php echo nl2br(htmlspecialchars($company['opening_hours'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
