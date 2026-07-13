<?php
session_start();
include('includes/config.php');

if (!isset($_POST['bulkMail'])) {
    header("Location: manage-users.php"); exit;
}

$users   = $_POST['selectedUsers'] ?? [];
$subject = trim($_POST['subject']);
$message = trim($_POST['message']);

if (empty($users) || $subject=="" || $message=="") {
    $_SESSION['delmsg'] = "Select at least one user and fill subject/message!";
    header("Location: manage-users.php"); exit;
}

// Email headers
$headers  = "From: MyShop <>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

foreach ($users as $id) {
    $res = mysqli_query($conn, "SELECT email FROM users WHERE id='".intval($id)."'");
    if ($row=mysqli_fetch_assoc($res)) {
        $to = $row['email'];
        // send mail
        mail($to, $subject, nl2br($message), $headers);
    }
}

$_SESSION['msg'] = "Mail sent to selected users!";
header("Location: manage-users.php");
exit;
?>
