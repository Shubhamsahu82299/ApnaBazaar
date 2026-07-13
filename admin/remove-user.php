<?php
session_start();
include('include/config.php');

// Only allow access if admin is logged in
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Delete user from users table
    $query = mysqli_query($con, "DELETE FROM users WHERE id = '$userId'");

    if ($query) {
        $_SESSION['msg'] = "User deleted successfully.";
    } else {
        $_SESSION['msg'] = "Failed to delete user.";
    }
}

header("Location: manage-users.php");
exit();
?>
