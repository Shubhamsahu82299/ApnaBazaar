<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sticky E-commerce Navbar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    .navbar {
      background-color: #2874f0;
    }

    .navbar-brand {
      font-size: 1.5rem;
      font-weight: bold;
      color: #fff;
    }

    .navbar-nav .nav-link {
      color: white;
      font-weight: 500;
    }

    .navbar-nav .nav-link:hover {
      text-decoration: underline;
    }

    .sticky-top {
      position: sticky;
      top: 0;
      z-index: 999;
    }

    .navbar-toggler {
      border-color: #fff;
    }

    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29)' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .icon-text {
      color: white;
      margin-left: 5px;
    }
  </style>
</head>
<body>
<!-- Sticky Navbar Start -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">ApnaBazaar</a>
    
    <!-- Toggle for Mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      
      <!-- Search Bar -->
      <form class="d-flex mx-auto my-2" role="search" action="search-results.php" method="GET" style="max-width: 500px; width: 100%;">
        <input class="form-control me-2" type="search" name="q" placeholder="Search for products, brands..." aria-label="Search">
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
      </form>
      
      <!-- Navigation Links -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i><span class="icon-text">Home</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="products.php"><i class="bi bi-box-seam"></i><span class="icon-text">Products</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="cart.php"><i class="bi bi-cart-fill"></i><span class="icon-text">Cart</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="wishlist.php"><i class="bi bi-heart-fill"></i><span class="icon-text">Wishlist</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="my-account.php"><i class="bi bi-person-circle"></i><span class="icon-text">My Account</span></a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- Sticky Navbar End -->
<!-- Sticky Navbar End -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
