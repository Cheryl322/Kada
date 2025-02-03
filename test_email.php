<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

try {
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'lauyeewen@graduate.utm.my';
    $mail->Password   = 'tjkf rzqm rbar rzee';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('lauyeewen@graduate.utm.my', 'Koperasi KADA Online System');
    $mail->addAddress('lauyeewen@graduate.utm.my'); // Add your email where you want to receive the test

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Koperasi KADA System';
    $mail->Body    = '
        <h2>This is a test email</h2>
        <p>If you receive this email, your email configuration is working correctly.</p>
        <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
    ';

    $mail->send();
    echo 'Message has been sent successfully';

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
} 