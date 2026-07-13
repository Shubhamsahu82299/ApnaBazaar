<?php 
session_start();
error_reporting(0);
include('includes/config.php');
$con->query("SET time_zone = '+05:30'");

// Fetch shipping address
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

// AJAX Quantity Update
if (isset($_POST['ajax_update']) && isset($_POST['pid']) && isset($_POST['qty'])) {
    $pid = $_POST['pid'];
    $qty = $_POST['qty'];
    if ($qty == 0) unset($_SESSION['cart'][$pid]);
    else $_SESSION['cart'][$pid]['quantity'] = $qty;
    echo json_encode(['status' => 'success']);
    exit;
}

// Remove product
if (isset($_POST['remove_single'])) {
    $removeId = $_POST['remove_single'];
    unset($_SESSION['cart'][$removeId]);
    $msg = "Item removed from cart.";
}

// Include stock management functions
include('includes/stock-management.php');

// Place Order
if (isset($_POST['ordersubmit'])) {
    if (strlen($_SESSION['login']) == 0) {
        header('location:login.php');
        exit;
    } else {
        $pids = $_POST['pid'];
        $quantities = $_POST['quantity'];
        $value = array_combine($pids, $quantities);
        $deliveryCharge = $_SESSION['delivery_charge']; // use session

        // Check stock availability before placing order
        $stockErrors = [];
        foreach ($value as $pid => $qty) {
            // Get variant_id from session if exists
            $variant_id = isset($_SESSION['cart'][$pid]['variant_id']) ? $_SESSION['cart'][$pid]['variant_id'] : null;
            
            if ($variant_id) {
                // Check variant stock
                if (!checkVariantStock($con, $variant_id, $qty)) {
                    $stockErrors[] = "Product ID $pid (Variant): Insufficient stock for quantity $qty";
                }
            } else {
                // Check product stock
                if (!checkProductStock($con, $pid, $qty)) {
                    $stockErrors[] = "Product ID $pid: Insufficient stock for quantity $qty";
                }
            }
        }

        if (!empty($stockErrors)) {
            $msg = "Order cannot be placed: " . implode(", ", $stockErrors);
        } else {
            // Place orders and update stock
            foreach ($value as $pid => $qty) {
                // Get variant_id from session if exists
                $variant_id = isset($_SESSION['cart'][$pid]['variant_id']) ? $_SESSION['cart'][$pid]['variant_id'] : null;
                
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
            }

            echo "<script>window.location.href='payment-method.php';</script>";
            exit;
        }
    }
}

