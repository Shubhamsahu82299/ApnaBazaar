<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('includes/config.php');
if(isset($_GET['action']) && $_GET['action']=="buynow"){
    // Clear any previous Buy Now product
    unset($_SESSION['buynow_product']);
    
    $id = intval($_GET['id']);
    $variant_id = isset($_GET['variant_id']) ? intval($_GET['variant_id']) : 0;

    if ($variant_id) {
        $variant = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT * FROM product_variants WHERE id = $variant_id AND slot = '1'")
    );
        if ($variant) {
            $_SESSION['buynow_product'] = [
                "id" => $id,
                "quantity" => 1,
                "variant_id" => $variant_id,
                "variant_label" => $variant['variant_label'],
                "price" => $variant['price']
            ];
        }
    } else {
        $sql_p = "SELECT * FROM products WHERE id={$id}";
        $query_p = mysqli_query($con,$sql_p);
        if(mysqli_num_rows($query_p)!=0){
            $row_p = mysqli_fetch_array($query_p);
            $_SESSION['buynow_product'] = [
                "id" => $row_p['id'],
                "quantity" => 1,
                "price" => $row_p['productPrice']
            ];
        }
    }
    
    header("Location: my-cart(1).php");
    exit();
}
// Commented out GET action to prevent page reload - now using AJAX only
/*
if(isset($_GET['action']) && $_GET['action']=="add"){
  $id=intval($_GET['id']);
  if(isset($_SESSION['cart'][$id])){
    $_SESSION['cart'][$id]['quantity']++;
  }else{
    $sql_p="SELECT * FROM products WHERE id={$id}";
    $query_p=mysqli_query($con,$sql_p);
    if(mysqli_num_rows($query_p)!=0){
      $row_p=mysqli_fetch_array($query_p);
      $_SESSION['cart'][$row_p['id']]=array("quantity" => 1, "price" => $row_p['productPrice']);
    }else{
      $message="Product ID is invalid";
    }
  }

  header("Location: index.php");
  exit();
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <title>ApnaBazaar</title>

  <!-- CSS Files -->
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/variant-buttons.css">

  <style>
  html, body {
    overflow-x: hidden !important;
    width: 100% !important;
    background-color: #f8fafc; /* Premium clean light gray-blue background */
    color: #1e293b;
  }

  @media (min-width: 992px) {
    .hello {
      overflow-x: max-width !important;
      margin-top: 120px;
    }
    .row {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
    .col-*, .col {
      padding-left: 8px !important;
      padding-right: 8px !important;
    }

    img {
      max-width: 100%;
      height: auto;
    }
    .header {
      display: fixed !important;
      top: 0;
      z-index: 1000;
    }

    .product-image {
      width: 100%;
      height: 160px;
      object-fit: contain;
      border-radius: 6px;
      background: #ffffff;
    }

    .product-name {
      font-size: 0.95rem;
      font-weight: 600;
      color: #0f172a; /* Deep charcoal for elegant visibility */
      height: 38px;
      overflow: hidden;
    }

    .product-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 12px;
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out, border-color 0.2s;
    }

    .product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05), 0 8px 8px -6px rgba(0, 0, 0, 0.05);
      border-color: #cbd5e1;
    }

    @media (min-width: 1200px) {
      .col-lg-2-4,
      .col-xl-2-4 {
        flex: 0 0 auto;
        width: 20%;
        max-width: 20%;
      }
    }
  }

  @media (max-width: 768px) {
    body {
      padding-top: 70px;
    }
    body {
      overflow-x: hidden;
    }

    .row {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    .col-*, .col {
      padding-left: 8px !important;
      padding-right: 8px !important;
    }

    img {
      max-width: 100%;
      height: auto;
    }
    .header {
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(13, 148, 136, 0.08); /* Sophisticated smooth light shadow */
    }

    .product-image {
      width: 100%;
      height: 160px;
      object-fit: contain;
      border-radius: 6px;
    }

    .product-name {
      font-size: 0.95rem;
      font-weight: 600;
      color: #0f172a;
      height: 38px;
      overflow: hidden;
    }

    .product-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 10px;
      transition: transform 0.2s ease-in-out;
    }

    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.04);
    }

    @media (min-width: 1200px) {
      .col-lg-2-4,
      .col-xl-2-4 {
        flex: 0 0 auto;
        width: 20%;
        max-width: 20%;
      }
    }
    body {
      padding-top: 60px;
    }
  }

  /* Dropdown Selector Refresh */
  .form-select-sm {
    border: 1px solid #e2e8f0 !important;
    color: #475569 !important;
    font-weight: 500 !important;
    border-radius: 8px !important;
  }
  .form-select-sm:focus {
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.15) !important;
  }

  /* Section Title styling */
  .new-products h4 {
    color: #0f172a;
    font-weight: 700 !important;
  }

  /* Load More Button Overhaul */
  #load-more {
      background: #ffffff;
      color: #0d9488;
      border: 1.5px solid #0d9488;
      border-radius: 24px;
      padding: 10px 28px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.05);
  }

  /* Hover */
  #load-more:hover {
      background: #0d9488;
      color: #ffffff;
      border-color: #0d9488;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
  }

  /* Loading State CSS Adjustments */
  #load-more.loading {
      color: transparent !important;
      background: transparent !important;
      border: none !important;
      pointer-events: none;
  }

  /* Custom Clean Continuous Spinner Integration */
  #load-more.loading::before {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      width: 36px;
      height: 36px;
      margin: -18px 0 0 -18px;
      border-radius: 50%;
      background: conic-gradient(from 0deg, #0d9488, #10b981, #f8fafc, #0d9488);
      animation: spinner-rotate 0.8s linear infinite;
  }

  #load-more.loading::after {
      display: none !important; /* Disabled obsolete messy second indicator layer */
  }

  @keyframes spinner-rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
  }

  /* Premium Bottom Drawer Design Framework overrides */
  #variantDrawerOverlay {
    display: none;
    position: fixed;
    left: 0; right: 0; bottom: 0; top: 0;
    background: rgba(15, 23, 42, 0.3);
    backdrop-filter: blur(4px);
    z-index: 99999;
    align-items: flex-end;
    justify-content: center;
  }
  #variantDrawer {
    width: 100vw;
    max-width: 100vw;
    background: #ffffff;
    border-top-left-radius: 24px;
    border-top-right-radius: 24px;
    box-shadow: 0 -10px 25px -5px rgba(0,0,0,0.08);
    padding: 24px 20px 20px 20px;
    min-height: 120px;
    max-height: 75vh;
    overflow-y: auto;
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    animation: slideUpDrawer 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }
  @keyframes slideUpDrawer {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
  }
  #variantDrawerClose {
    position: absolute;
    top: 16px; 
    right: 16px;
    background: #f1f5f9;
    border: none;
    font-size: 20px;
    color: #64748b;
    cursor: pointer;
    z-index: 100000;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
  }
  #variantDrawerClose:hover {
    background-color: #e2e8f0;
    color: #0f172a;
  }
  #variantDrawerTitle {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 18px;
    text-align: left;
  }
  .variant-drawer-variant {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
  }
  .variant-drawer-label {
    font-size: 15px;
    font-weight: 600;
    color: #334155;
  }
  .variant-drawer-price {
    font-size: 15px;
    font-weight: 700;
    color: #0d9488;
    margin-left: 8px;
  }
  .variant-drawer-btn {
    background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 8px 22px;
    font-size: 14px;
    font-weight: 700;
    margin-left: 10px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(13, 148, 136, 0.15);
    transition: opacity 0.2s;
  }
  .variant-drawer-btn:hover {
    opacity: 0.95;
  }
  .variant-drawer-btn.buy {
    background: #0f172a; /* Sophisticated contrasting deep graphite obsidian accent */
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
  }
  @media (min-width: 601px) {
    #variantDrawerOverlay { display: none flex; }
  }
  
  hr {
    border-top: 1px solid #e2e8f0 !important;
    opacity: 1 !important;
  }
  /* 1. Body ka top padding adjust karein taaki content header ke piche na chupe */
