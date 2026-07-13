<div id="floating-cart-blinkit" style="display:none"></div>
<style>
  #floating-cart-blinkit{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    background: white;
    color: #222;
    font-family: 'Segoe UI', 'Roboto', sans-serif;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 18px;
    min-height: 56px;
    z-index: 9999;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
 box-shadow: 0 1px 3px rgba(101, 188, 212, 0.53);
    
    letter-spacing: 0.01em;
    border: 1px solid #e0e0e0;
    transition: box-shadow 0.2s;
  }
  #floating-cart-blinkit .cart-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    padding: 10px 0;
  }
  #floating-cart-blinkit .cart-items {
    font-size: 15px;
    font-weight: 600;
    color: #222;
  }
  #floating-cart-blinkit .cart-total {
    font-size: 14px;
    font-weight: 400;
    color: #555;
  }
  #floating-cart-blinkit .view-cart-btn {
    background: #333;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 22px;
    padding: 10px 28px;
    margin-left: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    cursor: pointer;
    transition: background 0.2s, color 0.2s, transform 0.1s;
    outline: none;
  }
  #floating-cart-blinkit .view-cart-btn:active {
    background: #555;
    color: #fff;
    transform: scale(0.97);
  }
  @media (max-width: 600px) {
    #floating-cart-blinkit {
      font-size: 15px;
      min-height: 52px;
      padding: 0 8px;
      border-top-left-radius: 14px;
      border-top-right-radius: 14px;
    }
    #floating-cart-blinkit .view-cart-btn {
      font-size: 15px;
      padding: 4px 15px;
      margin-left: 8px;
    }
    #floating-cart-blinkit .cart-info {
      padding: 7px 0;
    }
  }
  #floating-cart-blinkit {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    /* More yellow gradient */
    background: linear-gradient(to right, #fffce6, #ffef99, #ffd633);
    color: #222;
    font-family: 'Segoe UI', 'Roboto', sans-serif;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 18px;
    min-height: 54px;
    z-index: 9999;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 215, 0, 0.4);
    backdrop-filter: blur(6px);
    animation: cartGlow 4s infinite ease-in-out;
}

/* Golden subtle glow */
@keyframes cartGlow {
  0%   { box-shadow: 0 0 6px rgba(255,215,0,0.4); }
  50%  { box-shadow: 0 0 16px rgba(255,215,0,0.8), 0 0 30px rgba(255,215,0,0.5); }
  100% { box-shadow: 0 0 6px rgba(255,215,0,0.4); }
}

#floating-cart-blinkit .cart-items {
    font-size: 15px;
    font-weight: 700;
    color: #111;
}

#floating-cart-blinkit .cart-total {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

/* Sleek GREEN BUTTON */
#floating-cart-blinkit .view-cart-btn {
    background: linear-gradient(to right, #28a745, #34d058);
    color: #fff;
    font-weight: 700;
    font-size: 14px;   /* smaller */
    border: none;
    border-radius: 18px;  /* sleek */
    padding: 8px 20px;    /* slim */
    margin-left: 12px;
    cursor: pointer;
    outline: none;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}

/* Shine effect */
#floating-cart-blinkit .view-cart-btn::after {
    content: "";
    position: absolute;
    top: 0;
    left: -75%;
    width: 50%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.7), transparent);
    transform: skewX(-25deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { left: -75%; }
    50% { left: 125%; }
    100% { left: 125%; }
}

#floating-cart-blinkit .view-cart-btn:hover {
    transform: translateY(-2px);
    background: linear-gradient(to right, #218838, #28a745);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}


</style>
<script>
function updateFloatingCart(cartCount, cartTotal) {
  const cartDiv = document.getElementById('floating-cart-blinkit');

  if (cartCount > 0) {
    cartDiv.innerHTML = `
      <div class="cart-info">
        <span class="cart-items">${cartCount} item${cartCount > 1 ? 's' : ''} in cart</span>
        <span class="cart-total">Total: ₹${cartTotal}</span>
      </div>
      <a href="my-cart" class="view-cart-btn">View Cart</a>
    `;
    cartDiv.style.display = 'flex';

    // Auto-hide after 5 seconds
    setTimeout(() => {
      cartDiv.style.display = 'none';
    },6000); // 5000 milliseconds = 5 seconds
  } else {
    cartDiv.style.display = 'none';
  }
}
</script>

<script>
  // These values come from PHP session
  var initialCartCount = <?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>;
  var initialCartTotal = <?php
    $total = 0;
    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $item) $total += $item['quantity'] * $item['price'];
    }
    echo $total;
  ?>;
  // Show the floating cart bar if there are items
  if (initialCartCount > 0) {
    updateFloatingCart(initialCartCount, initialCartTotal);
  }
</script>