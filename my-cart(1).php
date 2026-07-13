<?php
session_start();
error_reporting(0);
include('includes/config.php');
$con->query("SET time_zone = '+05:30'");

// Check if Buy Now product exists
if (!isset($_SESSION['buynow_product'])) {
    header("Location: my-cart.php");
    exit();
}
// Remove Buy Now product
if (isset($_POST['remove_buynow'])) {
    unset($_SESSION['buynow_product']);
    header("Location: index.php"); // or my-cart.php if you prefer
    exit;
}
if (isset($_POST['shipupdate']) && isset($_SESSION['id'])) {
    $address = mysqli_real_escape_string($con, $_POST['shippingaddress']);
    $state = mysqli_real_escape_string($con, $_POST['shippingstate']);
    $city = mysqli_real_escape_string($con, $_POST['shippingcity']);
    $pincode = mysqli_real_escape_string($con, $_POST['shippingpincode']);

    $uid = $_SESSION['id'];
    $update = mysqli_query($con, "UPDATE users SET 
        shippingAddress='$address',
        shippingState='$state',
        shippingCity='$city',
        shippingPincode='$pincode'
        WHERE id='$uid'");

    if ($update) {
        // Optional: Message or redirect
        header("Location: " . $_SERVER['REQUEST_URI']); // reload to reflect new address
        exit;
    } else {
        $msg = "❌ Address update failed.";
    }
}

$buynow_product = $_SESSION['buynow_product'];
$pid = $buynow_product['id'];

// Fetch shipping address (same as your existing code)
$shippingAddress = $shippingState = $shippingCity = $shippingPincode = '';
$hasAddress = false;
$msg = '';
if (isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];
    $result = mysqli_query($con, "SELECT shippingAddress, shippingState, shippingCity, shippingPincode FROM users WHERE id='$uid'");
    $row = mysqli_fetch_assoc($result);
    if ($row && ($row['shippingAddress'] || $row['shippingState'] || $row['shippingCity'] || $row['shippingPincode'])) {
        $shippingAddress = $row['shippingAddress'];
        $shippingState = $row['shippingState'];
        $shippingCity = $row['shippingCity'];
        $shippingPincode = $row['shippingPincode'];
        $hasAddress = true;
    }
}


// AJAX Quantity Update - Only affects Buy Now product
if (isset($_POST['ajax_update']) && isset($_POST['qty'])) {
    $qty = intval($_POST['qty']);
    if ($qty == 0) {
        unset($_SESSION['buynow_product']);
    } else {
        $_SESSION['buynow_product']['quantity'] = $qty;
    }
    echo json_encode(['status' => 'success']);
    exit;
}
// Include stock management functions
include('includes/stock-management.php');

