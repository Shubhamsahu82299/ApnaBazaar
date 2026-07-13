<?php
$cartItem = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$cartItemCount = max(0, $cartItem - 1);
?>

<?php if ($cartItemCount > 0): ?>
  <div id="floating-cart"
    style="position: fixed; bottom: 20px; right: 50%; transform: translateX(50%); background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 0.5rem 1rem; border-radius: 40px; z-index: 999; width: auto; min-width: 220px; border: 1px solid #ccc; display: flex; flex-direction: column; gap: 0.3rem;">

    <?php if (isset($_SESSION['message'])): ?>
      <div style="color: #28a745; font-size: 13px; display: flex; align-items: center;">
        <i class="fa fa-check-circle" style="margin-right: 6px;"></i>
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
      </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center;">
      <span style="font-weight: 600; font-size: 13px; color: #333;">
        <i class="fa fa-shopping-cart" style="margin-right: 6px; color: #0d6efd;"></i>
        Items: <?= $cartItemCount ?>
      </span>
      <a href="my-cart.php"
        style="background-color: #0d6efd; color: #fff; font-size: 12px; padding: 4px 12px; border-radius: 50px; text-decoration: none;">
        View
      </a>
    </div>

  </div>
<?php endif; ?>
