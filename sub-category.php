<?php
session_start();
error_reporting(0);
include('includes/config.php');

$cid = intval($_GET['scid']);

// Add to Cart
if (isset($_GET['action']) && $_GET['action'] == "add") {
    $id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        $sql_p = "SELECT * FROM products WHERE id={$id}";
        $query_p = mysqli_query($con, $sql_p);
        if (mysqli_num_rows($query_p) != 0) {
            $row_p = mysqli_fetch_array($query_p);
            $_SESSION['cart'][$row_p['id']] = array("quantity" => 1, "price" => $row_p['productPrice']);
          $_SESSION['message'] = "Product has been added to the cart!";
header("Location:sub-category.php?scid=$cid");
exit();
        }
    }
}

// Wishlist
if (isset($_GET['pid']) && $_GET['action'] == "wishlist") {
    if (strlen($_SESSION['login']) == 0) {
        header('location:login.php');
    } else {
        mysqli_query($con, "INSERT INTO wishlist(userId,productId) VALUES('" . $_SESSION['id'] . "','" . $_GET['pid'] . "')");
        echo "<script>alert('Product added to wishlist');</script>";
        header('location:my-wishlist.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Product Subcategory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/title.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/red.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/css/owl.transitions.css">
    <link rel="stylesheet" href="assets/css/lightbox.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/rateit.css">
    <link rel="stylesheet" href="assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/variant-buttons.css">
    <style>
         @media (min-width: 992px) {
  .hello {
   margin-top:50px;
  }
  }
       @media (max-width: 768px) {
  .hello {
   
   margin-top:-5px

  }
  }
  .subcat-link {
    padding: 6px 14px;
    border-radius: 10px;
    background-color: #f2f2f2;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: background 0.3s;
}

.subcat-link:hover {
    background-color: #ddd;
}

.subcat-link.active-subcat {
    
}
.subcat-list {
  margin-bottom:0px;
  display: flex;
  flex-wrap: nowrap; /* IMPORTANT */
  gap: 2px;
  overflow-x: auto; /* Enables horizontal scroll */
  scrollbar-width: thin; /* Firefox */
  padding-bottom: 0px;
}
.subcat-list::-webkit-scrollbar {
  height: 5px;
}
.subcat-list::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}
.subcat-list {
  -webkit-overflow-scrolling: touch; /* iOS momentum scroll */
}

    </style>
</head>
<body class="cnt-home">

<!-- ==== HEADER ==== -->

<header style="width: 100%; position: sticky; top: 0; z-index: 110000; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <?php include('includes/main-header.php'); ?>
</header>
<!-- ================ -->

<?php include('category(1).php'); ?>

<div class="container hello" style="">
    <div class="row hello ">
            <div style=""><?php include('includes/subcategory.php'); ?></div>   
        <div class='col-md-12 hello'>
            <?php
           $query = mysqli_query($con, "
    SELECT subcategory.subcategory AS subcatName, category.categoryName AS catName
    FROM subcategory
    JOIN category ON category.id = subcategory.categoryid
    WHERE subcategory.id = '$cid'
");
$catRow = mysqli_fetch_array($query);
            ?>
         

     
<?php
$cid = intval($_GET['scid']); // Subcategory ID

// Subcategory info fetch
$subcatQuery = mysqli_query($con, "
    SELECT categoryid, subcategory 
    FROM subcategory 
    WHERE id = '$cid'
");
$subcatData = mysqli_fetch_array($subcatQuery);
$categoryId = $subcatData['categoryid'];
$currentSubcatName = $subcatData['subcategory'];

// All subcategories under this category
$allSubcats = mysqli_query($con, "
    SELECT id, subcategory 
    FROM subcategory 
    WHERE categoryid = '$categoryId'
");
?>

<!-- Subcategory links -->
<div class="subcat-wrapper">
    <div class="subcat-list">
        <?php while ($sub = mysqli_fetch_array($allSubcats)) { 
            $isActive = ($sub['id'] == $cid) ? 'active-subcat' : '';
        ?>
            <a href="sub-category.php?scid=<?php echo $sub['id']; ?>" 
               class="subcat-link <?php echo $isActive; ?>">
               <?php echo htmlentities($sub['subcategory']); ?>
            </a>
        <?php } ?>
    </div>
    <div class="more-btn-wrapper">
        <button class="more-btn" onclick="toggleSubcats()">More</button>
    </div>
</div>

<style>
.subcat-wrapper {
  max-width: 100%;
  overflow: hidden;
  background:transparent;
}

.subcat-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  max-height: 85px; /* ~2 rows */
  overflow-x: auto;
  overflow-y: hidden;
  scroll-behavior: smooth;
  padding: 6px 0;
  margin-bottom: 12px; /* space before button */
}

/* Scrollbar styling (optional) */
.subcat-list::-webkit-scrollbar {
  height: 6px;
}
.subcat-list::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px;
}

.subcat-link {
  flex: 0 0 auto;
  padding: 6px 14px;
  background: #f5f5f5;
  border-radius: 20px;
  text-decoration: none;
  color: #333;
  font-size: 14px;
  white-space: nowrap;
  transition: background 0.2s, color 0.2s;
}

.subcat-link:hover {
  background: #e0e0e0;
}

.active-subcat {
  background: #007bff;
  color: #fff;
}

/* Centered More/Less button */
.more-btn-wrapper {
    background:transparent;
    padding:0px;
    margin-top:-10px;
    margin-bottom:4px;
  text-align: center;
}

.more-btn {
 
  padding: 6px 14px;
  background: #007bff;
  color: #fff;
  border: none;
  border-radius: 16px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.more-btn:hover {
  background: #0056b3;
}

/* Load More Button Base */
#load-more {
    background: #fff9c4;
    color: #222;
    border: 1px solid #ffe082;
    border-radius: 18px;
    padding: 10px 24px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, transform 0.15s, border-color 0.2s;
}

/* Hover */
#load-more:hover {
    background: #fff176;
    border-color: #ffd600;
    transform: translateY(-1px);
}

/* Loading State */
#load-more.loading {
    color: transparent !important;
    background: transparent !important;
    border: none !important;
    pointer-events: none;
}

/* Outer ring – big and vibrant */
#load-more.loading::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, #ffd600, #ffea00, #ffc107, #ffd600);
    box-shadow: 0 0 12px rgba(255, 193, 7, 0.6);
    animation: spinner-morph 1.2s linear infinite, spinner-rotate 1s linear infinite;
}

