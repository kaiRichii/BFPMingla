<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('./PHPMailer/src/Exception.php');
require_once('./PHPMailer/src/PHPMailer.php');
require_once('./PHPMailer/src/SMTP.php');

$mail = new PHPMailer;

$mail->isSMTP(); 
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'tessnarval11@gmail.com';
$mail->Password = 'vlkc srgz zdrw llbd';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->isHTML(true); 
$mail->setFrom('tessnarval11@gmail.com', 'BFP');
?>