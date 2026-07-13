<?php
require '../vendor/autoload.php'; // adjust path if needed

use Dompdf\Dompdf;

include_once('includes/config.php');

if (!isset($_GET['uid']) || !isset($_GET['dt'])) {
    die("Invalid request");
}

$uid = intval($_GET['uid']);
$orderDate = $_GET['dt']; // Full timestamp string

// Fetch all orders for same user and same timestamp
$query = $conn->query("
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
    WHERE u.contactno = $uid AND o.orderDate = '$orderDate'
     AND LOWER(o.orderStatus) = 'accepted'
");

if ($query->num_rows == 0) {
    echo 'index.php';
}

$total = 0;
$deliveryCharge = 0;
$rows = [];
$userInfo = [];

while ($row = $query->fetch_assoc()) {
    
    $productName = $row['productName'];
    if (!empty($row['variant_label'])) {
        $productName .= ' <br><span style="color:black;font-size:10px;"> ' . htmlspecialchars($row['variant_label']) . '</span>';
    }
    $variantLabel = !empty($row['variant_label']) ? $row['variant_label'] : '';

    // Use historical prices from orders table if available, otherwise fallback to current prices
    if (isset($row['sell_price_at_order_time']) && isset($row['buy_price_at_order_time'])) {
        $unitPrice = floatval($row['sell_price_at_order_time']);
    } else if (!empty($row['variant_price'])) {
        $unitPrice = floatval($row['variant_price']);
    } else {
        $unitPrice = floatval($row['productPrice']);
    }
    
    $subtotal = $row['quantity'] * $unitPrice;
    $total += $subtotal;
    $deliveryCharge = max($deliveryCharge, $row['deliveryCharge']); // pick highest if different

    $rows[] = [
        'productName' => $productName,
        'productPrice' => $unitPrice,
        'quantity' => $row['quantity'],
        'subtotal' => $subtotal,
          'variant_label' => $variantLabel
    ];

    // Get user info once
    if (empty($userInfo)) {
        $userInfo = [
            'orderid' => $row['orderid'],
            'orderDate' => $row['orderDate'],
            'paymentMethod' => $row['paymentMethod'],
            'orderStatus' => $row['orderStatus'],
            'name' => $row['name'],
            'contactno' => $row['contactno'],
            'address' => $row['shippingAddress'],
            'city' => $row['shippingCity'],
            'state' => $row['shippingState'],
            'pincode' => $row['shippingPincode'],
        ];
    }
}
$totalQty = 0;
$totalProducts = count($rows);

foreach ($rows as $r) {
    $totalQty += $r['quantity'];
}

$grandTotal = $total + $deliveryCharge;

// �� Build HTML Invoice (classic style)
$html = '
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
            margin: -40px;
            padding: -20px;
            
        }
        .invoice-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .invoice-box {
            display: table-cell;
            margin-left:10px;
            margin-right:10px;
            padding:10px;
            padding-left:10px;
            padding-right:10px;
           border: 1px dashed black;
            gap:50px;
            width: 10%;
            box-sizing: border-box;
            vertical-align: top;
        }
        .invoice-box:last-child {
           
        }
        h2, h3 { color: #2c3e50; margin: 4px 0; }
        p { margin: 6px 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 0px; margin-left:0px;padding-left:0px;}
        th, td { padding:4px; border: 1px solid #ccc; text-align: left; font-size: 10px; }
        .totals td { font-weight: bold; }
        .badge {
            font-size: 8px;
            padding: 2px;
            border-radius: 4px;
            background: black;
            color: white;
        }
        .footer {
            font-size: 8px;
            text-align: center;
            margin-top: 0px;
        }
    </style>

    <div class="invoice-row">';

for ($i = 0; $i < 3; $i++) {
    $html .= '<div class="invoice-box">';
    
    $html .= '
        <div class="header">
            <h2>ApnaBazaar PVT LIMITED</h2>
            <p></p>
            <p>Contact:7884074846,9340479676</p>
        </div>

        <hr>
        <p><strong>Order Date:</strong> ' . date('d M Y H:i:s', strtotime($userInfo['orderDate'] . ' +5 hours 30 minutes')) . '</p>
        <p><strong>Payment Method:</strong> ' . $userInfo['paymentMethod'] . '</p>
        <p><strong>Status:</strong> ' . $userInfo['orderStatus'] . '</p>

        <h3>Customer Details</h3>
        <p><strong>Name:</strong> ' . $userInfo['name'] . '</p>
        <p><strong>Contact:</strong> ' . $userInfo['contactno'] . '</p>
        <p><strong>Shipping Address:</strong> ' . $userInfo['address'] . ', ' . $userInfo['city'] . ', ' . $userInfo['state'] . ' - ' . $userInfo['pincode'] . '</p>

        <h3>Product Details</h3>
        <table>
            <tr>
                <th>Product</th>
                <th>Price (₹)</th>
                <th>Qty/Unit</th>
                <th>Subtotal (₹)</th>
            </tr>';

    foreach ($rows as $r) {
        $html .= '
            <tr>
                <td>' . $r['productName'] . '</td>
                <td>' . number_format($r['productPrice'], 2) . '</td>
               <td>' . $r['quantity'] .'</td>

                <td>' . number_format($r['subtotal'], 2) . '</td>
            </tr>';
    }

    $html .= '
            <tr class="totals">
                <td colspan="3">Delivery Charge</td>
                <td>
                    ₹' . number_format($deliveryCharge, 2) . '
                    ' . ($deliveryCharge == 0 ? '<span class="badge">Free Delivery</span>' : '') . '
                </td>
            </tr>
            <tr class="totals">
    <td colspan="3">Total Qty :   ' . $totalQty . '</td>
    <td></td>
</tr>
  <tr class="totals">
                <td colspan="3">Grand Total</td>
                <td>₹' . number_format($grandTotal, 2) . '</td>
            </tr>
        </table>

        <div class="footer">
            <hr>
            <p>Thank you for shopping with ApnaBazaar</p>
        </div>';

    $html .= '</div>'; // end .invoice-box
}

$html .= '</div>'; // end .invoice-row


$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A3', 'portrait');

$dompdf->render();
$dompdf->stream("invoice_" . $userInfo['orderid']  . $userInfo['name'] . ".pdf", array("Attachment" => 1));
exit;
?>
