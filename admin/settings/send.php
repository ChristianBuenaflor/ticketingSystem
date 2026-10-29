<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../includes/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../includes/smtp_config.php';

$recipient = filter_var($new_email ?? '', FILTER_VALIDATE_EMAIL);
if (!$recipient) {
    error_log('Verification email has an invalid recipient.');
    return false;
}

$email = new PHPMailer(true);
try {
    if (!configureTicketingMailer($email)) {
        return false;
    }
    $email->addAddress($recipient);
    $email->isHTML(true);
    $email->Subject = (string) ($subject ?? '');
    $email->Body = (string) ($message ?? '');
    $email->send();
    return true;
} catch (Exception $exception) {
    error_log('Admin profile verification email failed: ' . $email->ErrorInfo);
    return false;
}
