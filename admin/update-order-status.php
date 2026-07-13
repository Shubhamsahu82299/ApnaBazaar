<?php
// update-order-status.php 
include_once('includes/config.php');

$orderId = intval($_POST['order_id']);
$status = $_POST['status'] ?? '';
$remark = $_POST['remark'] ?? '';  // agar remark bhi bhejna hai to

$allowedStatuses = ['Accepted', 'Processing', 'Shipped', 'Payment_Done', 'Delivered'];

if (in_array($status, $allowedStatuses)) {
    // Start transaction (agar aapka MySQL aur config support karta hai)
    $conn->begin_transaction();

    try {
        // 1. Update order status
        $stmt = $conn->prepare("UPDATE orders SET orderStatus=? WHERE id=?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
        $stmt->close();

        // 2. Insert into ordertrackhistory
        $stmt2 = $conn->prepare("INSERT INTO ordertrackhistory (orderId, status, remark, postingDate) VALUES (?, ?, ?, NOW())");
        $stmt2->bind_param("iss", $orderId, $status, $remark);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();

        echo "success";

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo "Error updating order status: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid status";
}

$conn->close();
?>