// Place Order - Only processes Buy Now product
if (isset($_POST['ordersubmit'])) {
    if (strlen($_SESSION['login']) == 0) {
        header('location:login.php');
        exit;
    } else {
        // Only process the Buy Now product
        $pid = $_SESSION['buynow_product']['id'];
        $qty = $_SESSION['buynow_product']['quantity'];
        $variant_id = isset($_SESSION['buynow_product']['variant_id']) ? $_SESSION['buynow_product']['variant_id'] : null;
        $deliveryCharge = ($_SESSION['buynow_product']['price'] * $qty < 300) ? 10 : 0;
        
        // Check stock availability before placing order
        if ($variant_id) {
            // Check variant stock
            if (!checkVariantStock($con, $variant_id, $qty)) {
                $msg = "Order cannot be placed: Insufficient stock for the requested variant quantity.";
            } else {
                // Stock is available, proceed with order
            }
        } else {
            // Check product stock
            if (!checkProductStock($con, $pid, $qty)) {
                $msg = "Order cannot be placed: Insufficient stock for the requested quantity.";
            } else {
                // Stock is available, proceed with order
            }
        }
        
        if (empty($msg)) {
            // Get current prices for the product/variant
            $buy_price = 0.00;
            $sell_price = 0.00;
            
            // Get product base prices
            $product_query = "SELECT base_buy_price, productPrice FROM products WHERE id = ?";
            $stmt = mysqli_prepare($con, $product_query);
            mysqli_stmt_bind_param($stmt, "i", $pid);
            mysqli_stmt_execute($stmt);
            $product_result = mysqli_stmt_get_result($stmt);
            $product_data = mysqli_fetch_assoc($product_result);
            
            $base_buy_price = $product_data['base_buy_price'] ?? 0.00;
            $base_sell_price = $product_data['productPrice'] ?? 0.00;
            
            // If variant exists, get variant prices
            if ($variant_id) {
                $variant_query = "SELECT variant_buy_price, price FROM product_variants WHERE id = ?";
                $stmt = mysqli_prepare($con, $variant_query);
                mysqli_stmt_bind_param($stmt, "i", $variant_id);
                mysqli_stmt_execute($stmt);
                $variant_result = mysqli_stmt_get_result($stmt);
                $variant_data = mysqli_fetch_assoc($variant_result);
                
                if ($variant_data) {
                    $buy_price = $variant_data['variant_buy_price'] ?? $base_buy_price;
                    $sell_price = $variant_data['price'] ?? $base_sell_price;
                } else {
                    $buy_price = $base_buy_price;
                    $sell_price = $base_sell_price;
                }
            } else {
                $buy_price = $base_buy_price;
                $sell_price = $base_sell_price;
            }
            
            if ($variant_id) {
                // Update variant stock
                updateVariantStock($con, $variant_id, $qty);
                mysqli_query($con, "INSERT INTO orders(userId, productId, quantity, deliveryCharge, variant_id, buy_price_at_order_time, sell_price_at_order_time)
                                  VALUES('" . $_SESSION['id'] . "','$pid','$qty','$deliveryCharge','$variant_id','$buy_price','$sell_price')");
            } else {
                // Update product stock
                updateProductStock($con, $pid, $qty);
                mysqli_query($con, "INSERT INTO orders(userId, productId, quantity, deliveryCharge, buy_price_at_order_time, sell_price_at_order_time)
                                  VALUES('" . $_SESSION['id'] . "','$pid','$qty','$deliveryCharge','$buy_price','$sell_price')");
            }

            // Optionally add to main cart (if you want it to persist)
            // $_SESSION['cart'][$pid] = $_SESSION['buynow_product'];
            
            // Clear the Buy Now product
            unset($_SESSION['buynow_product']);
            
            header("Location: payment-method(1).php");
            exit;
        }
    }
}

// Get product details
$product_query = mysqli_query($con, "SELECT * FROM products WHERE id = $pid");
$product = mysqli_fetch_assoc($product_query);
$variant_id = isset($buynow_product['variant_id']) ? $buynow_product['variant_id'] : null; // ✅ ADD THIS
// Calculate totals
$item_price = isset($buynow_product['price']) ? $buynow_product['price'] : $product['productPrice'];
$subtotal = $buynow_product['quantity'] * $item_price;
$deliveryCharge = ($subtotal < 300) ? 10 : 0;
$grandTotal = $subtotal + $deliveryCharge;
$_SESSION['delivery_charge'] = $deliveryCharge;
$_SESSION['tp'] = $grandTotal . ".00";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Express Checkout</title>
  <!-- Same header includes as your my-cart.php -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/font-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Same styles as your my-cart.php */
    body {margin-top:50px; background-color: #f1f3f6; font-family: 'Segoe UI', sans-serif; padding-top:60px}
    .cart-img { width: 100%; max-width: 120px; height: 120px; object-fit: contain; border: 1px solid #ddd; padding: 8px; background: #f9f9f9; border-radius: 8px;}
    .cart-item { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 16px; }
    .qty-box { width: 50px; text-align: center; }
    .sticky-bottom-bar { position: sticky; bottom: 0; z-index: 1000; background: #fff; border-top: 1px solid #ddd; padding: 10px 16px; }
    @media (max-width: 768px) { body { padding-top: 70px; } }
    @media (max-width: 576px) { .cart-img { max-width: 100px; height: 100px; } }
  </style>
</head>
<body>

<?php include('includes/top-header.php'); ?>
<?php include('includes/main-header.php'); ?>
<?php include('includes/menu-bar.php'); ?>

<div class="container my-3">
  <h2 style="text-align:center; margin: 16px 0; font-weight:600; font-size:18px; color:#333;">
    ⚡ Express Checkout
  </h2>

  <!-- Single Product Display -->
  <div style="background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); padding:12px; margin-bottom:16px;">
    <div style="display:flex; gap:12px;">
      <!-- Product Image -->
      <div style="flex-shrink:0;">
        <img src="admin/productimages/<?php echo $pid; ?>/<?php echo $product['productImage1']; ?>" 
             alt="<?php echo htmlentities($product['productName']); ?>" class="cart-img">
      </div>

      <!-- Product Details -->
      <div style="flex-grow:1;">
        <h6 style="margin:0; font-size:16px;"><?php echo htmlentities($product['productName']); ?></h6>
        
        <?php if (isset($buynow_product['variant_label'])): ?>
          <div style="font-size:14px; color:#007bff;">Variant: <?php echo htmlspecialchars($buynow_product['variant_label']); ?></div>
        <?php endif; ?>
        
                 <div style="font-size:14px; color:#555;">
           Price: ₹<?php echo $item_price; ?>
           <?php if ($product['shippingCharge'] > 0): ?>
             + Shipping: ₹<?php echo $product['shippingCharge']; ?>
           <?php endif; ?>
         </div>
         
         <?php
         // Show stock warning if low stock
         if ($variant_id) {
             $stock_query = mysqli_query($con, "SELECT stock FROM product_variants WHERE id = $variant_id");
             $stock_result = mysqli_fetch_assoc($stock_query);
             $available_stock = intval($stock_result['stock']);
         } else {
             $stock_query = mysqli_query($con, "SELECT stock FROM products WHERE id = $pid");
             $stock_result = mysqli_fetch_assoc($stock_query);
             $available_stock = intval($stock_result['stock']);
         }
         
         if ($available_stock <= 5 || $available_stock > 0): ?>
           <div style="font-size:12px; color:#ff6b35; font-weight:bold;">⚠️ Only <?php echo $available_stock; ?> left in stock!</div>
         <?php elseif ($available_stock == 0): ?>
           <div style="font-size:12px; color:#dc3545; font-weight:bold;">❌ Out of stock!</div>
         <?php endif; ?>

        <!-- Quantity Controls -->
        <div style="margin:8px 0; display:flex; align-items:center;">
          <button type="button" class="minus-btn" 
                  style="padding:2px 8px; font-size:16px; border:1px solid #ccc; background:#f8f9fa;"
                  onclick="updateQty(<?php echo $pid; ?>, <?php echo $buynow_product['quantity']-1; ?>)">−</button>

          <input type="number" id="qty-input" 
                 value="<?php echo $buynow_product['quantity']; ?>"
                 style="width:50px; text-align:center; margin:0 5px; padding:4px; border:1px solid #ccc;"
                 min="1" max="10" onchange="updateQty(<?php echo $pid; ?>, this.value)">

          <button type="button" class="plus-btn" 
                  style="padding:2px 8px; font-size:16px; border:1px solid #ccc; background:#f8f9fa;"
                  onclick="updateQty(<?php echo $pid; ?>, <?php echo $buynow_product['quantity']+1; ?>)">+</button>
        </div>

        <div style="font-weight:bold; font-size:14px;">Total: ₹<?php echo $subtotal; ?></div>
      </div>
    </div>
  </div>

  <!-- Sticky Order Summary -->
  <form method="post" id="orderForm">
    <div class="sticky-bottom-bar d-flex justify-content-between align-items-center flex-wrap">
      <div>
        <strong>Grand Total: ₹<?php echo $grandTotal; ?></strong>
        <?php if ($deliveryCharge > 0): ?>
          <div style="color:#d9534f; font-size:13px;">₹10 delivery charge for orders below ₹300</div>
        <?php endif; ?>
      </div>
      
      <?php if ($grandTotal >= 50): ?>
        <button type="submit" name="ordersubmit" 
                style="background: linear-gradient(90deg, orange, green);
                       color: white; border: none; width: 240px; padding: 12px 0;
                       font-weight: 600; font-size: 15px; border-radius: 50px;
                       box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer;"
                onclick="return handlePlaceOrderClick();">
          🚀 Proceed To Payment
        </button>
      <?php else: ?>
        <div style="color:#d9534f; font-weight:500; font-size:14px;">
          ⚠️ Minimum order price is ₹50
        </div>
      <?php endif; ?>
    </div>
  </form>

  <!-- Option to go to full cart -->
  <div style="text-align:center; margin-top:16px;">
   <a href="index.php"
       style="display:inline-block; background:#fff; border:1px solid #ccc; color:#333; padding:6px 16px; border-radius:20px; font-size:14px; font-weight:500; text-decoration:none;">
       <i class="fa fa-arrow-left"></i> Continue Shopping
    </a>
  </div>


  <!-- Address Section (same as your existing code) -->
  <div class="container mb-4" style="max-width:600px; margin:auto;margin-top:20px">
    <?php
    $loggedIn = isset($_SESSION['login']) && strlen($_SESSION['login']) > 0;
    if ($loggedIn): ?>
      <!-- Your existing address form code here -->
      <?php if ($hasAddress && empty($_POST['editmode'])): ?>
        <!-- Show saved address -->
        <div style="background:#fff; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.08); padding:16px; position:relative;">
          <h6 style="font-weight:600; font-size:16px; margin-bottom:8px;">Shipping Address</h6>
          <p style="margin:0; font-size:14px; color:#333;">
            <?php echo htmlentities($shippingAddress); ?>,<br>
            <?php echo htmlentities($shippingCity); ?>, <?php echo htmlentities($shippingState); ?> - <?php echo htmlentities($shippingPincode); ?>
          </p>
          <form method="post" style="position:absolute; top:16px; right:16px;">
            <input type="hidden" name="editmode" value="1">
            <button type="submit" style="border:none; background:none; color:#007bff; font-size:13px;">Edit</button>
          </form>
        </div>
      <?php else: ?>
        <!-- Address form -->
        <div style="background:#fff; padding:16px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
          <h6 style="margin-bottom: 14px; font-weight:600; font-size:16px;">
            <?php echo $hasAddress ? 'Edit Shipping Address' : 'Add Shipping Address'; ?>
          </h6>
          <form method="post">
            <textarea name="shippingaddress" class="form-control" required placeholder="Complete Address including house number landmark"
              style="margin-bottom: 10px; border-radius: 6px;"><?php echo htmlentities($shippingAddress); ?></textarea>
            <select name="shippingstate" class="form-control" required style="margin-bottom: 10px; border-radius: 6px; padding: 8px; font-size: 14px;">
              <option value="Chhattisgarh" <?php if ($shippingState == 'Chhattisgarh') echo 'selected'; ?>>Chhattisgarh</option>
            </select>
            <select name="shippingcity" class="form-control" required style="margin-bottom: 10px; border-radius: 6px; padding: 8px; font-size: 14px;">
              <option value="Durg" <?php if ($shippingCity == 'Durg') echo 'selected'; ?>>Durg</option>
              <option value="Bhilai" <?php if ($shippingCity == 'Bhilai') echo 'selected'; ?>>Bhilai</option>
            </select>
            <input type="text" name="shippingpincode" class="form-control" required placeholder="Pincode"
              value="<?php echo htmlentities($shippingPincode); ?>" style="margin-bottom: 14px; border-radius: 6px;">
            <button type="submit" name="shipupdate"
              style="background:#2874f0; color:#fff; border:none; padding:8px 16px; border-radius:6px; width:100%; font-weight:600;">
              Save Address
            </button>
          </form>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
function updateQty(pid, qty) {
  if (qty > 10) qty = 10;
  if (qty < 1) qty = 1;
  
  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax_update=1&qty=' + qty
  }).then(() => location.reload());
}

function handlePlaceOrderClick() {
  const loggedIn = <?php echo isset($_SESSION['login']) && $_SESSION['login'] ? 'true' : 'false'; ?>;
  const hasAddress = <?php echo $hasAddress ? 'true' : 'false'; ?>;

  if (!loggedIn) {
    alert('Please login to place your order.');
    window.location.href = 'login.php';
    return false;
  }

  if (!hasAddress) {
    alert('Please fill your shipping address before placing the order.');
    return false;
  }

  return true;
}
</script>
</body>
</html>