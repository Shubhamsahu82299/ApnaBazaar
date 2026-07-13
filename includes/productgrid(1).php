<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Card</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/font-awesome.min.css">
  <style>
    .product-box {
        margin-top:5px;
    padding: 8px;
    box-sizing: border-box;
    float: left;
    width: 50%; /* Mobile view: 2 per row */
  }

  @media (min-width: 992px) {
    .product-box {
      width: 20%; /* Desktop view: 5 per row */
    }
  }

  .product-card {
    box-shadow: 0 0 8px  rgba(101, 188, 212, 0.53);
    border-radius: 10px;
    background: #fff;
    padding: 8px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    min-height: 340px; /* Ensures all cards are the same height */
    transition: 0.3s ease-in-out;
  }

  .product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  }

  .product-image {
    width: 100%;
    height: 150px;
    object-fit: contain;
    border-radius: 6px;
    display: block;
    margin: 0 auto;
  }

  .product-name {
    font-weight: 600;
    font-size: 14px;
    color: #000;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .price-wrapper {
    margin-top: 4px;
  }

  .current-price {
    font-weight: bold;
    color: #ff3f3f;
  }

  .original-price {
    color: #999;
    margin-left: 4px;
  }

  .action-buttons {
    display: flex;
    gap: 6px;
    margin-top: 8px;
  }

  .btn-add {
    flex: 1;
    background-color: #ff3f3f;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 0;
    text-align: center;
    border-radius: 20px;
    text-decoration: none;
  }

  .btn-wishlist {
    flex: 1;
    background-color: #f8f9fa;
    color: #000;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 0;
    text-align: center;
    border-radius: 20px;
    border: 1px solid #ddd;
    text-decoration: none;
  }

  .btn-wishlist i {
    color: red;
  }

  .out-of-stock {
    display: block;
    background: #dc3545;
    color: #fff;
    padding: 6px 0;
    border-radius: 4px;
    text-align: center;
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
  }

    /* Responsive grid: 2 per row on small, 5 per row on XL */
    .col-xl-2-4 {
      flex: 0 0 20%;
      max-width: 20%;
    }


      @media (min-width: 992px) {
    .product-box {
      width: 16.66% !important; /* 6 per row on large screens */
    }
  }

.product-box {
  padding: 8px;
  box-sizing: border-box;
  float: left;
  width: 50%; /* Mobile view - 2 per row */
}

@media (min-width: 992px) {
  .product-box {
    width: 20%; /* Desktop view - 5 per row */
  }
}

/* Loading state styles */
.btn-loading {
  opacity: 0.7;
  pointer-events: none;
}

.btn-loading .fa-spinner {
  display: inline-block !important;
}

.btn-loading .fa-shopping-cart {
  display: none !important;
}

.fa-spinner {
  display: none;
}