body {
    background-color: #f8fafc; 
    padding-top: 160px !important; /* Mobile aur desktop spacing adjust karne ke liye */
}

/* 2. Header ki fixed position set karein aur invalid display rule hatayein */
.header {
    position: fixed !important; /* display: fixed galat tha, use position: fixed kiya */
    top: 0;
    left: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95); 
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); 
    border-bottom: 1px solid #f1f5f9;
    z-index: 999999;
    padding: 14px 32px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
  </style>
</head>
<header style="width: 100%;top: 0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.02); border-bottom: 1px solid #f1f5f9;">

  <!-- Top Header -->
  <div style="">
    <?php include('includes/main-header.php'); ?>
  </div>

<!-- HEADER START -->
</header>
  <!-- Menu Navigation Bar -->
  
       
<body class="cnt-home nn" style="">

<!-- HEADER END -->
<?php include('category(1).php');?>

<?php include('categories.php'); ?>
<?php include('subcategories.php'); ?>

 
<div class="hello">
 

 

  <section class="new-products" style="">
    <div class="d-flex justify-content-between align-items-center px-2 mb-2">
      <h4 class="fw-semibold mb-0">All Products</h4>
      <form method="GET">
        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 160px;">
          <option value="">Sort by</option>
          <option value="price_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
          <option value="price_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
          <option value="newest" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'newest') echo 'selected'; ?>>Newest First</option>
          <option value="name_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'name_asc') echo 'selected'; ?>>Name: A to Z</option>
          <option value="name_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'name_desc') echo 'selected'; ?>>Name: Z to A</option>
        </select>
      </form>
    </div>

    <div class="row g-2 px-2">
