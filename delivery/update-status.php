<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['delivery_login'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['new_status'];
    $paymentMode = $_POST['payment_mode'] ?? null;

    $allowedStatuses = ['Out for Delivery', 'Delivered'];
    $allowedPayments = ['Cash', 'Online'];

    if (!in_array($newStatus, $allowedStatuses)) {
        die("Invalid status");
    }

    if ($newStatus === 'Delivered' && !in_array($paymentMode, $allowedPayments)) {
        die("Please select a valid payment mode for delivered orders.");
    }

    if ($newStatus === 'Delivered') {
        $stmt = $conn->prepare("UPDATE orders SET orderStatus = ?, deliveryPaymentMethod = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newStatus, $paymentMode, $orderId);
    } else {
        $stmt = $conn->prepare("UPDATE orders SET orderStatus = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
    }

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Failed to update.";
    }
}
?>