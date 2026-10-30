<?php
// login/signup-process.php
session_start();
include("../includes/connections.php");

require '../vendor/autoload.php'; // If using Composer
require_once '../includes/smtp_config.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function registerNewUser($userData, $con) {
    // Validate required fields
    if (empty($userData['email']) || empty($userData['password']) || 
       empty($userData['first_name']) || empty($userData['last_name']) || 
       empty($userData['clientele'])) {
        return ['status' => 'error', 'message' => 'All fields are required'];
    }

    // Check if email exists
    $stmt = $con->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $userData['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['status' => 'error', 'message' => 'Email already exists'];
    }

    // Hash password
    $hashed_password = password_hash($userData['password'], PASSWORD_DEFAULT);
    $role = 'clerk';

    // Start transaction
    $con->begin_transaction();

    try {
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
$expiry_date = date('Y-m-d H:i:s', strtotime('+24 hours'));

$userSql = "INSERT INTO users 
           (email, password, role, verification_token, token_expiry, verified) 
           VALUES (?, ?, ?, ?, ?, 0)"; // Explicitly set verified=0

        // Insert into users table
        $stmt = $con->prepare("INSERT INTO users (email, password, role, verification_token, token_expiry) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", 
            $userData['email'],
            $hashed_password,
            $role,
            $verification_token,
            $expiry_date
        );
        $stmt->execute();
        $user_id = $con->insert_id;

        // Insert into user_profiles table
        $stmt = $con->prepare("INSERT INTO users_info (user_id, first_name, last_name, clientele, date_created) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", 
            $user_id,
            $userData['first_name'],
            $userData['last_name'],
            $userData['clientele']
        );
        $stmt->execute();

        // Send verification email
        $mailSent = sendVerificationEmail($userData['email'], $verification_token, $userData['first_name']);
        
        if (!$mailSent) {
            throw new Exception("Failed to send verification email");
        }

        $con->commit();
        return ['status' => 'success', 'message' => 'Registration successful. Please check your email.', 'user_id' => $user_id];

    } catch (Exception $e) {
        $con->rollback();
        return ['status' => 'error', 'message' => 'Registration failed: '.$e->getMessage()];
    }
}

function sendVerificationEmail($email, $token, $name) {
    $mail = new PHPMailer(true);
    
    try {
        
        if (!configureTicketingMailer($mail)) {
            return false;
        }

        // Recipients
        $mail->addAddress($email, $name);

        // Content
     $verification_url = "http://".$_SERVER['HTTP_HOST']."/ticketing/login/verify.php?token=$token";
        
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address';
        $mail->Body = "
            <h2>Welcome!</h2>
            <p>Hello $name,</p>
            <p>Please verify your email:</p>
            <a href=\"$verification_url\" style=\"background: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Verify Email</a>
            <p>Or copy this link: <code>$verification_url</code></p>
            <p>Link expires in 24 hours.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $userData = [
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'clientele' => $_POST['clientele']
    ];

    $result = registerNewUser($userData, $con);
    echo json_encode($result);
    exit;
}
?>