/* Success animation */
.btn-success {
  background-color: #28a745 !important;
  animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.product-card {
  position: relative;
  overflow: hidden;
  border-radius: 16px;
  background-color: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.product-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -75%;
  width: 50%;
  height: 100%;
  background: linear-gradient(
    120deg,
    transparent 0%,
    rgba(255, 255, 255, 0.4) 50%,
    transparent 100%
  );
  transform: skewX(-25deg);
  animation: shimmer 2.5s infinite;
}

@keyframes shimmer {
  0% {
    left: -75%;
  }
  100% {
    left: 125%;
  }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}
@keyframes fadeSlideUp {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.product-box {
  animation: fadeSlideUp 0.5s ease-out;
  animation-fill-mode: both;
}
.product-card {
  position: relative;
  overflow: hidden;
}

.product-card::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(120deg, transparent, rgba(255,255,255,0.2), transparent);
  transform: rotate(25deg);
  animation: shine 3s infinite;
}

@keyframes shine {
  0% { transform: translateX(-100%) rotate(25deg); }
  100% { transform: translateX(100%) rotate(25deg); }
}

/* Staggered effect (optional) */
.product-box:nth-child(1) { animation-delay: 0.1s; }
.product-box:nth-child(2) { animation-delay: 0.2s; }
.product-box:nth-child(3) { animation-delay: 0.3s; }

/* Enhanced hover effect */
.product-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.product-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

  </style>
</head>
<body>
<div class="product-box">
  <div class="product-card">
    <a href="product-details?pid=<?php echo $row['id']; ?>">
     <img 
    src="<?php echo getProductImage($row['id'], $row['productImage1']); ?>" 
    alt="<?php echo htmlentities($row['productName']); ?>"
    class="product-img">
    </a>

    <div style="margin-top: 8px;">
      <div class="product-name" title="<?php echo htmlentities($row['productName']); ?>">
        <?php echo htmlentities($row['productName']); ?>
      </div>
      <?php
      $product_id = $row['id'];
      $variant_sql = mysqli_query($con, "SELECT id, price, variant_label FROM product_variants WHERE product_id = $product_id ORDER BY price ASC");
      $variants = [];
      $min_price = null;
      $min_variant_id = null;
      $variant_names = [];
      while ($v = mysqli_fetch_assoc($variant_sql)) {
          $variants[] = $v;
          $variant_names[] = $v['variant_label'];
          if ($min_price === null || $v['price'] < $min_price) {
              $min_price = $v['price'];
              $min_variant_id = $v['id'];
          }
      }
      $variants_json = json_encode($variants);
      ?>
      <?php if (count($variants) > 0): ?>
        <div class="price-wrapper">
          <span class="current-price">From ₹<?php echo number_format($min_price, 2); ?></span>
        </div>
        <div class="variants" style="font-size:12px; color:#007bff; margin-bottom:4px; min-height:18px;">
          Variants: <?php echo htmlspecialchars(implode(', ', $variant_names)); ?>
        </div>
      <?php else: ?>
        <div class="price-wrapper">
          <span class="current-price">₹<?php echo htmlentities($row['productPrice']); ?></span>
          <small class="original-price"><del>₹<?php echo htmlentities($row['productPriceBeforeDiscount']); ?></del></small>
        </div>
        <div class="variants" style="min-height:18px; margin-bottom:4px;"></div>
      <?php endif; ?>
      <?php if ($row['productAvailability'] == 'In Stock') { ?>
        <div class="action-buttons">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 0px; width: 100%;">

  <!-- Add to Cart Button - Now using AJAX -->
  <button type="button" 
          class="add-to-cart-btn <?php echo count($variants) > 1 ? 'variant-trigger-btn' : ''; ?>"
          data-product-id="<?php echo $row['id']; ?>"
          data-variant-id="<?php echo $min_variant_id ? $min_variant_id : ''; ?>"
          data-variants='<?php echo $variants_json; ?>'
          style="flex: 1; background-color: #fb641b; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15); border: none; cursor: pointer;">
    <i class="fa fa-shopping-cart" style="margin-right: 6px;"></i>
    <i class="fa fa-spinner fa-spin"></i>
    Add
  </button>

  <!-- Wishlist Button -->


  <!-- Buy Now Button -->
  <?php if (count($variants) > 1): ?>
    <a href="#" class="buy-now-btn variant-trigger-btn"
       data-product-id="<?php echo $row['id']; ?>"
       data-variant-id="<?php echo $min_variant_id ? $min_variant_id : ''; ?>"
       data-variants='<?php echo $variants_json; ?>'
       style="flex: 1; background-color: #ff9f00; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
      <i class="fa fa-bolt" style="margin-right: 6px;"></i> Buy
    </a>
  <?php else: ?>
    <a href="index?action=buynow&id=<?php echo $row['id']; ?><?php echo $min_variant_id ? '&variant_id=' . $min_variant_id : ''; ?>"
       class="buy-now-btn"
       style="flex: 1; background-color: #ff9f00; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
      <i class="fa fa-bolt" style="margin-right: 6px;"></i> Buy
    </a>
  <?php endif; ?>

</div>

        </div>
      <?php } else { ?>
        <div class="out-of-stock">Out of Stock</div>
      <?php } ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if this is a variant trigger button
            if (this.classList.contains('variant-trigger-btn')) {
                // Open variant drawer for mobile
                if (window.innerWidth <= 600) {
                    const variants = JSON.parse(this.getAttribute('data-variants'));
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.closest('.product-card').querySelector('.product-name').textContent;
                    window.showVariantDrawer(productId, variants, productName, false);
                } else {
                    // For desktop, add directly with first variant
                    addProductToCart(this, this.getAttribute('data-variant-id'));
                }
            } else {
                // Non-variant product, add directly
                addProductToCart(this, this.getAttribute('data-variant-id'));
            }
        });
    });
    
    // Handle buy now buttons
    document.querySelectorAll('.buy-now-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            // Check if this is a variant trigger button
            if (this.classList.contains('variant-trigger-btn')) {
                e.preventDefault();
                // Open variant drawer for mobile
                if (window.innerWidth <= 600) {
                    const variants = JSON.parse(this.getAttribute('data-variants'));
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.closest('.product-card').querySelector('.product-name').textContent;
                    window.showVariantDrawer(productId, variants, productName, true);
                } else {
                    // For desktop, redirect to buy with first variant
                    const productId = this.getAttribute('data-product-id');
                    const variantId = this.getAttribute('data-variant-id');
                    window.location.href = 'index?action=buynow&id=' + productId + '&variant_id=' + variantId;
                }
            }
            // Non-variant buy buttons will work normally via href (no preventDefault)
        });
    });
});

