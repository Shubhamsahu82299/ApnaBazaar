<?php
session_name("admin_session");
session_start();

include('includes/config.php');

// Agar login hai
if (isset($_SESSION['admin_login'])) {
    $username = $_SESSION['admin_login'];

    // ✅ Remove Remember Me tokens from DB
    $stmt = mysqli_prepare($conn, "DELETE FROM admin_sessions WHERE admin_user=?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ✅ Destroy normal session
$_SESSION = [];
session_destroy();

// ✅ Clear Remember Me cookie
setcookie("admin_auth", "", time() - 3600, "/");

// ✅ Clear Remember Me from localStorage
echo "<script>
    localStorage.removeItem('admin_auth');
    window.location.href='index.php';
</script>";
exit;
?>
