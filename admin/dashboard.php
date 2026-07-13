<?php
session_start();
include('includes/config.php');

// ==============================
// 🔹 Check session first
// ==============================
if (isset($_SESSION['admin_login'])) {
    $username = $_SESSION['admin_login'];
} 
// ==============================
// 🔹 Check Remember Me cookie
// ==============================
elseif (isset($_COOKIE['admin_auth'])) {
    list($selector, $validator) = explode(':', $_COOKIE['admin_auth']);
    $selector = mysqli_real_escape_string($conn, $selector);

    $stmt = mysqli_prepare($conn, "SELECT * FROM admin_sessions WHERE session_selector = ? AND session_expires >= NOW()");
    mysqli_stmt_bind_param($stmt, "s", $selector);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $token = mysqli_fetch_assoc($result);

    if ($token && password_verify($validator, $token['session_validator'])) {
        // ✅ Auto login
        $_SESSION['admin_login'] = $token['admin_user'];
        $username = $_SESSION['admin_login'];

        // 🔄 Rotate token (for better security)
        $newSelector = bin2hex(random_bytes(16));
        $newValidator = bin2hex(random_bytes(32));
        $hashed_validator = password_hash($newValidator, PASSWORD_DEFAULT);
        $expires = date('Y-m-d H:i:s', strtotime('+365 days'));
        $admin_user = $token['admin_user'];

        // Delete old token
        mysqli_query($conn, "DELETE FROM admin_sessions WHERE session_selector = '".$selector."'");

        // Insert new token
        $stmt = mysqli_prepare($conn, "INSERT INTO admin_sessions (admin_user, session_selector, session_validator, session_expires) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $admin_user, $newSelector, $hashed_validator, $expires);
        mysqli_stmt_execute($stmt);

        // Update cookie
        setcookie('admin_auth', "$newSelector:$newValidator", time() + 60*60*24*365, "/", "", true, true);
    } else {
        // ❌ Invalid cookie → remove and redirect
        setcookie("admin_auth", "", time() - 3600, "/");
        header('location:index.php');
        exit;
    }
} else {
    // ❌ No session & no cookie → redirect to login
    header('location:index.php');
    exit;
}

// ✅ At this point, $username is set and admin is logged in
include('manage-orders.php');
?>