<?php
$limit = 12; // per page
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$sortQuery = "";
if (isset($_GET['sort'])) {
  switch ($_GET['sort']) {
    case 'price_asc': $sortQuery = "ORDER BY productPrice ASC"; break;
    case 'price_desc': $sortQuery = "ORDER BY productPrice DESC"; break;
    case 'newest': $sortQuery = "ORDER BY id DESC"; break;
    case 'name_asc': $sortQuery = "ORDER BY productName ASC"; break;
    case 'name_desc': $sortQuery = "ORDER BY productName DESC"; break;
  }
}
$ret = mysqli_query($con, "SELECT * FROM products $sortQuery LIMIT $limit OFFSET $offset");

?>
<div id="product-container">
<?php
if (mysqli_num_rows($ret) > 0) {
  while ($row = mysqli_fetch_array($ret)) {
    include('includes/productgrid.php');
  }
} else {
  echo "<div class='col-12 text-center py-4'><p class='text-muted'>No products available right now.</p></div>";
}
?>
</div>


<!-- Load More Button -->
<div style="text-align:center; margin-top: 24px; margin-bottom: 24px;">
  <button id="load-more" class="btn load-more-btn" data-offset="<?php echo $offset + $limit; ?>">Load More</button>
</div>

    </div>
  </section>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const loadMoreBtn = document.getElementById('load-more');
    if (!loadMoreBtn) return;

    let isLoading = false; // lock flag

    const loadProducts = () => {
        if (isLoading) return; // already loading, ignore
        isLoading = true;

        let offset = parseInt(loadMoreBtn.dataset.offset);
        let sort = new URLSearchParams(window.location.search).get('sort') || '';

        // Add spinner
        loadMoreBtn.classList.add('loading');
        loadMoreBtn.innerText = ""; // hide text while spinner visible

        fetch("load_products.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `offset=${offset}&sort=${sort}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "no_more") {
                loadMoreBtn.style.display = "none"; // hide button
                window.removeEventListener('scroll', onScroll); // stop listening
            } else {
                document.getElementById('product-container').insertAdjacentHTML('beforeend', data);
                loadMoreBtn.dataset.offset = offset + 10;
            }
        })
        .finally(() => {
            isLoading = false; // release lock
            loadMoreBtn.classList.remove('loading');
            loadMoreBtn.innerText = "Load More";
        });
    };

    const onScroll = () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;

        // Trigger only if scroll >= 300px AND button near viewport
        if (scrollTop >= 300) {
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


  <hr />
  </div>

  <?php include('includes/footer.php');?>

  <script src="assets/js/jquery-1.11.1.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/owl.carousel.min.js"></script>
  <script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
  <script src="assets/js/echo.min.js"></script>
  <script src="assets/js/jquery.easing-1.3.min.js"></script>
  <script src="assets/js/bootstrap-slider.min.js"></script>
  <script src="assets/js/jquery.rateit.min.js"></script>
  <script src="assets/js/lightbox.min.js"></script>
  <script src="assets/js/bootstrap-select.min.js"></script>
  <script src="assets/js/wow.min.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    setTimeout(function() {
      let alert = document.querySelector('.alert');
      if(alert){
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = 0;
        setTimeout(() => alert.remove(), 500);
      }
    }, 3000);
  </script>
  <script>
    $(document).ready(function(){ 
      $(".changecolor").switchstylesheet({ seperator:"color" });
      $('.show-theme-options').click(function(){
        $(this).parent().toggleClass('open');
        return false;
      });

      $(window).bind("load", function() {
        $('.show-theme-options').delay(2000).trigger('click');
      });

      $("#owl-main").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        nav: true,
        dots: true
      });
    });
  </script>
  <script>
  // Poll every 3 seconds to check cart count
  setInterval(() => {
    fetch('cart-count.php')
      .then(res => res.text())
      .then(count => {
        count = parseInt(count);
        if (count <= 0) {
          const cart = document.getElementById('floating-cart-beautiful');
          if (cart) {
            cart.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            cart.style.opacity = 0;
            cart.style.transform = 'translateX(-50%) translateY(30px)';
          }
        }
      });
  }, 3000); // every 3 seconds
