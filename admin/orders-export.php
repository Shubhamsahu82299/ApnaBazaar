<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/config.php');

if (ob_get_length()) ob_end_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="orders.csv"');

$output = fopen("php://output", "w");
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

$headers = [
    'S.NO.', 
    'ORDER DATE', 
    'CUSTOMER NAME', 
    'ADDRESS', 
    'MO. NO', 
    'PRODUCT DETAIL', 
    'PRODUCT QUANTITY', 
    'DELIVERY DATE', 
    'RETURN DATE', 
    'TYPE OF PAYMENT', 
    'AMOUNT', 
    'TYPE OF CUSTOMER', 
    'ORDER CANCEL', 
    'RETURN', 
    'REASON FOR RETURN', 
    'FEEDBACK'
];
fputcsv($output, $headers);

// ✅ Query with subqueries for delivery, return & reason
$query = "SELECT 
            o.id,
            o.orderDate,
            u.name AS customerName,
            u.shippingAddress AS address,
            u.contactno AS mobile,
            p.productName AS productDetail,
            o.quantity,
            o.paymentMethod,
            o.orderStatus,
            (o.quantity * IFNULL(pv.price, p.productPrice)) AS amount,

            -- delivery date
            (SELECT postingDate 
             FROM ordertrackhistory h 
             WHERE h.orderId = o.id AND h.status = 'Delivered' 
             ORDER BY h.id DESC LIMIT 1) AS deliveryDate,

            -- return date
            (SELECT postingDate 
             FROM ordertrackhistory h 
             WHERE h.orderId = o.id AND h.status = 'Returned' 
             ORDER BY h.id DESC LIMIT 1) AS returnDate,

            -- reason for return
            (SELECT remark 
             FROM ordertrackhistory h 
             WHERE h.orderId = o.id AND h.status = 'Returned' 
             ORDER BY h.id DESC LIMIT 1) AS returnReason

          FROM orders o
          JOIN users u ON u.id = o.userId
          JOIN products p ON p.id = o.productId
          LEFT JOIN product_variants pv ON o.variant_id = pv.id
          ORDER BY o.id DESC";

$result = mysqli_query($conn, $query);

$serial = 1;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orderDate    = !empty($row['orderDate']) ? date("d-m-Y H:i", strtotime($row['orderDate'])) : "";
        $deliveryDate = !empty($row['deliveryDate']) ? date("d-m-Y H:i", strtotime($row['deliveryDate'])) : "";
        $returnDate   = !empty($row['returnDate']) ? date("d-m-Y H:i", strtotime($row['returnDate'])) : "";

        // ✅ Safe strtolower (avoid null warnings)
        $status = !empty($row['orderStatus']) ? strtolower($row['orderStatus']) : '';

        // ✅ Order Cancel / Return Handling
        $orderCancel = ($status === 'cancelled') ? 'Yes' : '';
        $orderReturn = ($status === 'returned') ? 'Yes' : '';

        $csvRow = [
            $serial++,                    
            $orderDate,                    
            $row['customerName'] ?? '',    
            $row['address'] ?? '',         
            $row['mobile'] ?? '',          
            $row['productDetail'] ?? '',   
            $row['quantity'] ?? '',        
            $deliveryDate,                 
            $returnDate,                   
            $row['paymentMethod'] ?? '',   
            $row['amount'] ?? 0,           
            '',  // type of customer (not in DB)
            $orderCancel,                  
            $orderReturn,                  
            $row['returnReason'] ?? '',    // ✅ from ordertrackhistory.remark
            ''                             // feedback (not in DB)
        ];

        fputcsv($output, $csvRow);
    }
} else {
    fputcsv($output, ["No orders found"]);
}

fclose($output);
exit;
?>
