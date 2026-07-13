<?php
include_once('includes/config.php');

$allowedStatuses = ['Accepted', 'Processing', 'Shipped', 'Payment_Done', 'Delivered'];
$allowedSortFields = ['orders.id', 'orders.orderDate', 'users.name', 'products.productPrice'];

$filterStatus = $_GET['filter_status'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'orders.id';
$sortOrder = strtolower($_GET['sort_order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

if (!in_array($filterStatus, $allowedStatuses)) $filterStatus = '';
if (!in_array($sortBy, $allowedSortFields)) $sortBy = 'orders.id';

// Detect new order in last 10 seconds
$newOrder = false;
$sqlNew = "SELECT id FROM orders WHERE TIMESTAMPDIFF(SECOND, orderDate, NOW()) <= 10 AND (orderStatus IS NULL OR orderStatus != 'Accepted') LIMIT 1";
$resNew = $conn->query($sqlNew);
if ($resNew->num_rows > 0) {
    $newOrder = true;
}

$sql = "SELECT orders.id AS orderId, orders.quantity, orders.orderDate, orders.paymentMethod, orders.orderStatus, orders.deliveryCharge,
        users.name, users.email, users.contactno, users.shippingAddress, users.shippingCity, users.shippingState,
        users.shippingPincode, products.productName, products.productCompany, products.productPrice,
        orders.variant_id, pv.variant_label, pv.price AS variant_price,
        orders.buy_price_at_order_time, orders.sell_price_at_order_time,
        CONCAT(users.contactno, '_', orders.orderDate) AS orderSession
        FROM orders
        JOIN users ON orders.userId = users.id
        JOIN products ON orders.productId = products.id
        LEFT JOIN product_variants pv ON orders.variant_id = pv.id";

if ($filterStatus) {
    $sql .= " WHERE orders.orderStatus = '" . $conn->real_escape_string($filterStatus) . "'";
}
$sql .= " ORDER BY $sortBy $sortOrder";

$result = $conn->query($sql);

if ($newOrder) {
    echo '<div data-neworder="yes"></div>';
    echo '<audio id="alertSound" autoplay><source src="assets/audio/notification.mp3" type="audio/mpeg"></audio>';
}

echo '<table><tr>
<th>ID</th><th>Customer</th><th>Contact</th><th>Product</th><th>Company</th><th>Price</th>
<th>Qty</th><th>Shipping</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th><th>Invoice</th><th>Shipping Address</th></tr>';

while ($row = $result->fetch_assoc()) {
    $cls = strtolower(str_replace([' ', '-'], '_', $row['orderStatus'])); 
    
    // Use historical prices from orders table if available, otherwise fallback to current prices
    if (isset($row['sell_price_at_order_time']) && isset($row['buy_price_at_order_time'])) {
        $unitPrice = floatval($row['sell_price_at_order_time']);
    } else if (!empty($row['variant_price'])) {
        $unitPrice = floatval($row['variant_price']);
    } else {
        $unitPrice = floatval($row['productPrice']);
    }
    
    $total = ($row['quantity'] * $unitPrice) + $row['deliveryCharge'];

    $orderDate = new DateTime($row['orderDate'], new DateTimeZone('UTC'));
    $orderDate->setTimezone(new DateTimeZone('Asia/Kolkata'));

    echo '<tr class="' . htmlspecialchars($cls) . '">';
    echo '<td>' . htmlspecialchars($row['orderId']) . '</td>';
    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['contactno']) . '</td>';
    $productName = $row['productName'];
    if (!empty($row['variant_label'])) {
        $productName .= '<br><span style="color:#007bff;font-size:13px;">Variant: ' . htmlspecialchars($row['variant_label']) . '</span>';
    }
    echo '<td>' . $productName . '</td>';
    echo '<td>' . htmlspecialchars($row['productCompany']) . '</td>';
    echo '<td>₹' . htmlspecialchars($unitPrice) . '</td>';
    echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
    echo '<td>₹' . htmlspecialchars($row['deliveryCharge']) . '</td>';
    echo '<td>₹' . htmlspecialchars($total) . '</td>';
    echo '<td>' . htmlspecialchars($row['paymentMethod']) . '</td>';
    echo '<td>' . htmlspecialchars($orderDate->format('d-m-Y H:i:s')) . '</td>';

    // ✅ Badge-style status
   // Update column (buttons instead of dropdown)
echo '<td><div class="status-buttons" data-orderid="' . htmlspecialchars($row['orderId']) . '">';

foreach ($allowedStatuses as $status) {
    $isCurrent = ($row['orderStatus'] === $status);
    $btnClass = $isCurrent ? 'current-status' : 'status-btn';

    echo '<button type="button" 
             class="' . $btnClass . '" 
             data-status="' . htmlspecialchars($status) . '" 
             ' . ($isCurrent ? 'disabled' : '') . '>'
             . htmlspecialchars($status) . 
         '</button> ';
}

echo '</div></td>';


    // Invoice and Send Mail buttons - Only show for first order in session
    $customerEmail = $row['email'] ?? '';
    $orderSession = $row['orderSession'];
    
    // Check if this is the first order in the session
    static $processedSessions = [];
    $isFirstInSession = !in_array($orderSession, $processedSessions);
    
    if ($isFirstInSession) {
        $processedSessions[] = $orderSession;
        echo '<td>';
        // Download Invoice button
        echo '<a href="generateinvoice.php?uid=' . htmlspecialchars($row['contactno']) . '&dt=' . urlencode($row['orderDate']) . '" target="_blank">
            <button type="button" style="margin-bottom: 5px;">Download</button></a><br>';
        
        // Send Mail button
        if (!empty($customerEmail)) {
            echo '<button type="button" class="send-mail-btn" 
                data-order-session="' . htmlspecialchars($orderSession) . '"
                data-customer-email="' . htmlspecialchars($customerEmail) . '"
                data-customer-name="' . htmlspecialchars($row['name']) . '"
                data-order-date="' . htmlspecialchars($orderDate->format('d-m-Y H:i:s')) . '"
                style="background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-bottom: 5px; width: 100%;">
                📧 Send Mail
            </button>';
        } else {
            echo '<span style="color: #6c757d; font-size: 12px;">No email</span><br>';
        }
        
        // Send WhatsApp button
        if (!empty($row['contactno'])) {
            $deliveryAddress = $row['shippingAddress'] . ', ' . $row['shippingCity'] . ', ' . $row['shippingState'] . ' - ' . $row['shippingPincode'];
            echo '<button type="button" 
                onclick="sendWhatsApp(\'' . htmlspecialchars($row['contactno']) . '\', \'' . htmlspecialchars($row['name']) . '\', \'' . htmlspecialchars($row['orderId']) . '\', \'' . htmlspecialchars($row['orderStatus']) . '\', \'' . htmlspecialchars($deliveryAddress) . '\')"
                style="background: #25d366; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; width: 100%;">
                📱 WhatsApp
            </button>';
        } else {
            echo '<span style="color: #6c757d; font-size: 12px;">No contact</span>';
        }
        echo '</td>';
    } else {
        echo '<td></td>'; // Empty cell for subsequent orders in same session
    }

    echo '<td>' . htmlspecialchars($row['shippingAddress']) . ', ' . htmlspecialchars($row['shippingCity']) . ', ' .
         htmlspecialchars($row['shippingState']) . ' - ' . htmlspecialchars($row['shippingPincode']) . '</td>';
    echo '</tr>';
}

echo '</table>';
$conn->close();

// ✅ Function to get background color based on status
function getStatusColor($status) {
    switch ($status) {
        case 'Accepted': return '#28a745';           // Green
        case 'Processing': return '#ffc107';         // Yellow
        case 'Shipped': return '#17a2b8'; // Blue
        case 'Payment_Done': return '#6f42c1';       // Purple
        case 'Delivered': return '#20c997';          // Teal
        default: return '#6c757d';                   // Grey
    }
}
