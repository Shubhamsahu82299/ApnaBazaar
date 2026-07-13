<?php
// ...existing code...
if ($stmt->execute()) {
    // Get latest order for this user with paymentMethod just set
    $order_query = mysqli_query($con, "SELECT orders.id AS orderId, orders.orderDate, users.contactno FROM orders WHERE userId=$userId AND paymentMethod='$paymethod' ORDER BY orderDate DESC LIMIT 1");
    $latestOrder = mysqli_fetch_assoc($order_query);

    // Order session banayein (same as fetch-orders.php)
    $orderSession = $latestOrder['contactno'] . '_' . $latestOrder['orderDate'];

    // Ab is session ke saare orders fetch karo
    $orders_query = mysqli_query($con, "SELECT orders.id AS orderId, orders.quantity, orders.orderDate, orders.paymentMethod, orders.orderStatus, orders.deliveryCharge,
        users.name, users.email, users.contactno, users.shippingAddress, users.shippingCity, users.shippingState,
        users.shippingPincode, products.productName, products.productCompany, products.productPrice,
        orders.variant_id, pv.variant_label, pv.price AS variant_price
        FROM orders
        JOIN users ON orders.userId = users.id
        JOIN products ON orders.productId = products.id
        LEFT JOIN product_variants pv ON orders.variant_id = pv.id
        WHERE CONCAT(users.contactno, '_', orders.orderDate) = '$orderSession'");

    // User details ek hi baar fetch kar lo
    $user_query = mysqli_query($con, "SELECT * FROM users WHERE id=$userId");
    $user = mysqli_fetch_assoc($user_query);

    $to1 = "";
// Dusra admin email

    $subject = "New Orders Received: Session $orderSession";
    $message = "New orders received in this session!\n\n";
    $message .= "Customer Name: " . $user['name'] . "\n";
    $message .= "Customer Email: " . $user['email'] . "\n";
    $message .= "Contact: " . $user['contactno'] . "\n";
    $message .= "Shipping Address: " . $user['shippingAddress'] . ', ' . $user['shippingCity'] . ', ' . $user['shippingState'] . ' - ' . $user['shippingPincode'] . "\n\n";
    $message .= "Order Details:\n";

    while ($order = mysqli_fetch_assoc($orders_query)) {
        $unitPrice = !empty($order['variant_price']) ? $order['variant_price'] : $order['productPrice'];
        $total = ($order['quantity'] * $unitPrice) + $order['deliveryCharge'];
        $message .= "----------------------\n";
        $message .= "Order ID: " . $order['orderId'] . "\n";
        $message .= "Product: " . $order['productName'] . "\n";
        $message .= "Company: " . $order['productCompany'] . "\n";
        $message .= "Variant: " . $order['variant_label'] . "\n";
        $message .= "Quantity: " . $order['quantity'] . "\n";
        $message .= "Unit Price: ₹" . $unitPrice . "\n";
        $message .= "Delivery Charge: ₹" . $order['deliveryCharge'] . "\n";
        $message .= "Total Amount: ₹" . $total . "\n";
        $message .= "Payment Method: " . $order['paymentMethod'] . "\n";
        $message .= "Order Date: " . $order['orderDate'] . "\n";
        $message .= "Order Status: " . $order['orderStatus'] . "\n";
    }

    mail($to1, $subject, $message);
    mail($to2, $subject, $message);

    unset($_SESSION['cart']);
    header('Location: order-history.php');
    exit;
}
// ...existing code...