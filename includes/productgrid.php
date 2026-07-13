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
    transition: 0.3s ease-in-out;
    position: relative;
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
.product-card {
  position: relative;
  overflow: hidden;
  border-radius: 16px;
  background-color: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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

/* Quantity Overlay Styles */
.quantity-overlay {
  transition: transform 0.1s ease-out, opacity 0.1s ease-out;
  animation: slideInFast 0.1s ease-out forwards;
  opacity: 0;
  transform: scale(0.7);
  backdrop-filter: blur(4px);
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 6px 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.quantity-overlay[style*="display: block"] {
  opacity: 1 !important;
  transform: scale(1) !important;
}

.quantity-overlay .qty-btn-overlay {
  transition: transform 0.1s ease-out, box-shadow 0.1s ease-out, background 0.1s ease-out;
  user-select: none;
  border-radius: 8px;
  padding: 6px 10px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #222;
  background: #f5f5f5;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quantity-overlay .qty-btn-overlay:hover {
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.quantity-overlay .qty-btn-overlay:active {
  transform: scale(0.95);
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.quantity-overlay .qty-btn-overlay.decrease:hover {
  background: #ffebee !important;
  color: #d32f2f !important;
}

.quantity-overlay .qty-btn-overlay.increase:hover {
  background: #ffe0b2 !important;
  color: #e65100 !important;
  box-shadow: 0 4px 12px rgba(251, 100, 27, 0.4);
}

@keyframes slideInFast {
  0% {
    opacity: 0;
    transform: scale(0.7);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Add button transition */
.add-to-cart-btn {
  transition: all 0.3s ease;
}

.add-to-cart-btn[style*="display: none"] {
  animation: fadeOut 0.3s ease-out;
}

@keyframes fadeOut {
  from {
    opacity: 1;
    transform: scale(1);
  }
  to {
    opacity: 0;
    transform: scale(0.8);
  }
}

.product-img {
  width: 100%;


  width: 100%;
    height: 150px;
    object-fit: contain;
    border-radius: 6px;
    display: block;
    margin: 0 auto;
}

.product-img.loaded {

  
  opacity: 1;
}

  </style>
</head>
<body>
<?php
if (!isset($row) || !is_array($row)) {
    echo '<!-- $row not set or not array -->';
    return;
}
if (!isset($con) || !$con) {
    echo '<!-- $con not set -->';
    return;
}
echo '<!-- Rendering product: '.htmlspecialchars($row['productName']).' -->';
$product_id = $row['id'];
$variant_sql = mysqli_query($con, "SELECT id, price, variant_label, stock FROM product_variants WHERE product_id = $product_id AND slot = '1' ORDER BY price ASC");
if ($variant_sql === false) {
    echo '<div style="color:red">SQL Error: ' . mysqli_error($con) . '</div>';
    return;
}
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
$variants_json = htmlspecialchars(json_encode($variants), ENT_QUOTES, 'UTF-8');
?>
<div class="product-box" style="height:310px">
  <div class="product-card">

    <!-- Quantity Controls Overlay -->
    <div class="quantity-overlay" id="qty-overlay-<?php echo $row['id']; ?>" 
         style="display: none; position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.55);
         border: 2px solid #fb641b; min-width: 80px;">
      <div style="display: flex; align-items: center; gap: 6px;">
        <button type="button" 
                class="qty-btn-overlay decrease" 
                onclick="updateProductQty(<?php echo $row['id']; ?>, -1)"
                style="background: #fff; border: 1px solid #fb641b; color: #fb641b; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; font-size: 14px; transition: all 0.2s ease;">
          −
        </button>
        <span class="qty-display-overlay" id="product-qty-overlay-<?php echo $row['id']; ?>" style="font-weight: 600; font-size: 12px; color: #333; min-width: 16px; text-align: center;">
          1
        </span>
        <button type="button" 
                class="qty-btn-overlay increase" 
                onclick="updateProductQty(<?php echo $row['id']; ?>, 1)"
                style="background: #fb641b; border: 1px solid #fb641b; color: #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; font-size: 14px; transition: all 0.2s ease;">
          +
        </button>
      </div>
    </div>
    
    <!-- Product Image (Lazy Loading Removed) -->
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
      <?php if (count($variants) > 0): ?>
        <div class="price-wrapper">
          <span class="current-price">From ₹<?php echo number_format($min_price, 2); ?></span>
        </div>
        <div class="variants" style="font-size:12px; color:#007bff; margin-bottom:4px;">
          Variants: <?php echo htmlspecialchars(implode(', ', $variant_names)); ?>
        </div>
     <?php else: ?>
  <?php if ($row['productAvailability'] == 'In Stock') { ?>
    <div class="price-wrapper">
      <span class="current-price">₹<?php echo htmlentities($row['productPrice']); ?></span>
      <small class="original-price"><del>₹<?php echo htmlentities($row['productPriceBeforeDiscount']); ?></del></small>
    </div>
  <?php } ?>
<?php endif; ?>

      
      <?php if ($row['productAvailability'] == 'In Stock') { ?>
        <div class="action-buttons">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 0px; width: 100%;">

            <?php if (count($variants) > 1): ?>
              <button type="button"
                class="open-variant-drawer-btn"
                data-product-id="<?php echo $row['id']; ?>"
                data-variant-id="<?php echo $min_variant_id ? $min_variant_id : ''; ?>"
                data-variants='<?php echo $variants_json; ?>'
                onclick="window.openVariantDrawer && window.openVariantDrawer(this)"
                style="flex: 1; background-color: #fb641b; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15); border: none; cursor: pointer;">
                <i class="fa fa-shopping-cart" style="margin-right: 6px;"></i>
                Add
              </button>
            <?php else: ?>
              <button type="button"
                class="add-to-cart-btn"
                data-product-id="<?php echo $row['id']; ?>"
                data-variant-id="<?php echo $min_variant_id ? $min_variant_id : ''; ?>"
                data-variants='<?php echo $variants_json; ?>'
                onclick="showQtyOverlay(<?php echo $row['id']; ?>)"
                style="flex: 1; background-color: #fb641b; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15); border: none; cursor: pointer;">
                <i class="fa fa-shopping-cart" style="margin-right: 6px;"></i>
                Add
              </button>
            <?php endif; ?>

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
    document.addEventListener("DOMContentLoaded", () => {
  const lazyImages = document.querySelectorAll("img.lazy-img");

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.onload = () => img.classList.add("loaded"); // blur hatao jab load ho jaye
        obs.unobserve(img);
      }
    });
  });

  lazyImages.forEach(img => observer.observe(img));
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking cart status...');
    
    // Check if products are already in cart and show quantity controls
    checkCartStatus();
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(function(button) {
        console.log('Found add-to-cart button:', button);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Add to cart clicked for product:', this.getAttribute('data-product-id'));
            
            if (this.classList.contains('btn-loading')) {
                return;
            }
            const productId = this.getAttribute('data-product-id');
            const variantId = this.getAttribute('data-variant-id');
            const originalText = this.innerHTML;
            this.classList.add('btn-loading');
            this.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right: 6px;"></i>Adding...';
            
            console.log('Sending request to add-to-cart with productId:', productId);
            
            fetch('add-to-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + productId + '&action=add&quantity=1' + (variantId ? '&variant_id=' + variantId : '')
            })
            .then(response => response.json())
            .then(data => {
                console.log('Add to cart response:', data);
                
                if (data.error) {
                    this.classList.remove('btn-loading');
                    this.innerHTML = originalText;
                    showNotification(data.error, 'error');
                    return;
                }
                this.classList.remove('btn-loading');
                
                                                        // Show quantity overlay on product card IMMEDIATELY (like test button)
            console.log('Showing quantity overlay for product:', productId);
            
            // Show overlay immediately without waiting for response
            setTimeout(() => {
                const qtyOverlay = document.getElementById('qty-overlay-' + productId);
                if (qtyOverlay) {
                    qtyOverlay.style.display = 'block';
                    qtyOverlay.style.opacity = '1';
                    qtyOverlay.style.transform = 'scale(1)';
                    
                    const qtyDisplay = document.getElementById('product-qty-overlay-' + productId);
                    if (qtyDisplay) {
                        qtyDisplay.textContent = '1';
                    }
                    console.log('Quantity overlay shown immediately');
                } else {
                    console.error('Quantity overlay not found for product:', productId);
                }
            }, 0.000001); // Small delay to ensure DOM is ready
            
            // Add success animation to Add button
            this.classList.add('btn-success');
            this.innerHTML = '<i class="fa fa-check" style="margin-right: 6px;"></i>Added!';
            setTimeout(() => {
                this.classList.remove('btn-success');
                this.innerHTML = originalText;
            }, 1500);
                
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cartCount;
                }
                showNotification(data.message || 'Product added to cart successfully!', 'success');
                updateFloatingCart(data.cartCount, data.cartTotal);
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                this.classList.remove('btn-loading');
                this.innerHTML = originalText;
                showNotification('Product added to cart successfully!', 'success');
            });
        });
    });
});

