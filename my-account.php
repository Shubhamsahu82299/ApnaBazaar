<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0){
    header('location:login.php');
    exit;
}

$msg = "";

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $contactno = $_POST['contactno'];
    $query = mysqli_query($con,"UPDATE users SET name='$name', contactno='$contactno' WHERE id='".$_SESSION['id']."'");
    $msg = $query ? "Profile updated successfully." : "";
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

if(isset($_POST['submit'])){
    $sql = mysqli_query($con,"SELECT password FROM users WHERE password='".md5($_POST['cpass'])."' AND id='".$_SESSION['id']."'");
    $num = mysqli_fetch_array($sql);
    if($num > 0){
        mysqli_query($con,"UPDATE users SET password='".md5($_POST['newpass'])."', updationDate='$currentTime' WHERE id='".$_SESSION['id']."'");
        $msg = "Password changed successfully.";
    } else {
        $msg = "Current password incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <style>
    body { background: #f5f5f5; font-family: 'Roboto', sans-serif; padding-top:110px; }
    .panel { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 6px; }
    .form-group { margin-bottom: 12px; }
    .btn { font-size: 14px; padding: 6px 14px; }
    .msg-box { font-size: 14px; margin-bottom: 15px; padding: 8px 12px; border-radius: 4px; background: #e0f8e9; color: #2b662b; }
    .continue-shopping-btn { border: 1px solid #ccc; background: #f9f9f9; padding: 6px 12px; font-size: 14px; display: inline-block; border-radius: 4px; text-decoration: none; }
    @media (max-width: 576px) {
      .form-control, .btn, .continue-shopping-btn { width: 100%; margin-top: 8px; }
    }
     
        @media (max-width: 768px) {
  body {
    padding-top: 110px; /* mobile header height */
  }
}
  </style>
</head>
<body>

<?php include('includes/top-header.php');?>
<?php include('includes/main-header.php');?>
<?php include('includes/menu-bar.php');?>
<h2 style="text-align:center; padding-top: 10px;padding-buttom:10px; font-weight:600; font-size:18px; color:#333;">
   👤 My Account
</h2>

<div class="container mt-4 mb-5">
  <div class="row">
    <div class="col-md-8">

      <div class="panel">
        <h5 style="padding-top:10px;padding-buttom:10px"><b>My Profile</b></h5>

        <?php if($msg): ?>
          <div class="msg-box"><?php echo htmlentities($msg); ?></div>
        <?php endif; ?>

        <?php
        $query = mysqli_query($con,"SELECT * FROM users WHERE id='".$_SESSION['id']."'");
        while($row = mysqli_fetch_array($query)){
        ?>
        <form method="post">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlentities($row['name']); ?>" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?php echo htmlentities($row['email']); ?>" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label>Contact No.</label>
            <input type="text" name="contactno" value="<?php echo htmlentities($row['contactno']); ?>" class="form-control" maxlength="10" required>
          </div>
          <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>
        <?php } ?>

        <!-- Continue Shopping -->
        <a href="index.php" class="continue-shopping-btn mt-3"><i class="fa fa-arrow-left"></i> Continue Shopping</a>
      </div>

      <!-- Change Password Panel -->
      <div class="panel">
        <h5 style="cursor:pointer;margin-top:-10px" data-toggle="collapse" data-target="#changePwdBox" aria-expanded="false" aria-controls="changePwdBox">
          <b>Change Password</b> <span style="font-size:12px;color:#888;">(Click to toggle)</span>
        </h5>
        <div class="collapse" id="changePwdBox">
          <form method="post" name="chngpwd" onsubmit="return validatePassword();">
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="cpass" class="form-control" required>
            </div>
            <div class="form-group">
              <label>New Password</label>
              <input type="password" name="newpass" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Confirm Password</label>
              <input type="password" name="cnfpass" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Change Password</button>
          </form>
        </div>
      </div>

    </div>
    <?php include('includes/myaccount-sidebar.php'); ?>
  </div>
</div>

<?php include('includes/footer.php');?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
function validatePassword(){
  var newpass = document.chngpwd.newpass.value;
  var cnfpass = document.chngpwd.cnfpass.value;
  if(newpass != cnfpass){
    alert("Passwords do not match!");
    return false;
  }
  return true;
}
</script>

</body>
</html>