</script>
<!-- Variant Selection Modal (now only once per page) -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal close
    document.getElementById('closeVariantModal').onclick = function() {
        document.getElementById('variantModal').style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target === document.getElementById('variantModal')) {
            document.getElementById('variantModal').style.display = 'none';
        }
    };
    // Delegate for Add/Buy buttons in modal
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('variant-add-btn')) {
            addToCart(e.target.getAttribute('data-product-id'), e.target.getAttribute('data-variant-id'), null);
            document.getElementById('variantModal').style.display = 'none';
        }
        if (e.target.classList.contains('variant-buy-btn')) {
            window.location.href = `index.php?page=product&action=buynow&id=${e.target.getAttribute('data-product-id')}&variant_id=${e.target.getAttribute('data-variant-id')}`;
        }
    });
});
function showVariantModal(productId, variants, productName, isBuyNow) {
    document.getElementById('variantModalTitle').textContent = productName;
    let html = '';
    variants.forEach(function(variant) {
        html += `<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">`
            + `<div style="display:flex;align-items:center;gap:10px;">`
            + (variant.variant_image ? `<img src="admin/productimages/${productId}/${variant.variant_image}\" style="width:38px;height:38px;object-fit:contain;border-radius:6px;">` : '')
            + `<div>`
            + `<div style="font-size:15px;font-weight:600;color:#1e293b;">${variant.variant_label}</div>`
            + `<div style="font-size:14px;">`
            + (variant.discount_percent && variant.discount_percent > 0 ? `<span style='color:#0d9488;font-weight:600;font-size:13px;margin-right:6px;'>${variant.discount_percent}% OFF</span>` : '')
            + `<span style="color:#0f172a;font-weight:700;">₹${variant.price}</span>`
            + (variant.mrp && variant.mrp > variant.price ? `<span style='color:#94a3b8;font-size:13px;margin-left:6px;'><del>₹${variant.mrp}</del></span>` : '')
            + `</div></div></div>`
            
            + (isBuyNow ? `<button class="variant-buy-btn" data-product-id="${productId}" data-variant-id="${variant.id}" style="background:#0f172a;border:none;color:#fff;font-weight:700;padding:6px 18px;border-radius:20px;cursor:pointer;font-size:14px;margin-left:8px;">BUY</button>` : `<button class="variant-add-btn" data-product-id="${productId}" data-variant-id="${variant.id}" style="background:#fff;border:1.5px solid #0d9488;color:#0d9488;font-weight:700;padding:6px 18px;border-radius:20px;cursor:pointer;font-size:14px;">ADD</button>`)
            + `</div>`;
    });
    document.getElementById('variantModalVariants').innerHTML = html;
    document.getElementById('variantModal').style.display = 'flex';
}

