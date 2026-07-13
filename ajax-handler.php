<?php
session_start();
include('includes/config.php');
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$response = ['success' => false];

if ($action == "add") {
    $id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        $sql_p = "SELECT * FROM products WHERE id={$id}";
        $query_p = mysqli_query($con, $sql_p);
        if (mysqli_num_rows($query_p) != 0) {
            $row_p = mysqli_fetch_array($query_p);
            $_SESSION['cart'][$row_p['id']] = array("quantity" => 1, "price" => $row_p['productPrice']);
        }
    }
    $response['success'] = true;
    $response['message'] = "Product added to cart!";
}

if ($action == "wishlist") {
    if (strlen($_SESSION['login']) == 0) {
        $response['redirect'] = "login.php";
    } else {
        $pid = intval($_GET['pid']);
        mysqli_query($con, "INSERT INTO wishlist(userId,productId) VALUES('" . $_SESSION['id'] . "','$pid')");
        $response['success'] = true;
        $response['message'] = "Product added to wishlist!";
    }
}

echo json_encode($response);
