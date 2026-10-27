<?php
// Initialize session
session_start();

// Define absolute base path
define('BASE_PATH', realpath(dirname(__DIR__)));

// Verify and load connections.php
$connectionsPath = BASE_PATH . '/includes/connections.php';
if (!file_exists($connectionsPath)) {
    die("Error: Database configuration file not found at: " . $connectionsPath);
}
require $connectionsPath;

// Verify and load header.php
$headerPath = BASE_PATH . '/header.php';
if (!file_exists($headerPath)) {
    die("Error: Header file not found at: " . $headerPath);
}
require $headerPath;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verification-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .alert-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container verification-container text-center">
        <?php if (isset($_SESSION['verification_status'])): ?>
            <div class="alert alert-<?= $_SESSION['verification_status']['success'] ? 'success' : 'danger' ?>">
                <div class="alert-icon">
                    <?= $_SESSION['verification_status']['success'] ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?>
                </div>
                <h4 class="alert-heading">
                    <?= $_SESSION['verification_status']['success'] ? 'Verification Successful!' : 'Verification Failed' ?>
                </h4>
                <p><?= htmlspecialchars($_SESSION['verification_status']['message']) ?></p>
                <?php if ($_SESSION['verification_status']['success'] && isset($_SESSION['verification_status']['email'])): ?>
                    <p class="mb-0">Email: <strong><?= htmlspecialchars($_SESSION['verification_status']['email']) ?></strong></p>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['verification_status']); ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="alert-heading">Verification Status Unknown</h4>
                <p>No verification information was found. Please try again.</p>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="<?= BASE_PATH ?>/index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Return to Home Page
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Verify and load footer.php
$footerPath = BASE_PATH . '/footer.php';
if (!file_exists($footerPath)) {
    die("Error: Footer file not found at: " . $footerPath);
}
require $footerPath;
?>