/* Inner ring – smaller & complementary color */
#load-more.loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    margin: -12px 0 0 -12px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, #fff176, #ffd600, #ffca28, #fff176);
    box-shadow: 0 0 8px rgba(255, 235, 59, 0.5);
    animation: spinner-morph-inner 1.5s ease-in-out infinite, spinner-rotate-reverse 1.2s linear infinite;
}

@keyframes spinner-rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes spinner-rotate-reverse {
    0% { transform: rotate(360deg); }
    100% { transform: rotate(0deg); }
}

@keyframes spinner-morph {
    0%,100% { border-radius: 50% 50% 50% 50%; }
    25% { border-radius: 55% 60% 45% 50%; }
    50% { border-radius: 60% 50% 50% 40%; }
    75% { border-radius: 50% 40% 60% 50%; }
}

@keyframes spinner-morph-inner {
    0%,100% { border-radius: 50% 50% 50% 50%; }
    25% { border-radius: 60% 50% 55% 45%; }
    50% { border-radius: 50% 60% 40% 50%; }
    75% { border-radius: 55% 45% 50% 60%; }
}

/* Mobile tweaks */
@media (max-width: 480px) {
    #load-more {
        padding: 8px 18px;
        font-size: 14px;
    }
}

</style>

<script>
function toggleSubcats() {
  const list = document.querySelector('.subcat-list');
  const btn = document.querySelector('.more-btn');
  
  if (list.style.maxHeight === 'none') {
    list.style.maxHeight = '80px'; // collapse to 2 rows
    btn.innerText = 'More';
  } else {
    list.style.maxHeight = 'none'; // expand
    btn.innerText = 'Less';
  }
}
</script>

