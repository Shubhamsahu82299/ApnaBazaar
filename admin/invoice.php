<?php
require 'vendor/autoload.php'; // adjust path if needed

use Dompdf\Dompdf;

// ✅ Direct DB connection (no config.php)
$host = "localhost";
$username = "u814646522_ApnaBazaar";
$password = "ApnaBazaar967";
$database = "u814646522_shopping";

$con = new mysqli($host, $username, $password, $database);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// ✅ Order ID validation
if (!isset($_GET['oid']) || !is_numeric($_GET['oid'])) {
    die("Invalid request");
}

$oid = intval($_GET['oid']);

// ✅ Fetch order details
$query = $con->query("
    SELECT 
        o.id AS orderid,
        o.orderDate,
        o.paymentMethod,
        o.quantity,
        o.orderStatus,
        p.productName,
        p.productPrice,
        p.shippingCharge,
        u.name, u.contactno, u.shippingAddress, u.shippingCity, u.shippingState, u.shippingPincode
    FROM orders o
    JOIN products p ON o.productId = p.id
    JOIN users u ON o.userId = u.id
    WHERE o.id = $oid
");

if ($query->num_rows == 0) {
    die("Order not found.");
}

$row = $query->fetch_assoc();

// 🧮 Calculate totals
$productTotal = $row['productPrice'] * $row['quantity'];
$shippingCharge = $row['shippingCharge'];
$totalAmount = $productTotal + $shippingCharge;

// ✅ Invoice HTML
$html = '
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: #333; }
        h2, h3 { color: #2c3e50; }
        p { margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
        .header, .footer { text-align: center; }
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; font-size: 12px; }
        .totals td { font-weight: bold; }
    </style>

    <div class="header">
        <h2>ApnaBazaar PVT LIMITED</h2>
        <p></p>
        <p>Contact:</p>
    </div>

    <hr>

    <p><strong>Order ID:</strong> '.$row['orderid'].'</p>
    <p><strong>Order Date:</strong> '.date('d M Y', strtotime($row['orderDate'])).'</p>
    <p><strong>Payment Method:</strong> '.$row['paymentMethod'].'</p>
    <p><strong>Status:</strong> '.$row['orderStatus'].'</p>

    <h3>Customer Details</h3>
    <p><strong>Name:</strong> '.$row['name'].'</p>
    <p><strong>Contact:</strong> '.$row['contactno'].'</p>
    <p><strong>Shipping Address:</strong> '.$row['shippingAddress'].', '.$row['shippingCity'].', '.$row['shippingState'].' - '.$row['shippingPincode'].'</p>

    <h3>Product Details</h3>
    <table>
        <tr>
            <th>Product</th>
            <th>Price (₹)</th>
            <th>Qty</th>
            <th>Subtotal (₹)</th>
        </tr>
        <tr>
            <td>'.$row['productName'].'</td>
            <td>'.number_format($row['productPrice'], 2).'</td>
            <td>'.$row['quantity'].'</td>
            <td>'.number_format($productTotal, 2).'</td>
        </tr>
        <tr class="totals">
            <td colspan="3">Shipping Charge</td>
            <td>₹'.number_format($shippingCharge, 2).'</td>
        </tr>
        <tr class="totals">
            <td colspan="3">Total Amount</td>
            <td>₹'.number_format($totalAmount, 2).'</td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        <p>Thank you for shopping with ApnaBazaar</p>
    </div>
';

// 🖨 Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ✅ Stream to browser
$dompdf->stream("invoice_{$row['orderid']}.pdf", array("Attachment" => 1));
exit;
?>
