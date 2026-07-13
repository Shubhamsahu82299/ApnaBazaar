<?php
include_once('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderSession = $_POST['order_session'] ?? '';
    $customerContact = $_POST['customer_contact'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $orderStatus = $_POST['order_status'] ?? '';
    
    if (empty($orderSession) || empty($customerContact)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }
    
    // Parse order session (contactno_orderDate)
    $sessionParts = explode('_', $orderSession);
    if (count($sessionParts) !== 2) {
        echo json_encode(['success' => false, 'message' => 'Invalid order session format']);
        exit;
    }
    
    $contactno = $sessionParts[0];
    $orderDateStr = $sessionParts[1];
    
    // Fetch order details
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
    WHERE u.contactno = ? AND o.orderDate = ?
    ORDER BY o.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $contactno, $orderDateStr);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    $totalAmount = 0;
    $orderIds = [];
    
    while ($row = $result->fetch_assoc()) {
        $unitPrice = !empty($row['variant_price']) ? $row['variant_price'] : $row['productPrice'];
        $itemTotal = ($row['quantity'] * $unitPrice) + $row['deliveryCharge'];
        $totalAmount += $itemTotal;
        $orderIds[] = $row['orderid'];
        
        $orders[] = [
            'product' => $row['productName'],
            'variant' => $row['variant_label'],
            'quantity' => $row['quantity'],
            'price' => $unitPrice,
            'total' => $itemTotal,
            'shippingAddress' => $row['shippingAddress'],
            'shippingCity' => $row['shippingCity'],
            'shippingState' => $row['shippingState'],
            'shippingPincode' => $row['shippingPincode']
        ];
    }
    
    $stmt->close();
    
    if (empty($orders)) {
        echo json_encode(['success' => false, 'message' => 'No orders found']);
        exit;
    }
    
    // Format phone number
    $phoneNumber = preg_replace('/[^0-9]/', '', $customerContact);
    if (strlen($phoneNumber) === 10) {
        $phoneNumber = '91' . $phoneNumber;
    }
    
    // Create detailed WhatsApp message
    $message = "🛍️ *ApnaBazaar - Order Update*\n\n";
    $message .= "Hi *{$customerName}*! 👋\n\n";
    $message .= "Your order status has been updated:\n";
    $message .= "📦 *Status:* {$orderStatus}\n";
    $message .= "🆔 *Order ID(s):* " . implode(', #', $orderIds) . "\n\n";
    
    $message .= "*Order Details:*\n";
    foreach ($orders as $order) {
        $message .= "• {$order['product']}";
        if (!empty($order['variant'])) {
            $message .= " ({$order['variant']})";
        }
        $message .= " x{$order['quantity']} - ₹{$order['price']}\n";
    }
    
    $message .= "\n💰 *Total Amount:* ₹{$totalAmount}\n";
    $message .= "📅 *Order Date:* " . date('d-m-Y H:i', strtotime($orderDateStr)) . "\n\n";
    
    // Get delivery address from first order
    $deliveryAddress = $orders[0]['shippingAddress'] . ', ' . $orders[0]['shippingCity'] . ', ' . $orders[0]['shippingState'] . ' - ' . $orders[0]['shippingPincode'];
    
    $message .= "📍 *Current Delivery Address:*\n";
    $message .= "{$deliveryAddress}\n\n";
    
    $message .= "📍 *Share Your Location:*\n";
    $message .= "Click the location button below to share your current location directly on WhatsApp.\n\n";
    
    $message .= "📱 *How to share location:*\n";
    $message .= "• Tap the 📎 (attachment) button\n";
    $message .= "• Select 'Location'\n";
    $message .= "• Choose 'Share live location' or 'Send your current location'\n";
    $message .= "• Send the location\n\n";
    
    $message .= "🚚 *Delivery Options:*\n";
    $message .= "• Confirm current address\n";
    $message .= "• Share updated location\n";
    $message .= "• Request delivery time\n";
    $message .= "• Schedule delivery\n\n";
    
    $message .= "Thank you for choosing *ApnaBazaar*! 🎉\n\n";
    $message .= "For any queries, please contact us:\n";
    $message .= "📞 Customer Support\n";
    $message .= "🌐https://ApnaBazaarservicepoint.store";
  
    // Encode message for URL
    $encodedMessage = urlencode($message);
    
    // Create WhatsApp URL
    $whatsappUrl = "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
    
    echo json_encode([
        'success' => true, 
        'message' => 'WhatsApp message ready',
        'whatsapp_url' => $whatsappUrl,
        'phone_number' => $phoneNumber
    ]);
    
    $conn->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 