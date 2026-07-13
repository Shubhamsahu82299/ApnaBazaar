<?php
session_start();
include('includes/config.php');

// already logged in
if (isset($_SESSION['admin_login'])) return;

// check cookie
if (isset($_COOKIE['auth_token'])) {
    list($selector, $validator) = explode(':', $_COOKIE['auth_token']);
    $stmt = mysqli_prepare($conn, "SELECT user_id, validator, expires FROM auth_tokens WHERE selector=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $selector);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row && $row['expires'] >= date('Y-m-d H:i:s') && password_verify($validator, $row['validator'])) {
        $_SESSION['admin_login'] = str_replace('admin_', '', $row['user_id']);
    }
}

// check localStorage (via JS) if cookie is missing
if (!isset($_SESSION['admin_login']) && !isset($_COOKIE['auth_token'])) {
    echo "<script>
        let token = localStorage.getItem('auth_token');
        if (token) {
            document.cookie = 'auth_token=' + token + '; path=/; max-age=' + (60*60*24*365*10);
            location.reload();
        }
    </script>";
}
?>