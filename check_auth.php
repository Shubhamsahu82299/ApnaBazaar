<?php
session_start();
include("includes/config.php");

// Skip if user is already logged in
if (isset($_SESSION['id'])) {
    return;
}

// Check if cookies exist
if (!isset($_COOKIE['remember_selector']) || !isset($_COOKIE['remember_validator'])) {
    return;
}

$selector = $_COOKIE['remember_selector'];
$validator = $_COOKIE['remember_validator'];

// Fetch token from DB
$stmt = mysqli_prepare($con, "SELECT * FROM auth_tokens WHERE selector = ? AND expires >= NOW()");
mysqli_stmt_bind_param($stmt, "s", $selector);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$token = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($token) {
    // Validate the hashed validator
    if (hash_equals($token['validator'], hash('sha256', $validator))) {
        // Token is valid — log the user in
        $userId = $token['user_id'];

        // Get user info
        $userQuery = mysqli_query($con, "SELECT * FROM users WHERE id = $userId LIMIT 1");
        if ($user = mysqli_fetch_assoc($userQuery)) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['login'] = $user['email'];
            $_SESSION['username'] = $user['name'];

            // (Optional) refresh token to extend expiration
            require_once("refresh_token.php"); // You can create this to rotate tokens
        }
    } else {
        // Invalid validator — remove token and clear cookies (possible theft attempt)
        $stmt = mysqli_prepare($con, "DELETE FROM auth_tokens WHERE selector = ?");
        mysqli_stmt_bind_param($stmt, "s", $selector);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        setcookie("remember_selector", "", time() - 3600, "/", "", true, true);
        setcookie("remember_validator", "", time() - 3600, "/", "", true, true);
    }
}
?>
