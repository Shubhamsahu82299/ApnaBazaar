<?php
include_once('includes/config.php');
session_start();

// ✅ Persistent login using cookie
if (!isset($_SESSION['admin_login']) && isset($_COOKIE['auth_token'])) {
    list($selector, $validator) = explode(':', $_COOKIE['auth_token']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM auth_tokens WHERE selector = ? AND expires >= NOW()");
    mysqli_stmt_bind_param($stmt, 's', $selector);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $token = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($token && strpos($token['user_id'], 'admin_') === 0 && password_verify($validator, $token['validator'])) {
        $admin_username = substr($token['user_id'], 6);
        $admin_query = mysqli_query($conn, "SELECT * FROM admin WHERE username='" . mysqli_real_escape_string($conn, $admin_username) . "'");
        $admin = mysqli_fetch_array($admin_query);
        if ($admin) {
            session_regenerate_id(true);
            $_SESSION['admin_login'] = $admin_username;

            // Rotate token
            $new_selector = bin2hex(random_bytes(16));
            $new_validator = bin2hex(random_bytes(32));
            $new_hashed_validator = password_hash($new_validator, PASSWORD_DEFAULT);
            $new_expires = date('Y-m-d H:i:s', strtotime('+30 days'));

            mysqli_query($conn, "DELETE FROM auth_tokens WHERE selector='" . mysqli_real_escape_string($conn, $selector) . "'");
            $stmt = mysqli_prepare($conn, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            $admin_user_id = 'admin_' . $admin_username;
            mysqli_stmt_bind_param($stmt, 'ssss', $admin_user_id, $new_selector, $new_hashed_validator, $new_expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            setcookie('auth_token', "$new_selector:$new_validator", time()+60*60*24*30, "/", "", true, true);
        }
    } else {
        setcookie('auth_token', '', time() - 3600, "/");
    }
}

// Redirect if not logged in
if (!isset($_SESSION['admin_login'])) {
    header('location:index.php');
    exit;
}

// Optional: current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel | ApnaBazaar Pvt. Ltd.</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }

    .admin-nav {
      background: #212529;
      display: flex;
      flex-wrap: nowrap;
      overflow-x: auto;
      white-space: nowrap;
      padding: 5px 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .admin-nav::-webkit-scrollbar {
      display: none;
    }

    .admin-nav a {
      flex: 1 0 auto;
      padding: 6px 10px;
      color: #ccc;
      text-decoration: none;
      text-align: center;
      font-size: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border-bottom: 2px solid transparent;
      transition: all 0.3s ease;
      line-height: 1.2;
    }

    .admin-nav a i {
      font-size: 14px;
      margin-bottom: 2px;
    }

    .admin-nav a.active,
    .admin-nav a:hover {
      color: #fff;
      border-bottom: 2px solid #ffc107;
      background: #343a40;
    }

    .admin-nav a.logout {
      color: #ff4d4d !important;
    }

    .admin-header-title {
      background: #212529;
      color: #ffc107;
      font-weight: bold;
      font-size: 16px;
      padding: 8px 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    #newOrderAlert {
      color: red;
      font-weight: bold;
      font-size: 14px;
    }

    @media screen and (max-width: 768px) {
      .admin-nav a {
        font-size: 11px;
        padding: 5px;
      }

      .admin-nav a i {
        font-size: 13px;
      }

      .admin-header-title {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }
    }
  </style>
</head>
<body>

<!-- 🔥 Admin Title + New Order Alert -->
<div class="admin-header-title">
  <div><i class="fas fa-user-shield"></i> Admin Panel</div>
  <div id="newOrderAlert"></div>
</div>

<!-- 🔔 Alert Sound -->
<audio id="newOrderTone" src="assets/audio/notification.mp3" preload="auto"></audio>

<!-- ✅ Navigation Bar -->
<nav class="admin-nav">
  <a href="sales-report.php" class="<?= $currentPage == 'sales-report.php' ? 'active' : '' ?>">
    <i class="fas fa-chart-line"></i> Sales Report
  </a>
  <a href="manage-orders.php" class="<?= $currentPage == 'manage-orders.php' ? 'active' : '' ?>">
    <i class="fas fa-boxes-stacked"></i> Orders
  </a>
  <a href="add-category.php" class="<?= $currentPage == 'add-category.php' ? 'active' : '' ?>">
    <i class="fas fa-layer-group"></i> Category
  </a>
  <a href="add-subcategory.php" class="<?= $currentPage == 'add-subcategory.php' ? 'active' : '' ?>">
    <i class="fas fa-code-branch"></i> Subcategory
  </a>
  <a href="insert-product.php" class="<?= $currentPage == 'insert-product.php' ? 'active' : '' ?>">
    <i class="fas fa-plus-circle"></i> Add Product
  </a>
  <a href="manage-products.php" class="<?= $currentPage == 'manage-products.php' ? 'active' : '' ?>">
    <i class="fas fa-cogs"></i> Manage Products
  </a>
  <a href="manage-users.php" class="<?= $currentPage == 'manage-users.php' ? 'active' : '' ?>">
    <i class="fas fa-user-group"></i> Users
  </a>
  <a href="logout.php" class="logout">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</nav>

<!-- 🔁 Order Check Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let lastOrderStatus = false;

function checkNewOrders() {
  $.ajax({
    url: 'includes/check-new-orders.php',
    method: 'GET',
    success: function (response) {
      const data = JSON.parse(response);
      const alertBox = document.getElementById('newOrderAlert');
      const audio = document.getElementById("newOrderTone");

      if (data.newOrder) {
        if (!lastOrderStatus) {
          alertBox.innerText = `🔔 New Order Received!`;
          audio.play();
          setTimeout(() => {
            alertBox.innerText = '';
          }, 3000);
        }
        lastOrderStatus = true;
      } else {
        alertBox.innerText = '';
        lastOrderStatus = false;
      }
    }
  });
}

setInterval(checkNewOrders, 5000);
window.addEventListener('DOMContentLoaded', checkNewOrders);
</script>

</body>
</html>