<?php
session_start();
include('includes/config.php');

$productId = intval($_POST['id']);
$quantity = intval($_POST['quantity']);
$variantId = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;

if ($quantity <= 0) {
    // Remove from cart
    unset($_SESSION['cart'][$productId]);
} else {
    // Update quantity
    if ($variantId) {
        $variant = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM product_variants WHERE id = $variantId"));
        if ($variant) {
            $_SESSION['cart'][$productId] = [
                "quantity" => $quantity,
                "variant_id" => $variantId,
                "variant_label" => $variant['variant_label'],
                "price" => $variant['price']
            ];
        }
    } else {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        } else {
            $product = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM products WHERE id = $productId"));
            if ($product) {
                $_SESSION['cart'][$productId] = [
                    "quantity" => $quantity,
                    "price" => $product['productPrice']
                ];
            }
        }
    }
}

// Calculate cart totals
$cartCount = 0;
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'];
    $cartTotal += $item['quantity'] * $item['price'];
}

echo json_encode([
    'success' => true,
    'cartCount' => $cartCount,
    'cartTotal' => $cartTotal
]);
exit();
?>