<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../includes/phpmailer/src/Exception.php';
require '../../includes/phpmailer/src/PHPMailer.php';
require '../../includes/phpmailer/src/SMTP.php';

$email= new PHPMailer(true);

$email->isSMTP();
$email->Host = 'smtp.gmail.com';
$email->SMTPAuth = true;
$email->Username = 'mojarjairus@gmail.com';
$email->Password = 'zxmrghjzpmfjzhdm';
$email->SMTPSecure = 'ssl';
$email->Port = 465;

$email->setFrom('mojarjairus@gmail.com', 'Ticketing System');

$email->addAddress($new_email);

$email->isHTML(true);

$email->Subject = $subject;
$email->Body = $message;

$email->send();

?>