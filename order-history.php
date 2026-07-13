<?php 
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0) {
    header('location:login.php');
    exit();
}

// ✅ Step 1: Check if latest order was placed within 5 seconds
$orderPlacedRecently = false;
$checkQuery = mysqli_query($con, "SELECT orderDate FROM orders WHERE userId='".$_SESSION['id']."' AND paymentMethod IS NOT NULL ORDER BY id DESC LIMIT 1");
if ($row = mysqli_fetch_assoc($checkQuery)) {
    $orderTime = strtotime($row['orderDate']);
    if (time() - $orderTime <= 5) {
        $orderPlacedRecently = true;
    }
}

// Handle Cancel
if(isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $cancelId = intval($_GET['cancel']);
    mysqli_query($con, "UPDATE orders SET orderStatus='Cancelled' WHERE id='$cancelId' AND userId='".$_SESSION['id']."'");
}

// Handle Return
if(isset($_POST['return_order_id']) && isset($_POST['return_reason'])) {
    $returnId = intval($_POST['return_order_id']);
    $reason = mysqli_real_escape_string($con, $_POST['return_reason']);
    mysqli_query($con, "UPDATE orders SET orderStatus='Return/Exchange Requested - $reason' WHERE id='$returnId' AND userId='".$_SESSION['id']."'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <style>
      
        body { 
            background-color: #f1f3f6; font-family: 'Roboto', sans-serif; padding-top:60px}
        .order-card { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .order-header { font-weight: 500; font-size: 16px; color: #212121; }
        .order-meta { font-size: 13px; color: #757575; }
        .product-info { display: flex; align-items: center; margin-top: 10px; }
        .product-info img { height: 80px; width: 80px; object-fit: contain; margin-right: 15px; }
        .product-name { font-weight: 500; font-size: 15px; color: #212121; }
        .order-actions { margin-top: 10px; }
        .btn-invoice, .btn-cancel, .btn-return {
            font-size: 13px; padding: 5px 10px; border: 1px solid #2874f0;
            color: #2874f0; background-color: #fff; border-radius: 4px; text-decoration: none; margin-right: 8px;
        }
        .btn-invoice:hover, .btn-cancel:hover, .btn-return:hover {
            background-color: #2874f0; color: #fff;
        }
        .badge-status {
            font-size: 12px; padding: 3px 7px; border-radius: 4px; font-weight: 500;
        }
       
        @media (max-width: 768px) {
  body {
    padding-top: 110px; /* mobile header height */
  }
}
        @media  (min-width: 992px) {
  body {
    padding-top: 110px; /* mobile header height */
  }
}
        .delivered { background-color: #c8e6c9; color: #2e7d32; }
        .pending { background-color: #ffe0b2; color: #ef6c00; }
        @media (max-width: 576px) {
            .product-info { flex-direction: column; align-items: flex-start; }
            .product-info img { margin-bottom: 10px; }
        }
        .return-form { margin-top: 10px; }
        /* Default style */
.order-card {
    background: #fff;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

/* Hover zoom effect */
.order-card:hover {
    transform: translateY(-3px) scale(1.01);
}

/* Status based colored glow + icon badge */
.order-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 5px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.status-accepted {
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3); /* Blue */
}
.status-accepted::before {
    background: #2196f3;
}

.status-processing {
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3); /* Amber */
}
.status-processing::before {
    background: #ffc107;
}

.status-shipped-from-ApnaBazaar {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}
.status-shipped-from-ApnaBazaar::before {
    background: #007bff;
}

.status-payment-done {
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}
.status-payment-done::before {
    background: #28a745;
}

.status-delivered {
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4);
}
.status-delivered::before {
    background: #198754;
}

.status-cancelled {
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}
.status-cancelled::before {
    background: #dc3545;
}

.status-return-exchange-requested---wrong-item,
.status-return-exchange-requested---damaged-product,
.status-return-exchange-requested---not-needed {
    box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
}
.status-return-exchange-requested---wrong-item::before,
.status-return-exchange-requested---damaged-product::before,
.status-return-exchange-requested---not-needed::before {
    background: #ff5722;
}
.badge-status {
    display: inline-block;
    background-color: #f2f2f2;
    color: #333;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    margin-top: 6px;
    border: 1px solid #ddd;
}
.order-history ul li {
    margin-bottom: 6px;
    border-left: 3px solid #28a745; /* green line */
    padding-left: 10px;
}

    </style>
</head>
<body>

<?php include('includes/top-header.php'); ?>
<?php include('includes/main-header.php'); ?>
<?php include('includes/menu-bar.php'); ?>

<div class="container ">
    <h3 style="text-align:center;" class="mb-3">My Orders Summary</h3>
    <div style="text-align: center; margin-top: 10px;">
  <a href="index.php"
     class="btn btn-outline-secondary "
     style="
        display: inline-block;
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 30px;
        border: 1px solid #6c757d;
        color: #333;
        transition: all 0.3s ease;
        text-decoration: none;
        margin-bottom:10px;
     "
     onmouseover="this.style.backgroundColor='#f0f0f0'; this.style.color='#000';"
     onmouseout="this.style.backgroundColor='transparent'; this.style.color='#333';"
  >
    <i class="fa fa-arrow-left"></i> Continue Shopping
  </a>
</div>

    <div id="orderList">
        <?php
        $query = mysqli_query($con, "SELECT 
            products.productImage1 as pimg1, 
            products.productName as pname, 
            products.id as proid, 
            orders.productId as opid, 
            orders.quantity as qty, 
            orders.variant_id as variant_id,
            COALESCE(orders.sell_price_at_order_time, 
                     CASE 
                         WHEN orders.variant_id IS NOT NULL THEN pv.price 
                         ELSE products.productPrice 
                     END) as pprice,
            products.shippingCharge as shippingcharge, 
            orders.paymentMethod as paym, 
            orders.orderDate as odate, 
            orders.id as orderid, 
            orders.orderStatus as ostatus, 
            orders.deliveryCharge as dcharge,
            pv.variant_label as variant_label,
            orders.buy_price_at_order_time,
            orders.sell_price_at_order_time
            FROM orders 
            JOIN products ON orders.productId=products.id 
            LEFT JOIN product_variants pv ON orders.variant_id = pv.id
            WHERE orders.userId='".$_SESSION['id']."' AND orders.paymentMethod IS NOT NULL 
            ORDER BY orders.id DESC");
        if (!$query) {
            die("Order history query error: " . mysqli_error($con));
        }
        $orderCount = 0;
        while($row = mysqli_fetch_array($query)) {
           $orderCount++;
           $total = ($row['qty'] * $row['pprice']) + $row['shippingcharge'] + $row['dcharge'];

            $orderDate = strtotime($row['odate']);
            $currentTime = time();
            $hoursPassed = ($currentTime - $orderDate) / 3600;
            $isDelivered = strtolower($row['ostatus']) == 'delivered';
            $orderId = intval($row['orderid']);
            // Step 1: Fetch status time mapping
$trackTimes = [];
$trackQuery = mysqli_query($con, "SELECT status, postingDate FROM ordertrackhistory WHERE orderId = $orderId");
while($trackRow = mysqli_fetch_assoc($trackQuery)) {
    $statusKey = strtolower(trim(str_replace([' ', '/', '_'], '-', $trackRow['status'])));
    $trackTimes[$statusKey] = date("d M, H:i", strtotime($trackRow['postingDate']));
}

   

   

        ?>
       <div class="order-card  <?php echo $statusClass; ?> <?php echo $orderCardClass; $status = strtolower(str_replace(['/', ' ', '_'], '-', trim($row['ostatus'])));
$statusClass = 'status-' . $status; ?>">


            <?php
$orderStatus = strtolower(trim($row['ostatus']));
$isCancelled = ($orderStatus == 'cancelled');
$isReturned = str_contains($orderStatus, 'return');

$orderCardClass = '';
if ($isCancelled) {
    $orderCardClass = 'order-cancelled';
} elseif ($isReturned) {
    $orderCardClass = 'order-returned';
}
?>
            <div class="order-header">
                Order ID: <?php echo $row['orderid']; ?> 
                <span class="order-meta float-end">Date: <?php echo $row['odate']; ?></span>
                
            </div>
            <div class="product-info">
             <img src="<?php echo getProductImage($row['proid'], $row['pimg1']); ?>" alt="">
                    <div class="product-name"><?php echo $row['pname']; ?></div>
                    <?php if (!empty($row['variant_label'])): ?>
                        <div class="order-meta" style="color:#007bff;">Variant: <?php echo htmlspecialchars($row['variant_label']); ?></div>
                    <?php endif; ?>
                    <div class="order-meta">Qty: <?php echo $row['qty']; ?> | Price: ₹<?php echo $row['pprice']; ?> 
                        <?php if (!empty($row['sell_price_at_order_time']) && $row['sell_price_at_order_time'] != $row['pprice']): ?>
                            <span style="color:#28a745; font-size:12px;">(Order Price)</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($row['dcharge'] > 0): ?>
  <div class="order-meta" style="color:#d9534f;">
    Delivery Charge: ₹<?php echo $row['dcharge']; ?> (Applied for orders below ₹300)
  </div>
<?php endif; ?>
                    <div class="order-meta">Total: ₹<?php echo number_format($total, 2); ?></div>
                    <?php 
                    // Show price change information if available
                    if (!empty($row['sell_price_at_order_time']) && !empty($row['pprice'])) {
                        $current_price = $row['pprice'];
                        $order_price = $row['sell_price_at_order_time'];
                        
                        if ($current_price != $order_price) {
                            $price_diff = $current_price - $order_price;
                            $change_text = $price_diff > 0 ? "increased" : "decreased";
                            $change_color = $price_diff > 0 ? "#d9534f" : "#28a745";
                            echo "<div class='order-meta' style='color:{$change_color}; font-size:12px;'>";
                            echo "Price has {$change_text} by ₹" . abs($price_diff) . " since order";
                            echo "</div>";
                        }
                    }
                    ?>
                    <div class="order-meta">Payment: <?php echo $row['paym']; ?></div>
                    <!-- order progress track -->
                    <div class="order-meta">
                        Status: 
                        <?php
// Clean status to map
$rawStatus = trim(strtolower(str_replace(['/', '_'], '-', $row['ostatus'])));

// Icon mapping
$statusIcons = [
    'accepted' => '✅ Accepted',
    'processing' => '🔄 Processing',
    'shipped-from-ApnaBazaar' => '📦 Shipped from ApnaBazaar',
    'payment-done' => '💰 Payment Done',
    'delivered' => '📬 Delivered',
    'cancelled' => '❌ Cancelled',
    'return-exchange-requested---wrong-item' => '↩️ Return Requested - Wrong Item',
    'return-exchange-requested---damaged-product' => '🛠️ Return - Damaged Product',
    'return-exchange-requested---not-needed' => '↪️ Return - Not Needed'
];

// Use mapped label or fallback
$displayStatus = isset($statusIcons[$rawStatus]) ? $statusIcons[$rawStatus] : "". htmlentities($row['ostatus']);
?>
<span class="badge-status <?php echo $statusClass; ?>">
    <?php echo $displayStatus; ?>
             <div style="margin-left:-13px;">    <?php
if (empty(trim($row['ostatus']))) {
    echo '<div style="padding: 2px; color: #555; font-style: italic; text-align:center;">';
    echo 'Please wait a while for order progress.';
    echo '</div>';
} else {
    // Show your progress tracker or status badges here
}
?> </div>
</span>

           

                    </div>
                    <?php 
// Define all steps in order
$stages = ['Accepted', 'Processing', 'Shipped from ApnaBazaar', 'Payment_Done', 'Delivered'];
$currentStatus = $row['ostatus'];

// Find current step index (case insensitive match)
$currentIndex = -1;
foreach ($stages as $index => $stage) {
    if (strcasecmp(trim($currentStatus), $stage) == 0) {
        $currentIndex = $index;
        break;
    }
}
?>

<style>
    .progress-tracker {
        display: flex;
        justify-content: space-between;
        margin-top: 12px;
        font-size: 13px;
        gap:5px;
        font-family: Arial, sans-serif;
    }
    .progress-step {
        flex: 1;
        text-align: center;
        position: relative;
        color: #ede8e8;
        font-weight: 500;
    }
    .progress-step.completed {
        color: #28a745; /* green */
        font-weight: 700;
    }
    .progress-step.active {
        color: #007bff; /* blue */
        font-weight: 700;
    }
    .progress-step::before {
        content: "";
        display: block;
        margin: 0 auto 6px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ede8e8;
        line-height: 24px;
        color: white;
        font-weight: bold;
        position: relative;
        z-index: 1;
    }
    .progress-step.completed::before {
        background: #28a745;
        content: "✓";
    }
    .progress-step.active::before {
        background: #007bff;
        content: attr(data-step);
        color: white;
    }
    .progress-step:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 12px;
        left: 50%;
        width: 100%;
        height: 4px;
        background: #ede8e8;
        z-index: 0;
    }
    .progress-step.completed:not(:last-child)::after {
        background: #28a745;
    }
    
</style>

<div class="progress-tracker" style="margin-left:-15px">
    <?php foreach ($stages as $index => $stage): 
        $class = '';
        if ($index < $currentIndex) {
            $class = 'completed';
        } elseif ($index == $currentIndex) {
            $class = 'active';
        }
    ?>
   <?php
$stageKey = strtolower(trim(str_replace([' ', '/', '_'], '-', $stage)));
$timeText = isset($trackTimes[$stageKey]) ? $trackTimes[$stageKey] : '';
?>
<div class="progress-step <?php echo $class; ?>" data-step="<?php echo $index + 1; ?>">
    <?php echo htmlspecialchars($stage); ?>
    <?php if ($timeText): ?>
        <div style="font-size:11px; margin-top:3px; color:#555;"><?php echo $timeText; ?></div>
    <?php endif; ?>
</div>

    <?php endforeach; ?>
</div>

                </div>
            </div>
            <div class="order-actions">
                <a href="invoice.php?oid=<?php echo htmlentities($row['orderid']); ?>" class="btn-invoice" target="_blank">Download Invoice</a>
                <?php if($hoursPassed < 24 && !$isDelivered) { ?>
                    <a href="?cancel=<?php echo $row['orderid']; ?>" class="btn-cancel">Cancel Order</a>
                <?php } elseif($isDelivered && $hoursPassed < 24) { ?>
                    <form method="post" class="return-form">
                        <input type="hidden" name="return_order_id" value="<?php echo $row['orderid']; ?>">
                        <select name="return_reason" required class="form-select form-select-sm d-inline w-auto">
                            <option value="">Select Reason</option>
                            <option value="Wrong Item">Wrong Item</option>
                            <option value="Damaged Product">Damaged Product</option>
                            <option value="Not Needed">Not Needed</option>
                        </select>
                        <button type="submit" class="btn-return">Submit Product Return </button>
                    </form>
                <?php } ?>
            </div>
        </div>
        <?php }
        if ($orderCount == 0) {
            echo "<div style='text-align:center; color:#d9534f; margin:40px 0;'>No orders found.</div>";
        }
    ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<!-- ✅ Step 2: Order animation overlay if placed recently -->
<?php if ($orderPlacedRecently): ?>
<div id="orderSuccessOverlay" style="
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #28a745;
    font-size: 2rem;
    font-weight: bold;
">
    ✓ Order Placed Successfully!
</div>
<audio autoplay>
    <source src="orderplaced.mp3" type="audio/ogg">
</audio>
<script>
    setTimeout(() => {
        document.getElementById("orderSuccessOverlay").style.display = "none";
    }, 2000);
</script>
<?php endif; ?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
    // Optional: Refresh orders every 5 seconds
    setInterval(function() {
        $('#orderList').load(location.href + " #orderList>*", "");
    }, 5000);
</script>

</body>
</html>