// Function to check cart status and show quantity controls for products already in cart
function checkCartStatus() {
    console.log('Checking cart status...');
    fetch('?ajax=cart')
        .then(res => res.text())
        .then(data => {
            console.log('Cart data received:', data);
            // Parse cart data to find which products are in cart
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const cartItems = doc.querySelectorAll('.item');
            console.log('Cart items found:', cartItems.length);
            
            cartItems.forEach(item => {
                const qtyDisplay = item.querySelector('.qty-display');
                if (qtyDisplay) {
                    const productId = qtyDisplay.id.replace('qty-', '');
                    const quantity = qtyDisplay.textContent;
                    console.log('Product in cart:', productId, 'Quantity:', quantity);
                    
                    // Show quantity overlay for this product
                    const qtyOverlay = document.getElementById('qty-overlay-' + productId);
                    
                    console.log('Quantity overlay found:', qtyOverlay);
                    
                    if (qtyOverlay) {
                        qtyOverlay.style.display = 'block';
                        qtyOverlay.style.opacity = '1';
                        qtyOverlay.style.transform = 'scale(1)';
                        
                        const productQtyDisplay = document.getElementById('product-qty-overlay-' + productId);
                        if (productQtyDisplay) {
                            productQtyDisplay.textContent = quantity;
                        }
                        console.log('Quantity overlay shown for product:', productId);
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error checking cart status:', error);
        });
}

// Function to update product quantity from product grid
function updateProductQty(productId, change) {
    console.log('Updating product quantity:', productId, 'Change:', change);
    
    fetch('update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${productId}&change=${change}`
    })
    .then(res => res.json())
    .then(data => {
        console.log('Update quantity response:', data);
        
        if (data.success) {
            const qtyDisplay = document.getElementById('product-qty-overlay-' + productId);
            if (qtyDisplay) {
                qtyDisplay.textContent = data.newQty;
            }
            
            // If quantity becomes 0, hide quantity overlay
            if (data.newQty <= 0) {
                const qtyOverlay = document.getElementById('qty-overlay-' + productId);
                
                if (qtyOverlay) {
                    qtyOverlay.style.display = 'none';
                }
            }
            
            // Update cart count in header
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                // Fetch updated cart count
                fetch('?ajax=cart')
                    .then(res => res.text())
                    .then(cartData => {
                        const parser = new DOMParser();
                        const cartDoc = parser.parseFromString(cartData, 'text/html');
                        const cartItems = cartDoc.querySelectorAll('.item');
                        let totalQty = 0;
                        cartItems.forEach(item => {
                            const qty = item.querySelector('.qty-display');
                            if (qty && qty.textContent) {
                                totalQty += parseInt(qty.textContent) || 0;
                            }
                        });
                        cartCountElement.textContent = totalQty;
                    });
            }
            
            showNotification('Cart updated successfully!', 'success');
        } else {
            showNotification(data.message || 'Something went wrong.', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        showNotification('Failed to update cart. Please try again.', 'error');
    });
}

// Test function to manually show quantity overlay
function testShowQuantityControls(productId) {
    console.log('Testing quantity overlay for product:', productId);
    const qtyOverlay = document.getElementById('qty-overlay-' + productId);
    
    if (qtyOverlay) {
        qtyOverlay.style.display = 'block';
        qtyOverlay.style.opacity = '1';
        qtyOverlay.style.transform = 'scale(1)';
        console.log('Test: Quantity overlay shown');
    } else {
        console.log('Test: Quantity overlay element not found');
    }
}

// Function to immediately show quantity overlay (for testing)
function showQuantityOverlayNow(productId) {
    console.log('Immediately showing quantity overlay for product:', productId);
    const qtyOverlay = document.getElementById('qty-overlay-' + productId);
    
    if (qtyOverlay) {
        qtyOverlay.style.display = 'block';
        qtyOverlay.style.opacity = '1';
        qtyOverlay.style.transform = 'scale(1)';
        
        const qtyDisplay = document.getElementById('product-qty-overlay-' + productId);
        if (qtyDisplay) {
            qtyDisplay.textContent = '1';
        }
        console.log('Quantity overlay shown immediately');
    } else {
        console.error('Quantity overlay not found for product:', productId);
    }
}

// Simple function to show quantity overlay
function showQtyOverlay(productId) {
    console.log('showQtyOverlay called for product:', productId);
    
    // Show overlay immediately
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
        
        // Add to cart via AJAX
        console.log('Sending AJAX request to add-to-cart.php');
        fetch('add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + productId + '&action=add&quantity=1'
        })
        .then(response => {
            console.log('Raw response status:', response.status);
            console.log('Raw response ok:', response.ok);
            return response.json();
        })
        .then(data => {
            console.log('Add to cart response:', data);
            if (data.success) {
                // Update cart count
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cartCount;
                }
                console.log('Cart count updated to:', data.cartCount);
            } else {
                console.error('Add to cart failed:', data.error);
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
        });
    } else {
        console.error('Quantity overlay not found for product:', productId);
    }
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
let floatingCartTimeout = null;
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
    // Auto-hide after 4 seconds
    
}
   function updateQty(productId, change) {
  fetch('update-cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `id=${productId}&change=${change}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById(`qty-${productId}`).innerText = data.newQty;
      loadCartDropdown(); // Refresh cart to show updated price
    } else {
      alert(data.message || 'Something went wrong.');
    }
  });
}
</script>

</body>
</html>

