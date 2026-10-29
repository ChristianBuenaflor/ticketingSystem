<?php
function configureTicketingMailer(\PHPMailer\PHPMailer\PHPMailer $mailer): bool
{
    $host = getenv('TICKETING_SMTP_HOST') ?: 'smtp.gmail.com';
    $username = trim((string) getenv('TICKETING_SMTP_USERNAME'));
    $password = (string) getenv('TICKETING_SMTP_PASSWORD');
    $portValue = getenv('TICKETING_SMTP_PORT');
    $port = filter_var($portValue === false ? '587' : $portValue, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);
    $encryption = strtolower((string) (getenv('TICKETING_SMTP_ENCRYPTION') ?: 'tls'));
    $fromAddress = trim((string) (getenv('TICKETING_SMTP_FROM') ?: $username));
    $fromName = (string) (getenv('TICKETING_SMTP_FROM_NAME') ?: 'Ticketing System');

    if ($username === '' || $password === '' || $port === false || !in_array($encryption, ['tls', 'ssl'], true)) {
        error_log('SMTP configuration is missing or invalid. Configure TICKETING_SMTP_USERNAME and TICKETING_SMTP_PASSWORD.');
        return false;
    }

    $mailer->isSMTP();
    $mailer->Host = $host;
    $mailer->SMTPAuth = true;
    $mailer->Username = $username;
    $mailer->Password = $password;
    $mailer->SMTPSecure = $encryption === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = $port;
    $mailer->setFrom($fromAddress, $fromName);

    return true;
}