<!-- Products Container -->
<div class="search-result-container">
    <div id="myTabContent" class="tab-content">
        <div class="tab-pane active" id="grid-container">
            <div class="category-product inner-top-vs">
                <div class="row" id="product-container">
                    <?php
                    $limit = 12;
                    $offset = 0;
                    //$query = "SELECT * FROM products WHERE subCategory='$cid' AND productAvailability='In Stock' LIMIT $limit OFFSET $offset";
                    $query = "SELECT * FROM products WHERE subCategory='$cid' LIMIT $limit OFFSET $offset";

                    $ret = mysqli_query($con, $query);
                    $count = mysqli_num_rows($ret);

                    if ($count > 0) {
                        while ($row = mysqli_fetch_array($ret)) {
                            include('includes/productgridsubcat.php');
                        }
                    } else {
                        echo "<div class='col-sm-6 col-md-4 wow fadeInUp'><h3>No Product Found</h3></div>";
                    }
                    ?>
                </div>

                <?php if ($count >= $limit): ?>
                <div style="text-align:center; margin:20px;">
                    <button id="load-more"  class="btn load-more-btn" 
                            data-offset="<?php echo $offset + $limit; ?>"
                            data-scid="<?php echo $cid; ?>">
                        Load More
                    </button>
                   

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
function initProductGridFeatures(scope = document) {
    console.log('Initializing product grid features...');

    // 🔹 Lazy Load Images
    scope.querySelectorAll("img.lazy-img").forEach(img => {
        if (img.dataset.src && !img.dataset.loaded) {
            img.src = img.dataset.src;
            img.dataset.loaded = "true";
        }
    });

    // 🔹 Add to Cart Buttons
    scope.querySelectorAll('.add-to-cart-btn').forEach(function(button) {
        if (!button.dataset.bound) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.classList.contains('variant-trigger-btn')) {
                    if (window.innerWidth <= 50000) {
                        const variants = JSON.parse(this.dataset.variants);
                        const productId = this.dataset.productId;
                        const productName = this.closest('.product-card').querySelector('.product-name').textContent;
                        window.showVariantDrawer(productId, variants, productName, false);
                    } else {
                        addProductToCart(this, this.dataset.variantId);
                    }
                } else {
                    addProductToCart(this, this.dataset.variantId);
                }
            });
            button.dataset.bound = "true";
        }
    });

    // 🔹 Buy Now Buttons
    scope.querySelectorAll('.buy-now-btn').forEach(function(button) {
        if (!button.dataset.bound) {
            button.addEventListener('click', function(e) {
                if (this.classList.contains('variant-trigger-btn')) {
                    e.preventDefault();
                    if (window.innerWidth <= 50000) {
                        const variants = JSON.parse(this.dataset.variants);
                        const productId = this.dataset.productId;
                        const productName = this.closest('.product-card').querySelector('.product-name').textContent;
                        window.showVariantDrawer(productId, variants, productName, true);
                    } else {
                        const productId = this.dataset.productId;
                        const variantId = this.dataset.variantId;
                        window.location.href = `index?action=buynow&id=${productId}&variant_id=${variantId}`;
                    }
                }
            });
            button.dataset.bound = "true";
        }
    });
}

// -----------------------
// Page Load
// -----------------------
document.addEventListener("DOMContentLoaded", () => {
    initProductGridFeatures();
    checkCartStatus();
});

// -----------------------
// Load More
// -----------------------


document.addEventListener("DOMContentLoaded", function() {
    const loadMoreBtn = document.getElementById('load-more');
    if (!loadMoreBtn) return;

    let isLoading = false; // prevent duplicate fetches

    const loadProducts = () => {
        if (isLoading) return;
        isLoading = true;

        let offset = parseInt(loadMoreBtn.dataset.offset);
        let scid = loadMoreBtn.dataset.scid;

        // Show spinner
        loadMoreBtn.classList.add('loading');
        loadMoreBtn.innerText = "";

        fetch("load_subcategory_products.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `offset=${offset}&scid=${scid}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "no_more") {
                loadMoreBtn.style.display = "none";
                window.removeEventListener('scroll', onScroll);
            } else {
                const container = document.getElementById('product-container');
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = data;
                container.append(...tempDiv.childNodes);

                // Re-initialize functions
                if (typeof initProductGridFeatures === "function") initProductGridFeatures(container);
                if (typeof checkCartStatus === "function") checkCartStatus();

                loadMoreBtn.dataset.offset = offset + 10;
            }
        })
        .finally(() => {
            isLoading = false;
            loadMoreBtn.classList.remove('loading');
            loadMoreBtn.innerText = "Load More";
        });
    };

    const onScroll = () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        if (scrollTop >= 400) {
            const rect = loadMoreBtn.getBoundingClientRect();
            const windowHeight = window.innerHeight || document.documentElement.clientHeight;

            if (rect.top <= windowHeight + 100) {
                loadProducts();
            }
        }
    };

    window.addEventListener('scroll', onScroll);
});