// Update Shipping Address
if (isset($_POST['shipupdate'])) {
    $saddress = $_POST['shippingaddress'];
    $sstate = $_POST['shippingstate'];
    $scity = $_POST['shippingcity'];
    $spincode = $_POST['shippingpincode'];
    $query = mysqli_query($con, "UPDATE users SET shippingAddress='$saddress', shippingState='$sstate', shippingCity='$scity', shippingPincode='$spincode' WHERE id='" . $_SESSION['id'] . "'");
    if ($query) {
        $msg = "Address updated successfully";
        $shippingAddress = $saddress;
        $shippingState = $sstate;
        $shippingCity = $scity;
        $shippingPincode = $spincode;
        $hasAddress = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> 
  <title>My Cart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/font-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
        margin-top:50px;
        background-color: #f1f3f6; font-family: 'Segoe UI', sans-serif; padding-top:60px}
    .cart-img { width: 80px; height: 80px; object-fit: contain; border: 1px solid #eee; background: #f9f9f9; border-radius: 6px; }
    .cart-item { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 16px; }
    .qty-box { width: 50px; text-align: center; }
    .sticky-bottom-bar { position: sticky; bottom: 0; z-index: 1000; background: #fff; border-top: 1px solid #ddd; padding: 10px 16px; }
    .empty-cart { text-align: center; padding: 60px 20px; }
    .empty-cart img { max-width: 150px; }
    textarea.form-control { resize: none; }
    @media (max-width: 768px) {
  body {
    padding-top: 70px; /* mobile header height */
  }
}
    @media (max-width: 576px) {
        .cart-img { width: 60px; height: 60px; }
        .sticky-bottom-bar { flex-direction: column; gap: 10px; }
    }
   .cart-img {
  width: 100%;
  max-width: 120px; /* Increased from 80 */
  height: 120px;     /* Increased height */
  object-fit: contain;
  border: 1px solid #ddd;
  padding: 8px;
  background: #f9f9f9;
  border-radius: 8px;
}
@media (max-width: 576px) {
  .cart-img {
    max-width: 100px;
    height: 100px;
  }
}

  </style>
</head>
<body>

<?php include('includes/top-header.php'); ?>
<?php include('includes/main-header.php'); ?>
<?php include('includes/menu-bar.php'); ?>
<h2 style="text-align:center; margin: 16px 0; font-weight:600; font-size:18px; color:#333;">
  🛒 My Cart
</h2>
<div class="container my-3">
<?php if (!empty($msg)): ?>
    <div class="alert alert-success" id="msgBox"><?php echo $msg; ?></div>
    <script>setTimeout(() => document.getElementById('msgBox')?.remove(), 2000);</script>
<?php endif; ?>

<?php if (!empty($_SESSION['cart'])): ?>
<form method="post" id="cartForm">
<?php
$totalprice = 0;
$pdtid = [];
$sql = "SELECT * FROM products WHERE id IN(" . implode(',', array_keys($_SESSION['cart'])) . ")";
$query = mysqli_query($con, $sql);
while ($row = mysqli_fetch_array($query)):
    $pid = $row['id'];
    $qty = $_SESSION['cart'][$pid]['quantity'];
    // Use variant price if present
    $item_price = isset($_SESSION['cart'][$pid]['price']) ? $_SESSION['cart'][$pid]['price'] : $row['productPrice'];
    $subtotal = ($qty * $item_price) + $row['shippingCharge'];
    $totalprice += $subtotal;
    $pdtid[] = $pid;
?>
<div style="background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); padding:12px; margin-bottom:16px;">
  <div style="display:flex; gap:12px;">
    
    <!-- Product Image Left -->
    <div style="flex-shrink:0;">
      <img src="<?php echo getProductImage($row['id'], $row['productImage1']); ?>" alt=""
           style="width:120px; height:120px; object-fit:contain; border:1px solid #eee; border-radius:8px; background:#f9f9f9;">
    </div>

    <!-- Product Details Right -->
    <div style="flex-grow:1;">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <h6 style="margin:0; font-size:16px;"><?php echo htmlentities($row['productName']); ?></h6>
        <form method="post">
          <input type="hidden" name="remove_single" value="<?php echo $pid; ?>">
          <button type="submit" style="border:none; background:none; color:#dc3545; font-size:18px;"><i class="fa fa-trash"></i></button>
        </form>
      </div>
             <?php if (isset($_SESSION['cart'][$pid]['variant_label'])): ?>
         <div style="font-size:14px; color:#007bff;">Variant: <?php echo htmlspecialchars($_SESSION['cart'][$pid]['variant_label']); ?></div>
       <?php endif; ?>
       <div style="font-size:14px; color:#555;">Price: ₹<?php echo $item_price; ?> </div>
       
       <?php
       // Show stock warning if low stock
       $variant_id = isset($_SESSION['cart'][$pid]['variant_id']) ? $_SESSION['cart'][$pid]['variant_id'] : null;
       if ($variant_id) {
           $stock_query = mysqli_query($con, "SELECT stock FROM product_variants WHERE id = $variant_id");
           $stock_result = mysqli_fetch_assoc($stock_query);
           $available_stock = intval($stock_result['stock']);
       } else {
           $stock_query = mysqli_query($con, "SELECT stock FROM products WHERE id = $pid");
           $stock_result = mysqli_fetch_assoc($stock_query);
           $available_stock = intval($stock_result['stock']);
       }
       
       if ($available_stock <= 5 && $available_stock > 0): ?>
         <div style="font-size:12px; color:#ff6b35; font-weight:bold;">⚠️ Only <?php echo $available_stock; ?> left in stock!</div>
       <?php elseif ($available_stock == 0): ?>
         <div style="font-size:12px; color:#dc3545; font-weight:bold;">❌ Out of stock!</div>
       <?php endif; ?>

      <div style="margin:8px 0; display:flex; align-items:center;">
        <button type="button"
                style="padding:2px 8px; font-size:16px; border:1px solid #ccc; background:#f8f9fa;"
                class="minus-btn" data-pid="<?php echo $pid; ?>">−</button>

        <input type="number" name="quantity[]" data-pid="<?php echo $pid; ?>"
               value="<?php echo $qty; ?>"
               style="width:50px; text-align:center; margin:0 5px; padding:4px; border:1px solid #ccc;"
               class="qty-input" min="1" max="10">

        <button type="button"
                style="padding:2px 8px; font-size:16px; border:1px solid #ccc; background:#f8f9fa;"
                class="plus-btn" data-pid="<?php echo $pid; ?>">+</button>
      </div>

      <div style="font-weight:bold; font-size:14px;">Total: ₹<?php echo $subtotal; ?></div>
    </div>

  </div>
</div>

<script>function sendQtyUpdate(pid, qty) {
  if (qty > 10) {
    qty = 10;
    showQtyWarning(); // Show warning when above 10
  }
  if (qty < 1) qty = 1;

  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax_update=1&pid=' + pid + '&qty=' + qty
  }).then(() => location.reload());
}

function showQtyWarning() {
  const box = document.getElementById('qtyWarning');
  box.style.display = 'block';
  setTimeout(() => {
    box.style.display = 'none';
  }, 2000);
}
</script>

 



<?php endwhile; $_SESSION['pid'] = $pdtid; 
$deliveryCharge = ($totalprice < 300) ? 10 : 0;
$grandTotal = $totalprice + $deliveryCharge;

$_SESSION['delivery_charge'] = $deliveryCharge;
$_SESSION['tp'] = $grandTotal . ".00";
?>
</form>

<!-- Sticky total bar -->
<form method="post">
  <?php foreach ($_SESSION['cart'] as $pid => $val): ?>
    <input type="hidden" name="pid[]" value="<?php echo $pid; ?>">
    <input type="hidden" name="quantity[]" value="<?php echo $val['quantity']; ?>">
  <?php endforeach; ?>
  <div class="sticky-bottom-bar d-flex justify-content-between align-items-center flex-wrap">
  <strong>Grand Total: ₹<?php echo $_SESSION['tp']; ?></strong>
  <?php if ($_SESSION['delivery_charge'] == 10): ?>
  <div style="color:#d9534f; font-size:13px;">₹10 delivery charge for orders below ₹300</div>
<?php endif; ?>
  <?php if ($totalprice >= 50): ?>
 <button type="submit" name="ordersubmit" style="background: linear-gradient(90deg, orange, green);
           color: white;
           border: none;
           width: 240px;
           padding: 12px 0;
           font-weight: 600;
           font-size: 15px;
           border-radius: 50px;
           box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
           cursor: pointer;
           position: relative;
           overflow: hidden;
           transition: all 0.3s ease;" class="btn btn-success btn-sm p-4 " onclick="return handlePlaceOrderClick();">
       <span style="position: relative; z-index: 2;">🚀 Proceed To Buy</span>
    </button> 
    <div style="text-align:center; margin-top: 20px;">
 
</div>

<style>
  .animated-slide-bg {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
                rgba(255, 165, 0, 0) 0%,    /* transparent orange */
                rgba(255, 165, 0, 0.3) 20%, /* light orange */
                rgba(255, 255, 255, 0.4) 50%, /* white highlight */
                rgba(0, 128, 0, 0.3) 80%,   /* light green */
                rgba(0, 128, 0, 0) 100%);   /* transparent green */
    z-index: 1;
    animation: indianGlow 2.2s linear infinite;
    border-radius: 50px;
  }

  @keyframes indianGlow {
    from {
      left: -100%;
    }
    to {
      left: 100%;
    }
  }
</style>

      <!-- 🛒 Continue Shopping Button -->
  <div style="text-align:center; margin-top:16px;">
   <a href="index.php"
       style="display:inline-block; background:#fff; border:1px solid #ccc; color:#333; padding:6px 16px; border-radius:20px; font-size:14px; font-weight:500; text-decoration:none;">
       <i class="fa fa-arrow-left"></i> Continue Shopping
    </a>
  </div>

  <?php else: ?>
  <div style="text-align:center;display:inline-block; background:#fff;  color:#333; padding:6px 16px; border-radius:20px; font-size:14px; font-weight:500; text-decoration:none;">
    <div style="color:#d9534f; font-weight:500; font-size:14px; margin-bottom:8px;">
      ⚠️ Minimum order price is ₹50
    </div>
    <div style="margin-left:50px;text-align:center; margin-top:16px;">
   <a href="index.php"
       style="display:inline-block; background:#fff; border:1px solid #ccc; color:#333; padding:6px 16px; border-radius:20px; font-size:14px; font-weight:500; text-decoration:none;">
       <i class="fa fa-arrow-left"></i> Continue Shopping
    </a>
  </div>
  </div>
<?php endif; ?>

</div>
</form>

<?php else: ?>

<!-- Empty Cart UI -->
<div class="empty-cart" style="text-align: center; padding: 50px 20px;">
  <i class="fas fa-cart-arrow-down" style="font-size: 60px; color: #999; margin-bottom: 16px;"></i>
  <h5 style="font-size: 18px; font-weight: 500; color: #555;">Your cart is empty</h5>
  <a href="index.php"
     style="background: #2874f0; color: white; padding: 8px 18px; margin-top: 12px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block;">
     Continue Shopping
  </a>
</div>
<?php endif; ?>
</div>

<!-- Address Section -->

<div class="container mb-4" style="max-width:600px; margin:auto;margin-top:20px">
<?php
$loggedIn = isset($_SESSION['login']) && strlen($_SESSION['login']) > 0;

if (!empty($_SESSION['cart']) && $loggedIn): ?>

  <?php if ($hasAddress && empty($_POST['editmode'])): ?>
    <!-- ✅ Show saved address -->
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
    <!-- ✍️ Show form if no address or edit clicked -->
    <div style="background:#fff; padding:16px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
      <h6 style="margin-bottom: 14px; font-weight:600; font-size:16px;">
        <?php echo $hasAddress ? 'Edit Shipping Address' : 'Add Shipping Address'; ?>
      </h6>
      <form method="post">
        <textarea name="shippingaddress" class="form-control" required placeholder="Complete Address including house number landmark"
          style="margin-bottom: 10px; border-radius: 6px;"><?php echo htmlentities($shippingAddress); ?></textarea>

       <!-- STATE SELECT BOX -->
<select name="shippingstate" class="form-control" required
  style="margin-bottom: 10px; border-radius: 6px; padding: 8px; font-size: 14px;">
  <option value="Chhattisgarh" <?php if ($shippingState == 'Chhattisgarh') echo 'selected'; ?>>
    Chhattisgarh
  </option>
</select>

<!-- CITY SELECT BOX -->
<select name="shippingcity" class="form-control" required
  style="margin-bottom: 10px; border-radius: 6px; padding: 8px; font-size: 14px;">
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

  <!-- 🛒 Continue Shopping Button -->
  

<?php endif; ?>
</div>
<!-- address section ends -->
<!-- placeorder button -->
<?php
$loggedIn = isset($_SESSION['login']) && strlen($_SESSION['login']) > 0;
$hasAddressData = $loggedIn && $hasAddress;
?>

<?php if (!empty($_SESSION['cart'])): ?>
  <form id="placeOrderForm" method="post" action="<?php echo $loggedIn && $hasAddress ? 'payment-method.php' : 'javascript:void(0);'; ?>">
    <?php if ($loggedIn && $hasAddress): ?>
      <input type="hidden" name="ordersubmit" value="1">
      <?php 
        foreach ($_SESSION['cart'] as $pid => $details) {
          echo "<input type='hidden' name='quantity[]' value='" . $details['quantity'] . "'>";
        }
      ?>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
<!DOCTYPE html>
<html>
<head>
  
</head>
<body>

<!-- Online Notification Sound -->
<audio id="orderSound" preload="auto">
  <source src="orderplaced.mp3" type="audio/mpeg">
  Your browser does not support the audio element.
</audio>

<!-- Place Order Button -->

<!-- JavaScript -->
<script>
  function handlePlaceOrderClick() {
    const sound = document.getElementById("orderSound");

    // Reset the sound and play it
    sound.currentTime = 0;
    sound.play()
      .then(() => {
        console.log("Sound played!");
        alert("✅ Order placed successfully!");
      })
      .catch(err => {
        console.error("Sound play failed:", err);
        alert("Order placed, but sound failed to play.");
      });

    return true;
  }
</script>

</body>
</html>

</div>
  </form>

  <script>
   function handlePlaceOrderClick() {
    const loggedIn = <?php echo isset($_SESSION['login']) && $_SESSION['login'] ? 'true' : 'false'; ?>;
    const hasAddress = <?php echo $hasAddress ? 'true' : 'false'; ?>;

    if (!loggedIn) {
      alert('Please login to place your order.');
      window.location.href = 'login.php'; // Redirect only if not logged in
      return false;
    }

    if (!hasAddress) {
      alert('Please fill your shipping address before placing the order.');
      // No redirect here, just stop form submission
      return false;
    }

    return true; // allow form submission
  }
  </script>
<?php endif; ?>


<?php include('includes/footer.php'); ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
function updateQty(pid, qty) {
  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax_update=1&pid=' + pid + '&qty=' + qty
  }).then(() => location.reload());
}

document.querySelectorAll('.plus-btn').forEach(btn => {
  btn.onclick = () => {
    let input = document.querySelector(`.qty-input[data-pid="${btn.dataset.pid}"]`);
    let qty = parseInt(input.value);
    if (qty < 10) updateQty(btn.dataset.pid, qty + 1);
  }
});
document.querySelectorAll('.minus-btn').forEach(btn => {
  btn.onclick = () => {
    let input = document.querySelector(`.qty-input[data-pid="${btn.dataset.pid}"]`);
    let qty = parseInt(input.value);
    if (qty > 1) updateQty(btn.dataset.pid, qty - 1);
  }
});
document.querySelectorAll('.qty-input').forEach(input => {
  input.addEventListener('change', () => updateQty(input.dataset.pid, input.value));
});
</script>
</body>
</html>
