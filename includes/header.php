<!-- Bootstrap CSS and Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Navigation -->
ewgrf
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container px-4 px-lg-5">
    <a class="navbar-brand fw-bold text-primary" href="index.php">ApnaBazaar SHOPPING PORTAL</a>

    <!-- Mobile Menu Button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navigation Items -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about-us.php">About</a></li>

        <!-- Shop Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" id="shopDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Shop
          </a>
          <ul class="dropdown-menu" aria-labelledby="shopDropdown">
            <li><a class="dropdown-item" href="index.php">All Products</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item" href="shop-categories.php">Category Wise</a></li>
          </ul>
        </li>

        <?php if ($_SESSION['id'] == 0) { ?>
        <!-- Guest Users -->
     
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Users
          </a>
          <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="login.php">Login</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item" href="signup.php">Sign Up</a></li>
    
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>
        <?php } else { ?>
        <!-- Logged In Users -->
        <li class="nav-item"><a class="nav-link" href="my-wishlist.php">My Wishlist</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" id="accountDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            My Account
          </a>
          <ul class="dropdown-menu" aria-labelledby="accountDropdown">
            <li><a class="dropdown-item" href="my-orders.php">Orders</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item" href="my-profile.php">Profile</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item" href="change-password.php">Change Password</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item" href="manage-addresses.php">Addresses</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </li>
        <?php } ?>

        <li class="nav-item"><a class="nav-link" href="contact-us.php">Contact Us</a></li>
      </ul>

      <!-- Welcome Message -->
      <?php if ($_SESSION['id'] != 0): ?>
      <span class="navbar-text me-3 text-primary fw-semibold">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
      </span>
      <?php endif; ?>

      <!-- Cart Button -->
      <?php
      $uid = $_SESSION['id'];
      $ret = mysqli_query($con, "SELECT SUM(productQty) AS qtyy FROM cart WHERE userId='$uid'");
      $result = mysqli_fetch_array($ret);
      $cartcount = $result['qtyy'] ?? 0;
      ?>
      <a class="btn btn-outline-dark position-relative" href="my-cart.php">
        <i class="bi bi-cart-fill me-1"></i> Cart
        <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartcount; ?></span>
      </a>
    </div>
  </div>
</nav>
