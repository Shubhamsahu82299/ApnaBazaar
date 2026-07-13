<?php
session_start();
include('includes/config.php');

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['message'] = "Product removed from cart successfully.";
    } else {
        $_SESSION['message'] = "Product not found in cart.";
    }
}

header("Location: my-cart.php");
exit();
?>
