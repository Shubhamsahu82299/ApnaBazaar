<?php
include_once('config.php');

$orderId = intval($_POST['order_id']);
$status = $_POST['status'] ?? '';

$allowedStatuses = ['Accepted', 'Processing', 'Shipped from ApnaBazaar', 'Payment_Done', 'Delivered'];

if (in_array($status, $allowedStatuses)) {
    $stmt = $conn->prepare("UPDATE orders SET orderStatus=? WHERE id=?");
    $stmt->bind_param("si", $status, $orderId);
    $stmt->execute();
    $stmt->close();
}
$conn->close();
?>
