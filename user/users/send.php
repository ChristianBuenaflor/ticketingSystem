<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once __DIR__ . '/../../includes/role_permissions.php';
require_once __DIR__ . '/../../includes/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../includes/smtp_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || empty($_SESSION['logged_in'])
    || !isManagementRole((string) ($_SESSION['role'] ?? ''))) {
    http_response_code(403);
    exit('Forbidden.');
}
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Invalid security token.');
}

$recipient = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = (string) ($_POST['message'] ?? '');
if (!$recipient || $subject === '' || strlen($subject) > 200 || strlen($message) > 12000) {
    http_response_code(400);
    exit('Invalid email content.');
}

$email = new PHPMailer(true);
try {
    if (!configureTicketingMailer($email)) {
        http_response_code(500);
        exit('Email service is not configured.');
    }
    $email->addAddress($recipient);
    $email->isHTML(true);
    $email->Subject = $subject;
    $email->Body = $message;
    $email->send();
} catch (Exception $exception) {
    error_log('Account notification email failed: ' . $email->ErrorInfo);
    http_response_code(500);
    exit('Unable to send email.');
}