setTimeout(function() {
    document.querySelectorAll('.add-to-cart-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            const variants = JSON.parse(this.getAttribute('data-variants'));
            const productId = this.getAttribute('data-product-id');
            const productName = this.closest('.product-card').querySelector('.product-name').textContent;
            if (variants.length > 1) {
                e.preventDefault();
                window.showVariantDrawer(productId, variants, productName, false);
            }
        });
    });

    document.querySelectorAll('.buy-now-btn.variant-trigger-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            const variants = JSON.parse(this.getAttribute('data-variants'));
            const productId = this.getAttribute('data-product-id');
            const productName = this.closest('.product-card').querySelector('.product-name').textContent;
            if (variants.length > 1) {
                e.preventDefault();
                window.showVariantDrawer(productId, variants, productName, true);
            }
        });
    });
}, 500);

</script>

<div id="variantDrawerOverlay">
  <div id="variantDrawer">
    <button id="variantDrawerClose">&times;</button>
    <div id="variantDrawerTitle"></div>
    <div id="variantDrawerVariants"></div>
  </div>
</div>
<script>
window.showVariantDrawer = function(productId, variants, productName, isBuy) {
  $('#variantDrawerTitle').text(productName);
  var html = '';
  variants.forEach(function(variant) {
    html += '<div class="variant-drawer-variant">';
    html += '<div><span class="variant-drawer-label">' + variant.variant_label + '</span>';
    html += '<span class="variant-drawer-price">₹' + variant.price + '</span>';
    html += '</div>';
    if (variant.stock == 0) {
      html += '<span style="color:#fff;background:#ef4444;padding:4px 14px;border-radius:16px;font-size:14px;font-weight:700;">Out of Stock</span>';
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
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#10b981'; /* Clean Mint Green */
    } else {
        notification.style.backgroundColor = '#ef4444'; /* Crimson Alert */
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


window.addToCart = function(productId, variantId) {
  console.log('addToCart function called with:', productId, variantId);
  
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
    }
  }).fail(function(xhr, status, error) {
    console.error('AJAX request failed:', error);
    showNotification('product added to cart. Please try again.', 'error');
  });
};
window.openVariantDrawer = function(btn) {
  var variants = $(btn).data('variants');
  if (typeof variants === 'string') variants = JSON.parse(variants);
  var productId = $(btn).data('product-id');
  var productName = $(btn).closest('.product-card').find('.product-name').text();
  var isBuy = $(btn).hasClass('buy-now-btn');
  if (variants.length > 1 && window.innerWidth <= 50000) 
  {
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
});
</script>

<!-- Floating Cart Element -->
<?php include('includes/view-cart.php'); ?>

</body>
</html>