</script>
</div>
</div>
</div>
<?php include 'includes/floating-btn.php'; ?>

<?php include('includes/view-cart.php'); ?>

<?php include('includes/footer.php'); ?>

<!-- Variant Bottom Drawer (mobile only) -->
<!-- orginal <style>
#variantDrawerOverlay {
  display: none;
  position: fixed;
  left: 0; right: 0; bottom: 0; top: 0;
  background: rgba(0,0,0,0.18);
  z-index: 99999;
  align-items: flex-end;
  justify-content: center;
}
#variantDrawer {
  width: 100vw;
  max-width: 100vw;
  background: #fff;
  border-top-left-radius: 18px;
  border-top-right-radius: 18px;
  box-shadow: 0 -2px 16px rgba(0,0,0,0.10);
  padding: 18px 12px 12px 12px;
  min-height: 120px;
  max-height: 70vh;
  overflow-y: auto;
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  animation: slideUpDrawer 0.25s cubic-bezier(.4,1.4,.6,1);
}
@keyframes slideUpDrawer {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
#variantDrawerClose {
  position: absolute;
  top: 8px; 
  right: 16px;
  background: none;
  border: none;
  font-size: 28px;
  color: #888;
  cursor: pointer;
  z-index: 100000;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}
#variantDrawerClose:hover {
  background-color: rgba(0,0,0,0.1);
}
#variantDrawerTitle {
  font-size: 17px;
  font-weight: 700;
  margin-bottom: 12px;
  text-align: left;
}
.variant-drawer-variant {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}
.variant-drawer-label {
  font-size: 15px;
  font-weight: 600;
}
.variant-drawer-price {
  font-size: 14px;
  font-weight: 700;
  color: #222;
  margin-left: 8px;
}
.variant-drawer-btn {
  background: #fb641b;
  color: #fff;
  border: none;
  border-radius: 18px;
  padding: 6px 18px;
  font-size: 15px;
  font-weight: 700;
  margin-left: 10px;
  cursor: pointer;
}
.variant-drawer-btn.buy {
  background: #ff9f00;
}
@media (min-width: 601px) {

#variantDrawerOverlay {
  display: none;
  position: fixed;
  left: 0; right: 0; bottom: 0; top: 0;
  background: rgba(0,0,0,0.18);
  z-index: 99999;
  align-items: flex-end;
  justify-content: center;
}
#variantDrawer {
  width: 100vw;
  max-width: 100vw;
  background: #fff;
  border-top-left-radius: 18px;
  border-top-right-radius: 18px;
  box-shadow: 0 -2px 16px rgba(0,0,0,0.10);
  padding: 18px 12px 12px 12px;
  min-height: 120px;
  max-height: 70vh;
  overflow-y: auto;
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  animation: slideUpDrawer 0.25s cubic-bezier(.4,1.4,.6,1);
}
@keyframes slideUpDrawer {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
#variantDrawerClose {
  position: absolute;
  top: 8px; 
  right: 16px;
  background: none;
  border: none;
  font-size: 28px;
  color: #888;
  cursor: pointer;
  z-index: 100000;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}
#variantDrawerClose:hover {
  background-color: rgba(0,0,0,0.1);
}
#variantDrawerTitle {
  font-size: 17px;
  font-weight: 700;
  margin-bottom: 12px;
  text-align: left;
}
.variant-drawer-variant {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}
.variant-drawer-label {
  font-size: 15px;
  font-weight: 600;
}
.variant-drawer-price {
  font-size: 14px;
  font-weight: 700;
  color: #222;
  margin-left: 8px;
}
.variant-drawer-btn {
  background: #fb641b;
  color: #fff;
  border: none;
  border-radius: 18px;
  padding: 6px 18px;
  font-size: 15px;
  font-weight: 700;
  margin-left: 10px;
  cursor: pointer;
}
.variant-drawer-btn.buy {
  background: #ff9f00;
}
}
</style>  -->

