<?php
session_start();
header('Content-Type: application/json');

if (!empty($_SESSION['shippingAddress'])) {
    $address = $_SESSION['shippingAddress'];
    echo json_encode([
        'status' => 'ok',
        'message' => "Delivering to: <strong>$address</strong>"
    ]);
} else {
    echo json_encode([
        'status' => 'fail'
    ]);
}
