<?php
session_start();
include('includes/config.php');

if (strlen($_SESSION['login']) == 0) {
    header('location:login.php');
    exit;
}

$msg = "";
$shipmsg = "";

// Billing Address Update
if (isset($_POST['update'])) {
    $baddress = $_POST['billingaddress'];
    $bstate = $_POST['bilingstate'];
    $bcity = $_POST['billingcity'];
    $bpincode = $_POST['billingpincode'];
    $query = mysqli_query($con, "UPDATE users SET billingAddress='$baddress', billingState='$bstate', billingCity='$bcity', billingPincode='$bpincode' WHERE id='" . $_SESSION['id'] . "'");
    if ($query) {
        $msg = "Billing address updated successfully.";
    }
}

// Shipping Address Update
if (isset($_POST['shipupdate'])) {
    $saddress = $_POST['shippingaddress'];
    $sstate = $_POST['shippingstate'];
    $scity = $_POST['shippingcity'];
    $spincode = $_POST['shippingpincode'];
    $query = mysqli_query($con, "UPDATE users SET shippingAddress='$saddress', shippingState='$sstate', shippingCity='$scity', shippingPincode='$spincode' WHERE id='" . $_SESSION['id'] . "'");
    if ($query) {
        $shipmsg = "Shipping address updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <style>
         body {
  padding-top: 120px;
  font-family: 'Roboto', sans-serif;
            background: #f9f9f9;
}

@media (max-width: 768px) {
  body {
    padding-top:120px; /* mobile header height */
  }
}
        .card-box {
            background: #fff;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }
        .form-group label {
            font-size: 14px;
            font-weight: 500;
        }
        .btn {
            font-size: 14px;
            padding: 6px 14px;
        }
        .alert {
            font-size: 14px;
            padding: 10px;
        }
        @media (max-width: 768px) {
            .form-col { order: 1; }
            .sidebar-col { order: 2; margin-top: 20px; }
        }
         @media  (min-width: 992px) {
  body {  
    margin-top: 30px; /* mobile header height */
  }
}
        
    </style>
</head>
<body>

<?php include('includes/top-header.php'); ?>
<?php include('includes/main-header.php'); ?>
<?php include('includes/menu-bar.php'); ?>

<div class="container mt-4 mb-5">
    <div class="row d-flex flex-column flex-md-row">
        <!-- Main Form Section -->
        <div class="col-md-8 form-col">
            <!-- Billing Address -->
            

            <!-- Shipping Address -->
            <div class="card-box">
                <div class="section-title">Shipping Address</div>

                <?php if ($shipmsg): ?>
                    <div class="alert alert-success"><?php echo htmlentities($shipmsg); ?></div>
                <?php endif; ?>

                <?php
                $query = mysqli_query($con, "SELECT * FROM users WHERE id='" . $_SESSION['id'] . "'");
                while ($row = mysqli_fetch_array($query)) {
                ?>
                    <form method="post">
                        <div class="form-group">
                            <label>Shipping Address</label>
                            <textarea class="form-control" name="shippingaddress" required><?php echo htmlentities($row['shippingAddress']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Shipping State</label>
                            <input type="text" name="shippingstate" class="form-control" value="<?php echo htmlentities($row['shippingState']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Shipping City</label>
                            <input type="text" name="shippingcity" class="form-control" value="<?php echo htmlentities($row['shippingCity']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Shipping Pincode</label>
                            <input type="text" name="shippingpincode" class="form-control" value="<?php echo htmlentities($row['shippingPincode']); ?>" required>
                        </div>
                        <button type="submit" name="shipupdate" class="btn btn-primary">Update Shipping</button>
                    </form>
                <?php } ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class=" sidebar-col">
            <?php include('includes/myaccount-sidebar.php'); ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>