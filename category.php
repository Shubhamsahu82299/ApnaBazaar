<?php
session_start();
error_reporting(0);
include('includes/config.php');

$cid = intval($_GET['cid']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Category</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="assets/images/title.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/variant-buttons.css">
    <style>
        #floating-cart-box {
            position: fixed;
            bottom: 20px;
            right: 50%;
            transform: translateX(50%);
            z-index: 999;
            min-width: 220px;
            background: white;
            border-radius: 30px;
            padding: 8px 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
        }
        .hello{
            margin-top:90px;
        }
       @media (min-width: 992px) {
  .hello {
   margin-top:120px;
  }
  }
       @media (max-width: 768px) {
  .hello {
   padding:top:0px;margin-top:0px
  }
  }
        
    </style>
</head>
<body class="cnt-home "  >

<?php include('includes/main-header.php'); ?>
<?php include('category(1).php');?>


<div class="container  hello" style="">
    <div class="row">
        <div class='col-md-12'>
            <?php
            $sql = mysqli_query($con, "SELECT categoryName FROM category WHERE id='$cid'");
            $row = mysqli_fetch_array($sql);
            ?>
            <?php include('includes/subcategory.php'); ?>

            <!-- Flash message after redirect -->
            <?php if (isset($_SESSION['message'])): ?>
              <div id="cart-message"
                   style="position:fixed;bottom:100px;left:50%;transform:translateX(-50%);background:#28a745;color:white;padding:8px 16px;border-radius:20px;z-index:1000;font-size:14px;">
                  <?= $_SESSION['message']; unset($_SESSION['message']); ?>
              </div>
              <script>
                  setTimeout(() => {
                      const msg = document.getElementById('cart-message');
                      if (msg) {
                          msg.style.opacity = 0;
                          setTimeout(() => msg.remove(), 500);
                      }
                  }, 2500);
              </script>
            <?php endif; ?>

            <div class="row">
               <?php
$cid = intval($_GET['cid']); // category id
$limit = 12; 
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

//$query = "SELECT * FROM products WHERE category='$cid' AND productAvailability='In Stock' LIMIT $limit OFFSET $offset";
$query = "SELECT * FROM products WHERE category='$cid' LIMIT $limit OFFSET $offset";

$ret = mysqli_query($con, $query);
?>

<div id="product-container">
<?php
if (mysqli_num_rows($ret) > 0) {
    while ($row = mysqli_fetch_array($ret)) {
        include('includes/productgrid.php');
    }
} else {
    echo "<div class='col-md-12'><h3>No Product Found</h3></div>";
}
?>
</div>

<?php if (mysqli_num_rows($ret) >= $limit): ?>
<div style="text-align:center; margin:20px;">
    <button id="load-more" class="btn load-more-btn" 
        data-offset="<?php echo $offset + $limit; ?>" 
        data-cid="<?php echo $cid; ?>">
    Loading
</button>

<style>
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

</div>
<?php endif; ?>
<script>
/* document.addEventListener("DOMContentLoaded", function() {
    const loadMoreBtn = document.getElementById('load-more');
    if (!loadMoreBtn) return;

    loadMoreBtn.addEventListener('click', function() {
        let button = this;
        let offset = parseInt(button.dataset.offset);
        let cid = button.dataset.cid;

        button.innerText = "Loading...";

        fetch("load_category_products.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `offset=${offset}&cid=${cid}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "no_more") {
                button.style.display = "none";
            } else {
                document.getElementById('product-container')
                        .insertAdjacentHTML('beforeend', data);
                button.dataset.offset = offset + 10;
                button.innerText = "Load More";
            }
        });
    });
}); */
document.addEventListener("DOMContentLoaded", function() {
    const loadMoreBtn = document.getElementById('load-more');
    if (!loadMoreBtn) return;

    let isLoading = false; // prevent multiple calls

    function loadProducts() {
        if (isLoading) return;
        isLoading = true;

        let button = loadMoreBtn;
        let offset = parseInt(button.dataset.offset);
        let cid = button.dataset.cid;

        // Show loading spinner
        button.classList.add('loading');

        fetch("load_category_products.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `offset=${offset}&cid=${cid}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "no_more") {
                button.style.display = "none";
            } else {
                document.getElementById('product-container')
                        .insertAdjacentHTML('beforeend', data);
                button.dataset.offset = offset + 10;
            }
        })
        .finally(() => {
            button.classList.remove('loading');
            isLoading = false;
        });
    }

    // Infinite scroll with pre-fetch before button is visible
    window.addEventListener('scroll', function() {
        const rect = loadMoreBtn.getBoundingClientRect();
        const windowHeight = window.innerHeight || document.documentElement.clientHeight;

        // Trigger 200px before button enters viewport
        if (rect.top - windowHeight < 200) {
            if (loadMoreBtn.style.display !== "none") {
                loadProducts();
            }
        }
    });

    // Optional: first batch auto load on page load
    loadProducts();
});