<div id="variantDrawerOverlay">
  <div id="variantDrawer">
    <button id="variantDrawerClose">&times;</button>
    <div id="variantDrawerTitle"></div>
    <div id="variantDrawerVariants"></div>
  </div>
</div>

<!-- JS Files -->
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/echo.min.js"></script>
<script src="assets/js/jquery.easing-1.3.min.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script>
<script src="assets/js/jquery.rateit.min.js"></script>
<script src="assets/js/lightbox.min.js"></script>
<script src="assets/js/bootstrap-select.min.js"></script>
<script src="assets/js/wow.min.js"></script>
<script src="assets/js/scripts.js"></script>

<!-- Theme Switcher (Optional) -->
<script src="switchstylesheet/switchstylesheet.js"></script>
<script>
    $(document).ready(function () {
        $(".changecolor").switchstylesheet({seperator: "color"});
        $('.show-theme-options').click(function () {
            $(this).parent().toggleClass('open');
            return false;
        });
    });
    $(window).bind("load", function () {
        $('.show-theme-options').delay(2000).trigger('click');
    });
</script>

<script>
// Notification function
function showNotification(message, type) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        z-index: 10000000000000;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else {
        notification.style.backgroundColor = '#dc3545';
    }
    
    notification.textContent = message;
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Floating cart function
let floatingCartTimeout = null;
function updateFloatingCart(cartCount, cartTotal) {
  // Function works but doesn't show anything on screen
  console.log('Cart updated - Count:', cartCount, 'Total:', cartTotal);
  
  // Clear existing timeout if any
  if (floatingCartTimeout) {
    clearTimeout(floatingCartTimeout);
  }
  
  // Set timeout to clear after 5 seconds (function still works but no visual)
  floatingCartTimeout = setTimeout(() => {
    console.log('Floating cart timeout cleared');
  }, 5000);
}

window.addToCart = function(productId, variantId) {
   // Show quantity overlay immediately
   const qtyOverlay = document.getElementById('qty-overlay-' + productId);
  if (qtyOverlay) {
    qtyOverlay.style.display = 'block';
    qtyOverlay.style.opacity = '1';
    qtyOverlay.style.transform = 'scale(1)';
    
    const qtyDisplay = document.getElementById('product-qty-overlay-' + productId);
    if (qtyDisplay) {
      qtyDisplay.textContent = '1';
    }
    console.log('Quantity overlay shown for product:', productId);
  } else {
    console.error('Quantity overlay not found for product:', productId);
  }
  
  console.log('addToCart function called with:', productId, variantId);
  $.post('add-to-cart.php', { id: productId, action: 'add', quantity: 1, variant_id: variantId }, function(data) {
    console.log('add-to-cart.php response:', data);
    if (typeof data === 'string') {
      try { data = JSON.parse(data); } catch(e) {}
    }
    if (data && data.message) {
      showNotification(data.message, 'success');
    }
    if (data && data.cartCount) {
      $('.cart-count').text(data.cartCount);
    }
    if (data && data.cartTotal) {
      updateFloatingCart(data.cartCount, data.cartTotal);
      // Also update the view-cart.php floating cart if it exists
      if (typeof window.updateFloatingCartViewCart === 'function') {
        window.updateFloatingCartViewCart(data.cartCount, data.cartTotal);
      }
    }
  }).fail(function(xhr, status, error) {
    console.error('AJAX request failed:', error);
    showNotification('Failed to add product to cart. Please try again.', 'error');
  });
};

