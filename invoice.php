<?php
require 'vendor/autoload.php'; // adjust path if needed

use Dompdf\Dompdf;

include_once('includes/config.php');

if (!isset($_GET['oid']) || !is_numeric($_GET['oid'])) {
    die("Invalid request");
}

$oid = intval($_GET['oid']);

$query = $con->query("
    SELECT 
        o.id AS orderid,
        o.orderDate,
        o.paymentMethod,
        o.quantity,
        o.orderStatus,
        o.deliveryCharge,
        o.variant_id,
        p.productName,
        p.productPrice,
        pv.variant_label,
        pv.price AS variant_price,
        o.buy_price_at_order_time, o.sell_price_at_order_time,
        u.name, u.contactno, u.shippingAddress, u.shippingCity, u.shippingState, u.shippingPincode
    FROM orders o
    JOIN products p ON o.productId = p.id
    LEFT JOIN product_variants pv ON o.variant_id = pv.id
    JOIN users u ON o.userId = u.id
    WHERE o.id = $oid
");

if ($query->num_rows == 0) {
    die("Order not found.");
}

$row = $query->fetch_assoc();

// Use variant if present
$productName = $row['productName'];
if (!empty($row['variant_label'])) {
    $productName .= ' <br><span style="color:#007bff;font-size:13px;">Variant: ' . htmlspecialchars($row['variant_label']) . '</span>';
}
// Use historical prices from orders table if available, otherwise fallback to current prices
if (isset($row['sell_price_at_order_time']) && isset($row['buy_price_at_order_time'])) {
    $unitPrice = floatval($row['sell_price_at_order_time']);
} else if (!empty($row['variant_price'])) {
    $unitPrice = floatval($row['variant_price']);
} else {
    $unitPrice = floatval($row['productPrice']);
}

$productTotal = $unitPrice * $row['quantity'];
$deliveryCharge = floatval($row['deliveryCharge']);
$grandTotal = $productTotal + $deliveryCharge;

// 🧾 Generate HTML Invoice
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
        .badge { font-size: 12px; padding: 2px 6px; border-radius: 4px; background: green; color: white; }
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
            <td>'.$productName.'</td>
            <td>'.number_format($unitPrice, 2).'</td>
            <td>'.$row['quantity'].'</td>
            <td>'.number_format($productTotal, 2).'</td>
        </tr>
        <tr class="totals">
            <td colspan="3">Delivery Charge</td>
            <td>
                ₹'.number_format($deliveryCharge, 2).'
                '.($deliveryCharge == 0 ? '<span class="badge">Free Delivery</span>' : '').'
            </td>
        </tr>
        <tr class="totals">
            <td colspan="3">Grand Total</td>
            <td>₹'.number_format($grandTotal, 2).'</td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        <p>Thank you for shopping with ApnaBazaar</p>
    </div>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("invoice_{$row['orderid']}.pdf", array("Attachment" => 1));
exit;
?>
