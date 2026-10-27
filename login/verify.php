<?php
session_start();

define('BASE_PATH', realpath(dirname(__DIR__)));

// Load configuration using absolute path
require BASE_PATH . '/includes/connections.php';

// Verify database connection exists
if (!isset($con) || !($con instanceof mysqli)) {
    die("Database connection not established");
};

// Debugging - remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify database connection exists
if (!isset($con) || !($con instanceof mysqli)) {
    die("Database connection not established");
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Check token validity
        $stmt = $con->prepare("SELECT user_id, email FROM users 
                             WHERE verification_token = ? 
                             AND token_expiry > NOW()");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }
        
        $stmt->bind_param("s", $token);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Mark as verified
            $update = $con->prepare("UPDATE users 
                                   SET verified = 1, 
                                       verification_token = NULL, 
                                       token_expiry = NULL 
                                   WHERE user_id = ?");
            $update->bind_param("i", $user['user_id']);
            $update->execute();
            
            $_SESSION['verification_status'] = [
                'success' => true,
                'email' => $user['email'],
                'message' => 'Email verified successfully!'
            ];
            header("Location: ./verification-landing.php");
        } else {
            $_SESSION['verification_status'] = [
                'success' => false,
                'message' => 'Invalid or expired verification link'
            ];
            header("Location: ./verification-landing.php");
        }
    } catch (Exception $e) {
        $_SESSION['verification_status'] = [
            'success' => false,
            'message' => 'Verification process failed: ' . $e->getMessage()
        ];
        header("Location: ./verification-landing.php");
    }
    exit;
}

// No token provided
header("Location: ../index.php");
exit;
?>