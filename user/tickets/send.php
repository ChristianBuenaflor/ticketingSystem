<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../includes/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../includes/smtp_config.php';

$email = new PHPMailer(true);

try {
    if (!configureTicketingMailer($email)) {
        return false;
    }

    $email->addAddress($client_email);
    $email->isHTML(true);
    $email->Subject = $subject;
    $email->Body = $message;
    $email->send();
    return true;
} catch (Exception $exception) {
    error_log('Ticket status notification failed: ' . $email->ErrorInfo);
    return false;
}
