<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

ini_set('session.cookie_lifetime', 60*60*24*30);
ini_set('session.gc_maxlifetime', 60*60*24*30);
ini_set('session.cookie_secure', true);
ini_set('session.cookie_httponly', true);
ini_set('session.use_only_cookies', true);
?>
<?php 

session_start(); 
include('includes/config.php');

if (!isset($_SESSION['login']) && isset($_COOKIE['auth_token'])) {
    list($selector, $validator) = explode(':', $_COOKIE['auth_token']);
    $stmt = mysqli_prepare($con, "SELECT * FROM auth_tokens WHERE selector = ? AND expires >= NOW()");
    mysqli_stmt_bind_param($stmt, 's', $selector);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $token = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($token && password_verify($validator, $token['validator'])) {
        $user_id = $token['user_id'];
        $user_query = mysqli_query($con, "SELECT * FROM users WHERE id = " . intval($user_id));
        $user = mysqli_fetch_array($user_query);
        if ($user) {
            $_SESSION['login'] = $user['email'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            
            session_write_close();
            session_start();
            
            $new_selector = bin2hex(random_bytes(16));
            $new_validator = bin2hex(random_bytes(32));
            $new_hashed_validator = password_hash($new_validator, PASSWORD_DEFAULT);
            $new_expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            mysqli_query($con, "DELETE FROM auth_tokens WHERE selector = '" . mysqli_real_escape_string($con, $selector) . "'");
            
            $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $user['id'], $new_selector, $new_hashed_validator, $new_expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            setcookie('auth_token', "$new_selector:$new_validator", time() + 60*60*24*30, "/", "", true, true);
        }
    } else {
        setcookie('auth_token', '', time() - 3600, "/", "", true, true);
    }
}

if (!empty($_SESSION['cart'])) {
    $totalqunty = 0;
    foreach ($_SESSION['cart'] as $id => $val) {
        $totalqunty += $val['quantity'];
    }
    $_SESSION['qnty'] = $totalqunty;
} else {
    $_SESSION['qnty'] = 0;
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 'cart') {
    if (!empty($_SESSION['cart'])) {
       echo "<div class='cart-header-sticky'>
        <a href='my-cart.php' class='go-to-cart-button'>Proceed to Checkout ➔</a>
      </div>";

        $totalprice = 0;
        $sql = "SELECT p.*, v.variant_label 
        FROM products p 
        LEFT JOIN product_variants v 
        ON p.id = v.product_id AND v.price = p.productPrice 
        WHERE p.id IN (" . implode(',', array_map('intval', array_keys($_SESSION['cart']))) . ")";

        $query = mysqli_query($con, $sql);
        while ($row = mysqli_fetch_array($query)) {
            $quantity = $_SESSION['cart'][$row['id']]['quantity'];
            $subtotal = $quantity * $row['productPrice'] + $row['shippingCharge'];
            $totalprice += $subtotal;
            $variant_id = isset($_SESSION['cart'][$row['id']]['variant_id']) ? $_SESSION['cart'][$row['id']]['variant_id'] : null;
            
        echo "<div class='item'>
<img src='" . getProductImage($row['id'], $row['productImage1']) . "' alt='{$row['productName']}' />
<div style='flex: 1;'>
              <div class='item-title'>{$row['productName']} {$row['variant_label']}</div>
              <div style='display: flex; align-items: center; gap: 10px; margin-top: 8px;'>
                <button onclick='updateQty({$row['id']}, -1)' class='qty-button decrease'>−</button>
                <span id='qty-{$row['id']}' class='qty-display'>{$quantity}</span>
                <button onclick='updateQty({$row['id']}, 1)' class='qty-button increase'>+</button>
              </div>
            </div>
          </div>";
        }
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['quantity'] * $item['price'];
        }
        echo "<div class='item total-row'><strong>Total Order Value: ₹$total</strong></div>";
    } else {
        echo "<div class='item empty-cart-msg'>🛒 Your shopping cart is empty.</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ApnaBazaar</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

  <style>
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        user-select: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    body {
        background-color: #f8fafc; 
        padding-top: 140px; 
    }

    /* Light Premium Blurred Header Interface */
    .header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: rgba(255, 255, 255, 0.95); 
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05); 
        border-bottom: 1px solid #f1f5f9;
        z-index: 999999;
        padding: 14px 32px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .logo-account-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 8px;
    }

    /* Energetic & Premium Modern Teal-Green Gradient Logo */
    .brand-logo-text {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.75px;
        text-decoration: none;
        background: linear-gradient(135deg, #0d9488 0%, #10b981 100%); 
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: transform 0.2s ease;
    }
    
    .brand-logo-text:hover {
        transform: scale(1.02);
    }

    .account-link {
        display: block !important;
        visibility: visible !important;
        min-width: 130px;
    }

    /* Integrated Account Component CSS Rules */
    .account-wrapper {
      display: flex;
      justify-content: flex-end;
      padding: 0px 4px;
      position: relative;
      background: transparent;
      flex-shrink: 0;
    }

    .account-toggle-btn {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 2px 10px;
      width: 130px;
      height: 35px;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
      background-color: #ffffff;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.04);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .account-toggle-btn:hover {
      background-color: #f8fafc;
      border-color: #cbd5e1;
      transform: translateY(-1px);
    }

    .text-container {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      line-height: 1.1;
      overflow: hidden;
      width: 100%;
    }

    .greeting {
      font-size: 9px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.2px;
      white-space: nowrap;
    }

    .username {
      font-size: 11px;
      font-weight: 700;
      color: #1e293b;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      width: 100%;
      text-align: left;
    }

    .account-toggle-btn.morning i { color: #f59e0b; }
    .account-toggle-btn.afternoon i { color: #10b981; }
    .account-toggle-btn.evening i { color: #f43f5e; }
    .account-toggle-btn.night i { color: #6366f1; }

    .account-toggle-btn i {
      font-size: 14px;
      transition: transform 0.2s;
      flex-shrink: 0;
    }

    .account-toggle-btn:hover i {
      transform: scale(1.08);
    }

    .account-menu {
      display: none;
      position: absolute;
      top: 42px;
      right: 0;
      background-color: #ffffff;
      border-radius: 12px;
      width: 170px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
      z-index: 99999;
      overflow: hidden;
      animation: menuSlideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes menuSlideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .account-menu.show { display: block; }
    .account-menu ul { margin: 0; padding: 4px; list-style: none; }
    
    .account-menu a {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      font-size: 13px;
      font-weight: 600;
      color: #475569;
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.15s ease;
    }

    .account-menu a i { font-size: 14px; color: #94a3b8; transition: color 0.15s; }
    .account-menu a:hover { background-color: #f1f5f9; color: #0d9488; }
    .account-menu a:hover i { color: #0d9488; }

    /* Global Search and Actions Alignment Frame */
    .search-bar-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
    }

    .search-container-box {
        flex: 1;
        min-width: 0;
    }

    .actions-container {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    /* Refreshed Light Location Widget System */
    .premium-loc-box {
        display: inline-flex;
        align-items: center;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 0 12px;
        border-radius: 8px;
        height: 38px;
        max-width: 240px;
        box-sizing: border-box;
        flex-shrink: 1;
        min-width: 0;
    }

    .premium-loc-box span {
        font-size: 12px;
        font-weight: 700;
        color: #ef4444;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .check-delivery-link {
        color: #0d9488;
        cursor: pointer;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.2s;
        margin-left: 6px;
        font-size: 11px;
        white-space: nowrap;
    }
    .check-delivery-link:hover {
        color: #059669;
        text-decoration: underline;
    }

    /* Unified Premium Action Buttons Framework */
    .nav-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 700;
        color: #475569 !important;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        padding: 0 14px;
        border-radius: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
        height: 38px;
        box-sizing: border-box;
        flex-shrink: 0;
    }
    
    .nav-action-btn:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        transform: translateY(-1px);
    }

    .nav-action-btn i {
        font-size: 13px;
    }

    .nav-badge-count {
        background-color: #0d9488;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 6px;
        position: absolute;
        top: -6px;
        right: -6px;
        box-shadow: 0 2px 4px rgba(13, 148, 136, 0.2);
    }

    .cart-section {
        position: relative;
    }

    .dropdown-cart {
        display: none;
        position: absolute;
        top: 46px;
        right: 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        width: 360px;
        max-height: 480px;
        overflow-y: auto;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        z-index: 100000;
    }

    .dropdown-cart.show {
        display: block;
        animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .cart-header-sticky {
        position: sticky;
        top: 0;
        background: #f8fafc;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
        z-index: 10;
    }

    .go-to-cart-button {
        background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
        color: #ffffff; 
        padding: 11px 18px; 
        font-size: 14px; 
        font-weight: 700; 
        border: none; 
        border-radius: 10px; 
        text-decoration: none; 
        display: inline-block;
        text-align: center;
        width: 100%;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        transition: opacity 0.2s, transform 0.2s;
    }
    
    .go-to-cart-button:hover {
        opacity: 0.95;
    }

    .dropdown-cart .item {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 14px;
        background: white;
    }

    .dropdown-cart .item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .item-title {
        font-weight: 600;
        color: #0f172a;
        font-size: 13px;
        line-height: 1.4;
    }

    .total-row {
        background: #f8fafc !important;
        font-size: 14px !important;
        font-weight: 700;
        color: #0f172a;
        justify-content: center;
        padding: 16px !important;
        border-top: 1px dashed #cbd5e1;
    }
    
    .total-row strong {
        color: #0d9488;
    }

    .empty-cart-msg {
        padding: 40px 16px !important;
        color: #64748b;
        text-align: center;
        justify-content: center;
        font-weight: 500;
    }

    .qty-button {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        transition: all 0.2s;
    }

    .qty-button.decrease { color: #ef4444; font-weight: bold; }
    .qty-button.increase { color: #10b981; font-weight: bold; }
    .qty-button:hover { background: #f8fafc; border-color: #94a3b8; }

    .qty-display {
        min-width: 24px;
        font-weight: 700;
        font-size: 13px;
        text-align: center;
        color: #0f172a;
    }

    #loc-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.4); 
        backdrop-filter: blur(8px);
        z-index: 9999999;
        justify-content: center;
        align-items: center;
    }

    .popup-card {
        background: #fff;
        padding: 28px;
        border-radius: 20px;
        width: 90%;
        max-width: 340px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        text-align: center;
        border: 1px solid #e2e8f0;
    }

    .popup-card h4 {
        margin-bottom: 14px;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .popup-card input {
        padding: 12px;
        width: 100%;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        margin-bottom: 14px;
        outline: none;
        text-align: center;
        font-weight: 600;
        color: #0f172a;
        transition: border-color 0.2s;
    }
    .popup-card input:focus {
        border-color: #0d9488;
    }

    .popup-btn-group {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
    }

    .popup-btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.2s;
    }

    .btn-submit { background: #0d9488; color: #fff; }
    .btn-submit:hover { background: #059669; }
    .btn-gps { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
    .btn-gps:hover { background: #e2e8f0; }
    .btn-close { background: transparent; color: #64748b; font-size: 13px; font-weight: 600; }
    .btn-close:hover { color: #334155; }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Flawless Adaptive Media Viewports */
    @media screen and (max-width: 768px) {
        body { padding-top: 175px; }
        .header { padding: 12px 18px; gap: 8px; }
        .brand-logo-text { font-size: 22px; }
        .search-bar-wrapper { flex-direction: column; gap: 8px; align-items: stretch; }
        .search-container-box { width: 100%; }
        .actions-container { width: 100%; justify-content: space-between; gap: 8px; }
        
        .premium-loc-box {
            flex-grow: 1;
            max-width: none;
        }

        .dropdown-cart { 
            width: 100vw; 
            max-width: 100vw; 
            position: fixed; 
            top: 165px; 
            left: 0; 
            right: 0;
            border-radius: 0 0 16px 16px; 
            border-left: none; 
            border-right: none; 
            box-shadow: 0 15px 20px rgba(0,0,0,0.08);
        }

        .nav-action-btn {
            padding: 0;
            width: 38px; 
        }
        
        .btn-label-text {
            display: none !important; 
        }
        
        .nav-action-btn i {
            margin: 0 !important;
        }

        .account-wrapper { justify-content: center; }
        .account-toggle-btn { 
          font-size: 12px; 
          padding: 2px 8px; 
          width: 115px; 
          height: 33px; 
          gap: 4px; 
        }
        .account-menu { width: 150px; top: 38px; }
    }
    @media screen and (max-width: 768px) {
        .dropdown-cart { 
            width: 100vw !important; 
            max-width: 100vw !important; 
            position: fixed !important; 
            top: 125px !important; 
            left: 0 !important; 
            right: 0 !important;
            margin: 0 auto;
            margin-top:10px;
            border-radius: 0 0 16px 16px; 
            border-left: none; 
            border-right: none; 
            box-shadow: 0 15px 20px rgba(0,0,0,0.08);
        }
    }
  </style>
</head>
<body>

  <!-- Sticky Premium Navigation Header -->
  <div class="header" id="mainHeader">
    
    <!-- Branding & Identity Segment -->
    <div class="logo-account-wrapper">
      <a href="index.php" class="brand-logo-text">ApnaBazaar</a>
      <div class="account-link">
        
        <!-- DIRECT CONTEXT INTEGRATION OF ACCOUNT COMPONENT -->
        <div class="account-wrapper">
        <?php 
        if (isset($_SESSION['login']) && $_SESSION['login']) {
            $hour = date('H');
            if ($hour >= 5 && $hour < 12) { $greeting = "Good Morning"; $icon = "fas fa-sun"; $colorClass = "morning"; }
            elseif ($hour >= 12 && $hour < 17) { $greeting = "Good Afternoon"; $icon = "fas fa-leaf"; $colorClass = "afternoon"; }
            elseif ($hour >= 17 && $hour < 21) { $greeting = "Good Evening"; $icon = "fas fa-moon"; $colorClass = "evening"; }
            else { $greeting = "Good Night"; $icon = "fas fa-star"; $colorClass = "night"; }
        ?>
          <button class="account-toggle-btn <?php echo $colorClass; ?>" onclick="toggleAccountMenu()">
            <i class="<?php echo $icon; ?>"></i>
            <div class="text-container">
              <span class="greeting"><?php echo $greeting; ?></span>
              <span class="username"><?php echo htmlentities($_SESSION['username']); ?></span>
            </div>
          </button>
        <?php 
        } else {
        ?>
          <a href="login.php" class="account-toggle-btn night">
            <i class="fas fa-user-circle"></i>
            <div class="text-container">
              <span class="greeting">Welcome</span>
              <span class="username">Login</span>
            </div>
          </a>
        <?php 
        }
        ?>

          <div class="account-menu" id="accountMenu">
            <ul>
              <?php if(!isset($_SESSION['login']) || strlen($_SESSION['login']) == 0) { ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
              <?php } else { ?>
                <li><a href="my-account.php"><i class="fas fa-user-cog"></i> My Account</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
              <?php } ?>
            </ul>
          </div>
        </div>

      </div>
    </div>

    <!-- Live Utility Actions Bar -->
    <div class="search-bar-wrapper">
      
      <!-- Interactive Search Block -->
      <div class="search-container-box">
        <?php include('searchbar.php'); ?>
      </div>
      
      <!-- Operational Tools Console -->
      <div class="actions-container">
        
        <!-- Smart Geolocation Framework -->
        <div id="loc-box" class="premium-loc-box">
          <span id="loc-status"></span>
          <a href="javascript:void(0);" onclick="openLocPopup()" class="check-delivery-link">Location Check</a>
        </div>

        <!-- Premium Shopping Cart Mechanism -->
        <div class="cart-section" style="position: relative; flex-shrink: 0;">
          <a href="javascript:void(0);" class="cart-toggle nav-action-btn" onclick="toggleCartDropdown()">
            <i class="fas fa-shopping-cart" style="margin-right: 6px;"></i>
            <span class="btn-label-text">Basket</span>
            <span class="nav-badge-count"><?php echo isset($_SESSION['qnty']) ? $_SESSION['qnty'] : '3'; ?></span>
          </a>

          <div class="dropdown-cart" id="cartDropdown" style="position: absolute; right: 0; top: 44px; z-index: 1000;">
            <div class="cart-header-sticky">
              <strong style="color: #0f172a; font-size: 16px; font-weight: 700;">My Shopping Basket</strong>
              <button onclick="closeCartDropdown()" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 18px;"><i class="fas fa-times"></i></button>
            </div>
            <div id="cartItems"></div>
          </div>
        </div>

        <!-- Conversion Magnet Order Tracker Button -->
        <a href="order-history.php" class="track-order-btn nav-action-btn">
          <i class="fas fa-truck" style="margin-right: 6px;"></i>
          <span class="btn-label-text">Track</span>
        </a>

      </div>
    </div>

  </div>

  <!-- Delivery Bounds Radar Modal Overlay -->
  <div id="loc-popup">
    <div class="popup-card">
      <h4>Check Delivery Serviceability</h4>
      <input type="text" id="loc-pin" placeholder="Enter Area Pincode" maxlength="6">
      <div class="popup-btn-group">
        <button onclick="checkLocPin()" class="popup-btn btn-submit">Verify Area</button>
        <button onclick="detectLoc()" class="popup-btn btn-gps">🌐 Use Live GPS</button>
      </div>
      <button onclick="closeLocPopup()" class="popup-btn btn-close">Close</button>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    function toggleAccountMenu(){
      const menu = document.getElementById('accountMenu');
      if (menu) menu.classList.toggle('show');
    }

    window.addEventListener('click', function(e) {
      const wrapper = document.querySelector('.account-wrapper');
      if (wrapper && !wrapper.contains(e.target)) {
        const menu = document.getElementById('accountMenu');
        if(menu) menu.classList.remove('show');
      }
    });

    function toggleCartDropdown() {
      const dropdown = document.getElementById('cartDropdown');
      dropdown.classList.toggle('show');
      if (window.cartDropdownTimeout) clearTimeout(window.cartDropdownTimeout);
      window.cartDropdownTimeout = setTimeout(() => {
        dropdown.classList.remove('show');
      }, 8000);
    }

    function closeCartDropdown() {
      document.getElementById('cartDropdown').classList.remove('show');
    }

    function loadCartDropdown() {
      fetch('?ajax=cart')
        .then(res => res.text())
        .then(data => {
          document.getElementById('cartItems').innerHTML = data;
        });
    }

    setInterval(loadCartDropdown, 2500);
    window.addEventListener('DOMContentLoaded', loadCartDropdown);

    function updateQty(productId, change) {
      fetch('update-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${productId}&change=${change}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const qtyElement = document.getElementById(`qty-${productId}`);
          if(qtyElement) qtyElement.innerText = data.newQty;
          loadCartDropdown();
        } else {
          alert(data.message || 'Something went wrong.');
        }
      });
    }

    // Geolocation Logistics Matrix
    const storeLat = 21.1642332, storeLon = 81.3231649, maxDist = 15;
    const validTime = 1800 * 60 * 1000;

    function distKm(lat1,lon1,lat2,lon2){
      const R=6371, dLat=(lat2-lat1)*Math.PI/180, dLon=(lon2-lon1)*Math.PI/180;
      const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
      return R*(2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)));
    }

    function saveSession(r){ sessionStorage.setItem("locData",JSON.stringify({r,t:Date.now()})); }
    function loadSession(){
      let s=sessionStorage.getItem("locData");
      if(s){let {r,t}=JSON.parse(s);
        if(Date.now()-t<validTime){
          document.getElementById("loc-status").innerHTML=r;
          document.querySelector(".check-delivery-link").style.display="none";
          return true;
        }
      }
      return false;
    }

    function checkLocPin(){
      let p=document.getElementById("loc-pin").value.trim();
      if(!/^\d{6}$/.test(p)){alert("Enter valid 6-digit pincode");return;}
      fetch(`https://nominatim.openstreetmap.org/search?postalcode=${p}&country=India&format=json`)
      .then(r=>r.json()).then(d=>{
        if(d.length>0){
          let lat=parseFloat(d[0].lat),lon=parseFloat(d[0].lon);
          processLoc(lat,lon,"Pin "+p);
        }else{document.getElementById("loc-status").innerHTML="<span style='color:#ef4444;font-weight:700;'>❌ Invalid Pincode</span>";}
        closeLocPopup();
      }).catch(()=>{document.getElementById("loc-status").innerHTML="<span style='color:#ef4444;font-weight:700;'>❌ Error</span>";closeLocPopup();});
    }

    function detectLoc() {
      if (!("geolocation" in navigator)) { alert("❌ GPS not supported"); return; }
      navigator.geolocation.getCurrentPosition(
        pos => {
          processLoc(pos.coords.latitude, pos.coords.longitude, "📍 GPS");
          closeLocPopup();
        },
        err => { alert("⚠️ Location Access Denied."); },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    }

    function processLoc(lat,lon,src){
      let d=distKm(storeLat,storeLon,lat,lon);
      let st=d<=maxDist?`<span style='color:#10b981;font-weight:700;'>✅ Servicing Area</span>`:`<span style='color:#ef4444;font-weight:700;'>❌ Unserviceable</span>`;
      let r=`${st} (${d.toFixed(1)} km)`;
      document.getElementById("loc-status").innerHTML=r;
      document.querySelector(".check-delivery-link").style.display="none";
      saveSession(r);
    }

    if (!loadSession()) {
      document.getElementById("loc-status").innerHTML = "⚠️ Check availability";
    }
  </script>
</body>
</html>