function addProductToCart(button, variantId) {
    if (button.classList.contains('btn-loading')) {
        return;
    }
    
    const productId = button.getAttribute('data-product-id');
    const originalText = button.innerHTML;
    button.classList.add('btn-loading');
    button.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right: 6px;"></i>Adding...';
    
    fetch('add-to-cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + productId + '&action=add&quantity=1' + (variantId ? '&variant_id=' + variantId : '')
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            button.classList.remove('btn-loading');
            button.innerHTML = originalText;
            showNotification(data.error, 'error');
            return;
        }
        button.classList.remove('btn-loading');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="fa fa-check" style="margin-right: 6px;"></i>Added!';
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = data.cartCount;
        }
        setTimeout(() => {
            button.classList.remove('btn-success');
            button.innerHTML = originalText;
        }, 2000);
        showNotification(data.message || 'Product added to cart successfully!', 'success');
        updateFloatingCart(data.cartCount, data.cartTotal);
    })
    .catch(error => {
        console.error('Error:', error);
        button.classList.remove('btn-loading');
        button.innerHTML = originalText;
        showNotification('Failed to add product to cart. Please try again.', 'error');
    });
}

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
</script>

<div id="floating-cart-beautiful" style="display:none"></div>
<style>
  #floating-cart-beautiful {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    padding: 14px 24px;
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(200, 200, 200, 0.3);
    border-radius: 50px;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.15);
    font-family: 'Segoe UI', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: #333;
    z-index: 9999;
    animation: slideFadeUp 0.6s ease-out;
    min-width: 280px;
    max-width: 95vw;
  }
  .cart-info {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .cart-icon {
    font-size: 20px;
    color: #0d6efd;
  }
  .cart-total {
    font-size: 15px;
    color: #185a9d;
    margin-left: 10px;
    font-weight: 700;
  }
  .view-button {
    background: linear-gradient(135deg, #43cea2, #185a9d);
    color: white;
    padding: 7px 22px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: bold;
    font-size: 15px;
    transition: all 0.3s ease;
    box-shadow: 0 3px 8px rgba(24, 90, 157, 0.25);
    margin-left: 18px;
  }
  .view-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 12px rgba(24, 90, 157, 0.4);
  }
  @keyframes slideFadeUp {
    0% { transform: translate(-50%, 40px); opacity: 0; }
    100% { transform: translate(-50%, 0); opacity: 1; }
  }
  @media (max-width: 768px) {
    #floating-cart-beautiful {
      width: 96vw;
      padding: 10px 8px;
      font-size: 13px;
      flex-wrap: wrap;
      gap: 8px;
      min-width: 0;
    }
    .view-button {
      font-size: 13px;
      padding: 6px 12px;
      margin-left: 8px;
    }
    .cart-total {
      font-size: 13px;
      margin-left: 6px;
    }
  }
</style>
<script>
function updateFloatingCart(cartCount, cartTotal) {
  const cartDiv = document.getElementById('floating-cart-beautiful');
  if (cartCount > 0) {
    cartDiv.innerHTML = `
      <div class="cart-info">
        <i class="fa fa-shopping-cart cart-icon"></i>
        <span>${cartCount} item${cartCount > 1 ? 's' : ''} added</span>
        <span class="cart-total">₹${cartTotal}</span>
      </div>
      <a href="my-cart" class="view-button">View Cart</a>
    `;
    cartDiv.style.display = 'flex';
    cartDiv.style.opacity = '1';
    // No auto-hide
  } else {
    cartDiv.style.display = 'none';
  }
}
</script>

</body>
</html>

