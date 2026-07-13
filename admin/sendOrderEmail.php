<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

function sendOrderEmail($toEmail, $toName, $orderId, $statusKey)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username = '';
$mail->Password = '';    // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
     
$mail->setFrom('', '');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);

        $statusMessages = [
            'accepted' => 'Your order has been <b>Accepted</b>! We'll start processing it shortly.',
            'shipped_from_ApnaBazaar' => 'Your order has been <b>Shipped</b>! You will receive it soon.',
            'delivered' => 'Your order has been <b>Delivered</b>. Thank you for shopping with us!'
        ];

        $subjectMap = [
            'accepted' => 'Order Accepted - Order #' . $orderId,
            'shipped_from_ApnaBazaar' => 'Order Shipped - Order #' . $orderId,
            'delivered' => 'Order Delivered - Order #' . $orderId
        ];

        $statusKey = strtolower($statusKey);
        $message = $statusMessages[$statusKey] ?? 'Order update';
        $subject = $subjectMap[$statusKey] ?? 'Order Update - Order #' . $orderId;

        $mail->Subject = $subject;
        $mail->Body    = "<p>Hi <b>$toName</b>,</p><p>$message</p><p>Order ID: <b>$orderId</b></p><br><p>--<br>Team ApnaBazaar</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error for Order #$orderId: {$mail->ErrorInfo}");
        return false;
    }
}