window.showVariantDrawer = function(productId, variants, productName, isBuy) {
  $('#variantDrawerTitle').text(productName);
  var html = '';
  variants.forEach(function(variant) {
    html += '<div class="variant-drawer-variant">';
    html += '<div><span class="variant-drawer-label">' + variant.variant_label + '</span>';
    html += '<span class="variant-drawer-price">₹' + variant.price + '</span>';
    html += '</div>';
    if (variant.stock == 0) {
      html += '<span style="color:#fff;background:#dc3545;padding:4px 14px;border-radius:16px;font-size:14px;font-weight:700;">Out of Stock</span>';
    } else if (isBuy) {
      html += '<button class="variant-drawer-btn buy" data-product-id="' + productId + '" data-variant-id="' + variant.id + '">Buy</button>';
    } else {
      html += '<button class="variant-drawer-btn" data-product-id="' + productId + '" data-variant-id="' + variant.id + '">Add</button>';
    }
    html += '</div>';
  });
  $('#variantDrawerVariants').html(html);
  $('#variantDrawerOverlay').fadeIn(120);
  
  // Add direct click handler to close button
  $('#variantDrawerClose').off('click').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Direct close button clicked!');
    $('#variantDrawerOverlay').fadeOut(120);
  });
  
  // Add direct click handlers for add/buy buttons
  $('.variant-drawer-btn').off('click').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Direct add/buy button clicked!');
    var productId = $(this).data('product-id');
    var variantId = $(this).data('variant-id');
    console.log('Direct - Product ID:', productId, 'Variant ID:', variantId);
    if ($(this).hasClass('buy')) {
      window.location.href = 'index.php?action=buynow&id=' + productId + '&variant_id=' + variantId;
    } else {
      console.log('Direct - Adding to cart...');
      window.addToCart(productId, variantId);
      $('#variantDrawerOverlay').fadeOut(120);
    }
  });
};

window.openVariantDrawer = function(btn) {
  var variants = $(btn).data('variants');
  if (typeof variants === 'string') variants = JSON.parse(variants);
  var productId = $(btn).data('product-id');
  var productName = $(btn).closest('.product-card').find('.product-name').text();
  var isBuy = $(btn).hasClass('buy-now-btn');
  if (variants.length > 1 && window.innerWidth <= 50000) {
    window.showVariantDrawer(productId, variants, productName, isBuy);
  }
};

$(function() {
  // Close button click handler
  $(document).on('click', '#variantDrawerClose', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Close button clicked!');
    $('#variantDrawerOverlay').fadeOut(120);
  });
  
  // Overlay click handler (close when clicking outside)
  $(document).on('click', '#variantDrawerOverlay', function(e) {
    if (e.target === this) {
      $('#variantDrawerOverlay').fadeOut(120);
    }
  });
  
  // Add/Buy button click handlers
  $(document).on('click', '.variant-drawer-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Add/Buy button clicked!');
    var productId = $(this).data('product-id');
    var variantId = $(this).data('variant-id');
    console.log('Product ID:', productId, 'Variant ID:', variantId);
    if ($(this).hasClass('buy')) {
      window.location.href = 'index.php?action=buynow&id=' + productId + '&variant_id=' + variantId;
    } else {
      console.log('Adding to cart...');
      window.addToCart(productId, variantId);
      $('#variantDrawerOverlay').fadeOut(120);
    }
  });
  
  // Handle buy now buttons from product grid
  $(document).on('click', '.buy-now-btn', function(e) {
    // Check if this is a variant trigger button
    if ($(this).hasClass('variant-trigger-btn')) {
      e.preventDefault();
      // Open variant drawer for mobile
      if (window.innerWidth <= 50000) {
        var variants = $(this).data('variants');
        if (typeof variants === 'string') variants = JSON.parse(variants);
        var productId = $(this).data('product-id');
        var productName = $(this).closest('.product-card').find('.product-name').text();
        window.showVariantDrawer(productId, variants, productName, true);
      } else {
        // For desktop, redirect to buy with first variant
        var productId = $(this).data('product-id');
        var variantId = $(this).data('variant-id');
        window.location.href = 'index.php?action=buynow&id=' + productId + '&variant_id=' + variantId;
      }
    }
    // Non-variant buy buttons will work normally via href (no preventDefault)
  });
});
</script>

<!-- ✅ Scroll-based Header Compact JS -->
<script>
    let lastScrollY = window.scrollY;
    const header = document.querySelector('.header');
    let ticking = false;

    function handleScroll() {
        const currentScrollY = window.scrollY;

        if (currentScrollY > lastScrollY + 20) {
            header.classList.add('compact');
        } else if (currentScrollY < lastScrollY - 20) {
            header.classList.remove('compact');
        }

        lastScrollY = currentScrollY;
        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(handleScroll);
            ticking = true;
        }
    });
</script>

</body>
</html>
