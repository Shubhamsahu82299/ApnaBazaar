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
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    border-radius: 10px;
    background: #fff;
    padding: 8px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
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

      <div class="price-wrapper">
        <span class="current-price">₹<?php echo htmlentities($row['productPrice']); ?></span>
        <small class="original-price"><del>₹<?php echo htmlentities($row['productPriceBeforeDiscount']); ?></del></small>
      </div>

      <?php if ($row['productAvailability'] == 'In Stock') { ?>
        <div class="action-buttons">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 0px; width: 100%;">

  <!-- Add to Cart Button -->
  <a href="javascript:void(0);" onclick="addToCart(<?= $row['id'] ?>, <?= $cid ?>)"
     style="flex: 1; background-color: #fb641b; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
    <i class="fa fa-shopping-cart" style="margin-right: 6px;"></i> Add
  </a>

  <!-- Wishlist Button -->
  <a href="category?pid=<?php echo htmlentities($row['id']); ?>&action=wishlist"
     style="width: 44px; height: 44px; min-width: 44px; background-color: #fff; color: #ff3f3f; font-size: 18px; border-radius: 50%; border: 1.5px solid #ff3f3f; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
    <i class="fa fa-heart"></i>
  </a>

  <!-- Buy Now Button -->
  <a href="index?page=product&action=buynow&id=<?php echo $row['id']; ?>"
     style="flex: 1; background-color: #ff9f00; color: #fff; font-size: 14px; font-weight: 600; padding: 5px 0; border-radius: 25px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
    <i class="fa fa-bolt" style="margin-right: 6px;"></i> Buy
  </a>

</div>

        </div>
      <?php } else { ?>
        <div class="out-of-stock">Out of Stock</div>
      <?php } ?>
    </div>
  </div>
</div>

</body>
</html>

