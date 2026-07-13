<?php
session_start();
include("includes/config.php");
$_SESSION['login']=="";
date_default_timezone_set('Asia/Kolkata');
$ldate=date( 'd-m-Y h:i:s A', time () );
mysqli_query($con,"UPDATE userlog  SET logout = '$ldate' WHERE userEmail = '".$_SESSION['login']."' ORDER BY id DESC LIMIT 1");
session_unset();
$_SESSION['errmsg']="You have successfully logout";
// Remove persistent login token and cookie
if (isset($_COOKIE['auth_token'])) {
    list($selector, $validator) = explode(':', $_COOKIE['auth_token']);
    mysqli_query($con, "DELETE FROM auth_tokens WHERE selector = '" . mysqli_real_escape_string($con, $selector) . "'");
    setcookie('auth_token', '', time() - 3600, "/");
}
?>
<script language="javascript">
document.location="index.php";
</script>
