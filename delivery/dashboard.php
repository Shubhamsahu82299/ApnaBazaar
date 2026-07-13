<?php
session_start();
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM delivery_users WHERE username = '$username' AND password = '$password'");
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $_SESSION['delivery_login'] = true;
        $_SESSION['delivery_id'] = $row['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
