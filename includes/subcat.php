<!-- Horizontal Category Bar (Responsive & Centered) -->
<div class="category-bar-wrapper">
  <div class="category-bar-container">
    <div class="category-bar" id="categoryScroll">
      <?php 
      $sql = mysqli_query($con, "SELECT id, categoryName FROM category");
      while($row = mysqli_fetch_array($sql)) {
        $icon = match(strtolower($row['categoryName'])) {
          'electronics' => 'fa-tv',
          'fashion'     => 'fa-tshirt',
          'mobiles'     => 'fa-mobile-alt',
          'books'       => 'fa-book',
          'furniture'   => 'fa-couch',
          'grocery'     => 'fa-shopping-basket',
          'sports'      => 'fa-futbol',
          default       => 'fa-tag'
        };
      ?>
        <a href="category.php?cid=<?php echo $row['id']; ?>" class="category-item">
          <i class="fa <?php echo $icon; ?>"></i>
          <span><?php echo htmlentities($row['categoryName']); ?></span>
        </a>
      <?php } ?>
    </div>
  </div>
</div>
<!-- CSS -->
<style>
.category-bar-wrapper {
  width: 100%;
  background-color: #fff;
  border-bottom: 1px solid #ddd;
  overflow-x: auto;
}

.category-bar-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 10px;
}

.category-bar {
  display: flex;
  flex-wrap: nowrap;
  overflow-x: auto;
  padding: 6px 0;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.category-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 0 0 auto;
  text-decoration: none;
  color: #333;
  font-size: 11px;
  padding: 4px 8px;
  margin-right: 12px;
  border-radius: 6px;
  min-width: 50px;
  transition: background-color 0.2s;
}

.category-item i {
  font-size: 16px;
  margin-bottom: 2px;
  color: #444;
}

.category-item span {
  text-align: center;
  white-space: nowrap;
  font-weight: 500;
  line-height: 1;
}

.category-item:hover {
  background-color: #f2f2f2;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .category-bar-container {
    padding: 0 6px;
  }

  .category-item {
    font-size: 10.5px;
    padding: 3px 6px;
  }

  .category-item i {
    font-size: 15px;
  }
}
</style>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
