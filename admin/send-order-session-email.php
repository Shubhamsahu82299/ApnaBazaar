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
    $orderSession = $_POST['order_session'] ?? '';
    $customerEmail = $_POST['customer_email'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $orderDate = $_POST['order_date'] ?? '';
    
    if (empty($orderSession) || empty($customerEmail)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }
    
    $sessionParts = explode('_', $orderSession);
    if (count($sessionParts) !== 2) {
        echo json_encode(['success' => false, 'message' => 'Invalid order session format']);
        exit;
    }
    
    $contactno = $sessionParts[0];
    $orderDateStr = $sessionParts[1];
    
    $sql = "SELECT 
        o.id AS orderid,
        o.orderDate,
        o.paymentMethod,
        o.quantity,
        o.orderStatus,
        o.deliveryCharge,
        o.variant_id,
        p.productName,
        p.productPrice,
        p.productCompany,
        pv.variant_label,
        pv.price AS variant_price,
        u.name, u.contactno, u.shippingAddress, u.shippingCity, u.shippingState, u.shippingPincode
    FROM orders o
    JOIN products p ON o.productId = p.id
    LEFT JOIN product_variants pv ON o.variant_id = pv.id
    JOIN users u ON o.userId = u.id
    WHERE u.contactno = ? AND o.orderDate = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $contactno, $orderDateStr);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'No orders found for this session']);
        exit;
    }
    
    $total = 0;
    $deliveryCharge = 0;
    $products = [];
    $userInfo = [];
    $orderStatuses = [];
    
    while ($row = $result->fetch_assoc()) {
        $productName = $row['productName'];
        if (!empty($row['variant_label'])) {
            $productName .= ' (Variant: ' . $row['variant_label'] . ')';
        }
        
        $unitPrice = !empty($row['variant_price']) ? $row['variant_price'] : $row['productPrice'];
        $subtotal = $row['quantity'] * $unitPrice;
        $total += $subtotal;
        $deliveryCharge = max($deliveryCharge, $row['deliveryCharge']);
        
        $products[] = [
            'name' => $productName,
            'company' => $row['productCompany'],
            'price' => $unitPrice,
            'quantity' => $row['quantity'],
            'subtotal' => $subtotal
        ];
        
        $orderStatuses[] = $row['orderStatus'];
        
        if (empty($userInfo)) {
            $userInfo = [
                'orderid' => $row['orderid'],
                'orderDate' => $row['orderDate'],
                'paymentMethod' => $row['paymentMethod'],
                'name' => $row['name'],
                'contactno' => $row['contactno'],
                'address' => $row['shippingAddress'],
                'city' => $row['shippingCity'],
                'state' => $row['shippingState'],
                'pincode' => $row['shippingPincode'],
            ];
        }
    }
    
    $grandTotal = $total + $deliveryCharge;
    $uniqueStatuses = array_unique($orderStatuses);
    $statusText = implode(', ', $uniqueStatuses);
    
    $orderDate = new DateTime($userInfo['orderDate'], new DateTimeZone('UTC'));
    $orderDate->setTimezone(new DateTimeZone('Asia/Kolkata'));
    $formattedOrderDate = $orderDate->format('d M Y, h:i A');
    
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();  
        $mail->Host = $SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $SMTP_EMAIL;
        $mail->Password = $SMTP_PASSWORD;
        $mail->SMTPSecure = $SMTP_SECURE === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $SMTP_PORT;

        $mail->setFrom($SMTP_EMAIL, $COMPANY_NAME);
        $mail->addReplyTo($REPLY_TO, $REPLY_TO_NAME);
        $mail->addAddress($customerEmail, $customerName);
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . $REPLY_TO . '>');
        $mail->XMailer = 'ApnaBazaarMailer 1.0';

        $mail->isHTML(true);
        $mail->Subject = $EMAIL_SUBJECT_PREFIX . "Order #" . $userInfo['orderid'];
        
        $productsTable = '';
        foreach ($products as $product) {
            $productsTable .= '
            <tr>
                <td style="padding: 6px; border-bottom: 1px solid #eee; font-size:10px;">' . htmlspecialchars($product['name']) . '<br>
                    <span style="font-size: 11px; color: #666;">' . htmlspecialchars($product['company']) . '</span>
                </td>
                <td style="padding: 6px; text-align: center; font-size:10px;">₹' . number_format($product['price'], 2) . '</td>
                <td style="padding: 6px; text-align: center; font-size:10px;">' . $product['quantity'] . '</td>
                <td style="padding: 6px; text-align: right; font-weight: bold; font-size:10px;">₹' . number_format($product['subtotal'], 2) . '</td>
            </tr>';
        }

        // Email Body
       $emailBody = '
