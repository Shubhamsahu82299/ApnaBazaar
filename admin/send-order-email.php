<?php
include_once('includes/config.php');
include_once('includes/email-config.php');
require_once('../PHPMailer/src/Exception.php');
require_once('../PHPMailer/src/PHPMailer.php');
require_once('../PHPMailer/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? '';
    $customerEmail = $_POST['customer_email'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $orderStatus = $_POST['order_status'] ?? '';
    $productName = $_POST['product_name'] ?? '';
    $orderDate = $_POST['order_date'] ?? '';
    $totalAmount = $_POST['total_amount'] ?? '';

    if (empty($orderId) || empty($customerEmail)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        // Server settings - HOSTINGER SMTP
        $mail->isSMTP();
        $mail->Host = $SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $SMTP_EMAIL;
        $mail->Password = $SMTP_PASSWORD;
        $mail->SMTPSecure = $SMTP_SECURE === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $SMTP_PORT;

        // Sender and Recipient
        $mail->setFrom($SMTP_EMAIL, $COMPANY_NAME);
        $mail->addReplyTo($REPLY_TO, $REPLY_TO_NAME);
        $mail->addAddress($customerEmail, $customerName);

        // Improve deliverability
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . $REPLY_TO . '>');
        $mail->XMailer = 'ApnaBazaarMailer 1.0';

        // Email Subject
        $mail->isHTML(true);
        $mail->Subject = $EMAIL_SUBJECT_PREFIX . "Order #$orderId";

        // Email Body
        $emailBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;'>
                <h1 style='color: #007bff; margin: 0;'>$COMPANY_NAME</h1>
                <p style='color: #6c757d; margin: 10px 0;'>Order Status Update</p>
            </div>

            <div style='background: white; padding: 20px; border-radius: 10px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; margin-top: 0;'>Hello " . htmlspecialchars($customerName) . ",</h2>
                <p>Your order status has been updated. Here are the details:</p>

                <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Order ID:</td>
                            <td style='padding: 8px 0;'>#$orderId</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Product:</td>
                            <td style='padding: 8px 0;'>" . htmlspecialchars($productName) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Order Date:</td>
                            <td style='padding: 8px 0;'>$orderDate</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Total Amount:</td>
                            <td style='padding: 8px 0;'>₹$totalAmount</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Current Status:</td>
                            <td style='padding: 8px 0;'>
                                <span style='background: " . getStatusColor($orderStatus) . "; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px;'>
                                    $orderStatus
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <p>Thank you for choosing $COMPANY_NAME!</p>

                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #6c757d; font-size: 14px;'>
                    <p>If you have any questions, please contact our support team.</p>
                    <p>© " . date("Y") . " $COMPANY_NAME. All rights reserved.</p>
                </div>
            </div>
        </div>";

        $mail->Body = $emailBody;
        $mail->AltBody = "Order Status Update\n\nOrder ID: #$orderId\nProduct: $productName\nStatus: $orderStatus\n\nThank you for choosing $COMPANY_NAME!";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function getStatusColor($status) {
    switch ($status) {
        case 'Accepted': return '#28a745';
        case 'Processing': return '#ffc107';
        case 'Shipped': return '#17a2b8';
        case 'Payment_Done': return '#6f42c1';
        case 'Delivered': return '#20c997';
        default: return '#6c757d';
    }
}
?>
