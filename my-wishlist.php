<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0){   
    header('location:login.php');
    exit;
}

// Remove from wishlist
if(isset($_GET['del'])){
    $wid = intval($_GET['del']);
    mysqli_query($con,"DELETE FROM wishlist WHERE id='$wid'");
    header("Location: my-wishlist.php?removed=1");
    exit;
}

// Add to cart
if(isset($_GET['action']) && $_GET['action'] == "add"){
    $id = intval($_GET['id']);
    mysqli_query($con,"DELETE FROM wishlist WHERE productId='$id'");
    if(isset($_SESSION['cart'][$id])){
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        $sql_p = "SELECT * FROM products WHERE id={$id}";
        $query_p = mysqli_query($con, $sql_p);
        if(mysqli_num_rows($query_p)){
            $row_p = mysqli_fetch_array($query_p);
            $_SESSION['cart'][$row_p['id']] = array("quantity" => 1, "price" => $row_p['productPrice']);
        }
    }
    header("Location: my-cart.php?added=1");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'wishlist') {
    if (strlen($_SESSION['login']) == 0) {
        header('location:login.php');
    } else {
        $productId = intval($_GET['pid']);
        $userId = $_SESSION['id'];

        // Check if product already in wishlist
        $check = mysqli_query($con, "SELECT * FROM wishlist WHERE userId='$userId' AND productId='$productId'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($con, "INSERT INTO wishlist(userId, productId) VALUES('$userId', '$productId')");
        }

        header('location:my-wishlist.php');
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Wishlist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
            padding-top: 50px;
        }
        
        @media (max-width: 768px) {
  body {
    padding-top: 90px; /* mobile header height */
  }
}
        @media  (min-width: 992px) {
  body {
    padding-top: 90px; /* mobile header height */
  }
}
        .wishlist-container {
            max-width: 960px;
            margin: 30px auto;
        }
        .wishlist-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .wishlist-card img {
            max-width: 100px;
            margin-right: 20px;
        }
        .wishlist-details {
            flex: 1;
        }
        .wishlist-details h5 {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 500;
        }
        .wishlist-details .price {
            color: #388e3c;
            font-size: 16px;
            font-weight: bold;
        }
        .wishlist-actions {
            text-align: right;
        }
        .wishlist-actions button {
            margin: 5px;
        }
        .message-box {
            max-width: 960px;
            margin: 20px auto;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            transition: all 0.4s ease;
        }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
        @media(max-width: 576px){
            .wishlist-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .wishlist-actions {
                width: 100%;
                margin-top: 10px;
                text-align: left;
            }
        }
        .continue-shopping {
            text-align: center;
            margin: 30px 0;
        }
        .continue-shopping a {
            background: #2874f0;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .continue-shopping a:hover {
            background: #0b58cf;
        }
    </style>
</head>
<body>

<?php include('includes/top-header.php');?>
<?php include('includes/main-header.php');?>
<?php include('includes/menu-bar.php');?>

<!-- Notification Sound -->
<audio id="addToCartSound" preload="auto">
    <source src="https://notificationsounds.com/storage/sounds/file-sounds-1153-pristine.mp3" type="audio/mpeg">
</audio>

<!-- Message Banner -->
<?php if(isset($_GET['removed']) && $_GET['removed'] == 1): ?>
    <div class="message-box error" id="messageBox">🗑️ Product removed from wishlist.</div>
<?php endif; ?>

<div class="wishlist-container">
    <h3 style="text-align:center;padding-top:10px;padding-buttom:10px">My Wishlist</h3>

    <?php
    $ret = mysqli_query($con, "SELECT products.productName as pname, products.id as pid, products.productImage1 as pimage, products.productPrice as pprice, wishlist.id as wid FROM wishlist JOIN products ON products.id=wishlist.productId WHERE wishlist.userId='".$_SESSION['id']."'");
    $num = mysqli_num_rows($ret);
    if($num > 0){
        while($row = mysqli_fetch_array($ret)) {
            $productId = $row['pid'];
    ?>
    <div class="wishlist-card">
        <img src="admin/productimages/<?php echo $productId; ?>/<?php echo $row['pimage']; ?>" alt="<?php echo $row['pname']; ?>">
        <div class="wishlist-details">
            <h5><?php echo $row['pname']; ?></h5>
            <div class="price">₹<?php echo $row['pprice']; ?></div>
            <div class="rating">★★★★☆ 
                <span style="font-size: 13px;">
                (<?php 
                    $rt = mysqli_query($con,"SELECT * FROM productreviews WHERE productId='$productId'");
                    echo mysqli_num_rows($rt); 
                ?> reviews)
                </span>
            </div>
        </div>
        <div class="wishlist-actions">
          <a href="my-wishlist.php?page=product&action=add&id=<?php echo $row['pid']; ?>" class="btn-upper btn btn-primary">Add to cart</a>
            <a href="my-wishlist.php?del=<?php echo $row['wid']; ?>&removed=1" onclick="return confirm('Are you sure you want to remove this item?');" class="btn btn-sm btn-danger">Remove</a>
        </div>
    </div>
    <?php } } else { ?>
        <div class="message-box" style="background:#fff3cd; color:#856404;">😕 Your Wishlist is Empty.</div>
    <?php } ?>

    <!-- Continue Shopping Button -->
    <div class="continue-shopping">
        <a href="index.php">← Continue Shopping</a>
    </div>
</div>

<?php include('includes/brands-slider.php');?>
<?php include('includes/footer.php');?>

<script>
// Play sound and redirect
function handleAddToCart(e) {
    e.preventDefault();
    const audio = document.getElementById('addToCartSound');
    audio.currentTime = 0;
    audio.play().then(() => {
        setTimeout(() => {
            const url = e.target.closest('a').href;
            window.location.href = url.replace('my-wishlist.php', 'my-cart.php') + '&added=1';
        }, 500);
    }).catch(() => {
        const url = e.target.closest('a').href;
        window.location.href = url.replace('my-wishlist.php', 'my-cart.php') + '&added=1';
    });
    return false;
}

// Hide message box after 3 seconds
setTimeout(() => {
    const box = document.getElementById('messageBox');
    if (box) {
        box.style.opacity = '0';
        setTimeout(() => box.remove(), 500);
    }
}, 3000);
</script>

</body>
</html>