</script>

            </div>
        </div>
    </div>
</div>

<!-- Floating Cart View -->
<?php include('includes/view-cart.php'); ?>


<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

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
$(document).ready(function() {
  $('.add-to-cart-btn').click(function(e) {
    e.preventDefault();
    var productId = $(this).data('id');

    $.ajax({
      url: 'add-to-cart.php',
      type: 'POST',
      data: { id: productId },
      success: function(res) {
        $('#cart-count').text(res.cartCount); // live update cart count
        $('#message-box').hide().html(res.message).fadeIn();

        setTimeout(function() {
          $('#message-box').fadeOut();
        }, 2000);
      },
      error: function() {
        alert('Something went wrong');
      }
    });
  });
});
</script>
  <script>
      $(document).ready(function() {
  $('.add-to-cart-btn').click(function(e) {
    e.preventDefault();
    var productId = $(this).data('id');

    $.ajax({
      url: 'add-to-cart.php',
      type: 'POST',
      data: { id: productId },
      success: function(res) {
        $('#cart-count').text(res.cartCount);
        $('#message-box').hide().text(res.message).fadeIn();
        setTimeout(() => $('#message-box').fadeOut(), 2000);
      },
      error: function() {
        alert("Something went wrong!");
      }
    });
  });
});
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
            );
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
        html += `<div style=\"display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #eee;\">`
            + `<div style=\"display:flex;align-items:center;gap:10px;\">`
            + (variant.variant_image ? `<img src=\"admin/productimages/${productId}/${variant.variant_image}\" style=\"width:38px;height:38px;object-fit:contain;border-radius:6px;\">` : '')
            + `<div>`
            + `<div style=\"font-size:15px;font-weight:600;\">${variant.variant_label}</div>`
            + `<div style=\"font-size:14px;\">`
            + (variant.discount_percent && variant.discount_percent > 0 ? `<span style='color:#2196f3;font-weight:600;font-size:13px;margin-right:6px;'>${variant.discount_percent}% OFF</span>` : '')
            + `<span style=\"color:#222;font-weight:700;\">₹${variant.price}</span>`
            + (variant.mrp && variant.mrp > variant.price ? `<span style='color:#888;font-size:13px;margin-left:6px;'><del>₹${variant.mrp}</del></span>` : '')
            + `</div></div></div>`
            
            + (isBuyNow ? `<button class=\"variant-buy-btn\" data-product-id=\"${productId}\" data-variant-id=\"${variant.id}\" style=\"background:#ff9f00;border:none;color:#fff;font-weight:700;padding:6px 18px;border-radius:20px;cursor:pointer;font-size:15px;margin-left:8px;\">BUY</button>` : '<button class=\"variant-add-btn\" data-product-id=\"${productId}\" data-variant-id=\"${variant.id}\" style=\"background:#fff;border:1.5px solid #28a745;color:#28a745;font-weight:700;padding:6px 18px;border-radius:20px;cursor:pointer;font-size:15px;\">ADD</button>')
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
<!-- Variant Bottom Drawer original

<style>
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
  #variantDrawerOverlay { display:none flex; }
}
</style>  -->

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
        z-index: 10000;
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
  const cartDiv = document.getElementById('floating-cart-beautiful');
  if (cartCount > 0) {
    cartDiv.innerHTML = `
      <div class="cart-info" style="background-color:red">
        <i class="fa fa-shopping-cart cart-icon"></i>
        <span>${cartCount} item${cartCount > 1 ? 's' : ''} added</span>
        <span class="cart-total">₹${cartTotal}</span>
      </div>
      <a href="my-cart.php" class="view-button">View Cart</a>
    `;
    cartDiv.style.display = 'flex';
    
    // Clear existing timeout
    if (floatingCartTimeout) {
      clearTimeout(floatingCartTimeout);
    }
    
   
  }
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
    }
  }).fail(function(xhr, status, error) {
    console.error('AJAX request failed:', error);
    showNotification('Failed to add product to cart. Please try again.', 'error');
  });
};
window.openVariantDrawer = function(btn) {
  var variants = $(btn).data('variants');
  if (typeof variants === 'string') variants = JSON.parse(variants);
  var productId = $(btn).data('product-id');
  var productName = $(btn).closest('.product-card').find('.product-name').text();
  var isBuy = $(btn).hasClass('buy-now-btn');
  if (variants.length > 1 && window.innerWidth <= 50000) //deskstop me khole ke liye 600 se 50000 kar diya hu 
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
<?php include 'includes/floating-btn.php'; ?>

<!-- Floating Cart Element -->

<?php include('includes/view-cart.php'); ?>
</style>
</body>
</html>

