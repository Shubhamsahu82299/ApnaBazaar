<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['change'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$id = intval($_POST['id']);
$change = intval($_POST['change']);

if (isset($_SESSION['cart'][$id])) {
  $newQty = $_SESSION['cart'][$id]['quantity'] + $change;

  if ($newQty <= 0) {
    unset($_SESSION['cart'][$id]);
    echo json_encode(['success' => true, 'newQty' => 0]);
  } else {
    $_SESSION['cart'][$id]['quantity'] = $newQty;
    echo json_encode(['success' => true, 'newQty' => $newQty]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
}