<div style="font-family: Arial, sans-serif; max-width: 700px; margin: auto; padding: 12px; background: #f2f2f2;">
  <div style="background: #ffffff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
    
    <div style="text-align: center; margin-bottom: 20px;">
      <h1 style="color: #ff9800; margin: 0; font-size: 22px; font-weight: bold;">' . $COMPANY_NAME . '</h1>
      <p style="color: #777; font-size: 10px; margin-top: 4px;">Order Status Update</p>
    </div>

    <p style="font-size: 13px; color: #333; line-height: 1.6;">
      Namaste <strong>' . htmlspecialchars($customerName) . '</strong> 🙏,<br>
      Here is the latest update on your order:
    </p>

    <div style="background: #fffaf0; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #f0e0c0;">
      <h3 style="margin-top: 0; color: #333; font-size: 14px;">📋 Order Information</h3>
      <p style="margin: 5px 0; font-size: 10px;">
        <strong>Order ID:</strong> #' . $userInfo['orderid'] . '<br>
        <strong>Date:</strong> ' . $formattedOrderDate . '<br>
        <strong>Status:</strong> 
        <span style="background:' . getStatusColor($uniqueStatuses[0]) . '; padding:3px 8px; border-radius:4px; color:#fff; font-weight:bold; font-size:11px;">' . $statusText . '</span><br>
        <strong>Payment:</strong> ' . htmlspecialchars($userInfo['paymentMethod']) . '
      </p>
    </div>

    <div style="background: #f9f9f9; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #eee;">
      <h3 style="margin-top: 0; color: #333; font-size: 14px;">📍 Shipping Address</h3>
      <p style="margin: 0; font-size: 10px; color: #444; line-height:1.4;">' . htmlspecialchars($userInfo['address']) . ', ' . htmlspecialchars($userInfo['city']) . ', ' . htmlspecialchars($userInfo['state']) . ' - ' . $userInfo['pincode'] . '</p>
    </div>

    <h3 style="color: #333; font-size: 14px; margin-bottom: 6px;">🛍️ Products Ordered</h3>
    <div style="overflow-x:auto;">
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; word-break: break-word;">
        <thead>
          <tr style="background: #fafafa; color: black; font-size: 10px;">
            <th style="padding: 6px; text-align: left;">Product</th>
            <th style="padding: 6px; text-align: center;">Price</th>
            <th style="padding: 6px; text-align: center;">Qty</th>
            <th style="padding: 6px; text-align: right;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          ' . $productsTable . '
        </tbody>
        <tfoot>
          <tr style="background: #fafafa;">
            <td colspan="3" style="text-align:right; padding:6px; font-weight:bold; font-size:10px;">Subtotal:</td>
            <td style="padding:6px; text-align:right; font-size:10px;">₹' . number_format($total, 2) . '</td>
          </tr>
          <tr style="background: #fafafa;">
            <td colspan="3" style="text-align:right; padding:6px; font-weight:bold; font-size:10px;">Delivery:</td>
            <td style="padding:6px; text-align:right; font-size:10px;">₹' . number_format($deliveryCharge, 2) . '</td>
          </tr>
          <tr style="background: #ff9800; color:white; font-size:13px;">
            <td colspan="3" style="text-align:right; padding:6px; font-weight:bold;">Grand Total:</td>
            <td style="padding:6px; text-align:right;">₹' . number_format($grandTotal, 2) . '</td>
          </tr>
        </tfoot>
      </table>
    </div>

  <!-- Contact & Social Links -->
<div style="text-align:center; margin-top:40px; padding:25px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:12px;">
    <h3 style="color:#1565C0; font-size:20px; margin-bottom:15px;">📞 Contact Us</h3>
    <p style="margin:6px 0; font-size:14px; color:#333; line-height:1.8;">
        ☎️ Office: 
        <a href="tel:07884074846" style="color:#1565C0; text-decoration:none; font-weight:600;">07884074846</a><br>
        📱 Mobile: 
        <a href="tel:7648023765" style="color:#1565C0; text-decoration:none; font-weight:600;">7648023765</a>, 
        <a href="tel:9340479676" style="color:#1565C0; text-decoration:none; font-weight:600;">9340479676</a><br>
        🟢 WhatsApp: 
        <a href="https://wa.me/ApnaBazaar" style="color:#1565C0; text-decoration:none; font-weight:600;">Chat Now</a>
    </p>

    <div style="margin-top:25px;">
        <h3 style="color:#1565C0; font-size:20px; margin-bottom:15px;">🌐 Follow Us</h3>
        <p style="font-size:14px; line-height:1.8;">
            ▶️ <a href="https://youtube.com/@ApnaBazaarspsservicepoint" style="color:#1565C0; text-decoration:none; font-weight:600;">YouTube</a> | 
            📘 <a href="https://www.facebook.com/share/16pjhtxR3J/" style="color:#1565C0; text-decoration:none; font-weight:600;">Facebook</a> | 
            📸 <a href="https://www.instagram.com/ApnaBazaarspsservicepoint" style="color:#1565C0; text-decoration:none; font-weight:600;">Instagram</a><br>
            🌍 <a href="https://ApnaBazaarservicepoint.store" style="color:#1565C0; text-decoration:none; font-weight:600;">Website</a>
        </p>
    </div>
</div>


  </div>
</div>';


        $mail->Body = $emailBody;
        $mail->AltBody = "Order Update\nOrder ID: #" . $userInfo['orderid'] . "\nStatus: " . $statusText;

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
    }

    $stmt->close();
    $conn->close();
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
