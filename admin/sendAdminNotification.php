<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

function sendAdminNotification($orderId, $userEmail, $paymentMethod) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';   // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = ''; // SMTP username
        $mail->Password   = '';    // SMTP password
        $mail->SMTPSecure = 'ssl';                   // encryption (tls or ssl)
        $mail->Port       = 465;                     // SMTP port

        // Recipients
        $mail->setFrom('', '');
        $mail->addAddress('', 'Site Admin'); // Admin email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Payment Method Selected for Order #' . htmlspecialchars($orderId);
        $mail->Body    = "
            <h3>New Payment Update</h3>
            <p><strong>Order ID:</strong> " . htmlspecialchars($orderId) . "</p>
            <p><strong>User Email:</strong> " . htmlspecialchars($userEmail) . "</p>
            <p><strong>Payment Method:</strong> " . htmlspecialchars($paymentMethod) . "</p>
            <p>Please check the admin panel for more details.</p>
        ";

        $mail->send();
        // You can return true or log success here
        return true;
    } catch (Exception $e) {
        // Log error or return false
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
