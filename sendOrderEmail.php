<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

function sendOrderEmail($toEmail, $toName, $orderId, $status) {
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP setup
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '';     // 🔁 Replace with your Gmail
        $mail->Password = 'pcpysbsdpjzmibro';       // 🔁 Replace with app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Sender & receiver
        $mail->setFrom('', 'ApnaBazaar');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);

        // Switch based on order status
        switch (strtolower($status)) {

            case 'accepted':
                $mail->Subject = "🛒 Order Accepted - Order #$orderId";
                $mail->Body = "
                    <div style='font-family:sans-serif;background:#f5f5f5;padding:20px;border-radius:8px'>
                        <h2 style='color:#4CAF50;'>Hi $toName,</h2>
                        <p>Your order <strong>#$orderId</strong> has been <strong style='color:green;'>accepted</strong>.</p>
                        <p>We’ll notify you when it's shipped.</p>
                        <br>
                        <p style='font-size:13px;color:#777;'>Thank you for shopping with ApnaBazaar!</p>
                    </div>
                ";
                break;

            case 'shipped':
                $mail->Subject = "🚚 Order Shipped - Order #$orderId";
                $mail->Body = "
                    <h2>Hi $toName,</h2>
                    <p>Your order <strong>#$orderId</strong> has been shipped and is on its way!</p>
                    <p>You’ll receive it soon. Track from your account.</p>
                ";
                break;

            case 'delivered':
                $mail->Subject = "📦 Delivered - Order #$orderId";
                $mail->Body = "
                    <h2>Hello $toName,</h2>
                    <p>Your order <strong>#$orderId</strong> has been successfully delivered.</p>
                    <p>We hope you liked it! Please rate your experience.</p>
                ";
                break;

            default:
                $mail->Subject = "Order Update - Order #$orderId";
                $mail->Body = "<p>Your order #$orderId status is now: $status</p>";
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
