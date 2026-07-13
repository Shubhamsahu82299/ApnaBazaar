<?php
// ======= side-menu.php =======
session_start();
include('includes/config.php');

$currentSubId = $_GET['scid'] ?? 0;
$currentCatId = $_GET['cid'] ?? 0;

if ($currentSubId && !$currentCatId) {
    $q = mysqli_query($con, "SELECT categoryid FROM subcategory WHERE id='$currentSubId'");
    $row = mysqli_fetch_array($q);
    $currentCatId = $row['categoryid'] ?? 0;
}
?>
<style>
body {
    background-color: #f8fafc;
    color: #1e293b;
}
</style>

<div class="category-bar-wrapper">
  <div class="category-bar-container">
    <div class="category-bar" id="categoryScroll">
      <?php 
      $sql = mysqli_query($con, "SELECT id, categoryName FROM category");
      while($row = mysqli_fetch_array($sql)) {
        $icon = match(strtolower($row['categoryName'])) {
          'electronics'      => 'fa-tv',
          'fashion'          => 'fa-tshirt',
          'mobiles'          => 'fa-mobile-alt',
          'laptops'          => 'fa-laptop',
          'books'            => 'fa-book',
          'furniture'        => 'fa-couch',
          'grocery'          => 'fa-shopping-basket',
          'sports'           => 'fa-futbol',
          'toys'             => 'fa-puzzle-piece',
          'beauty'           => 'fa-magic',
          'appliances'       => 'fa-blender',
          'kitchen'          => 'fa-utensils',
          'home_decor'       => 'fa-paint-roller',
          'stationery'       => 'fa-pencil-alt',
          'automobile'       => 'fa-car',
          'health'           => 'fa-heartbeat',
          'dry fruits'       => 'fa-heartbeat',
          'baby_products'    => 'fa-baby',
          'pet_supplies'     => 'fa-paw',
          'jewelry'          => 'fa-gem',
          'footwear'         => 'fa-shoe-prints',
          'music'            => 'fa-music',
          'gaming'           => 'fa-gamepad',
          'tools'            => 'fa-tools',
          'garden'           => 'fa-seedling',
          'bags_luggage'     => 'fa-suitcase-rolling',
          'clocks'           => 'fa-clock',
          'camera'           => 'fa-camera-retro',
          'smart_devices'    => 'fa-microchip',
          'daily needs'      => 'fa-broom',
          'travel'           => 'fa-plane',
          'industrial'       => 'fa-industry',
          'art_craft'        => 'fa-palette',
          'party_supplies'   => 'fa-glass-cheers',
          'alcohol'          => 'fa-wine-bottle',
          'medical'          => 'fa-stethoscope',
          'vegetables'       => 'fa-carrot',
          'fruits'           => 'fa-apple-alt',
          default            => 'fa-tag'
        };

        $isActive = ($row['id'] == $currentCatId) ? 'active-category' : '';
      ?>
        <a href="category.php?cid=<?php echo $row['id']; ?>" class="category-item <?= $isActive ?>" id="cat-<?php echo $row['id']; ?>">
          <i class="fa <?php echo $icon; ?>"></i>
          <span><?php echo htmlentities($row['categoryName']); ?></span>
        </a>
      <?php } ?>
    </div>
  </div>
</div>

<?php include('news-ticker.php') ?>

<?php
// ======= subcategory context matrix =======
$activeSubId = $_GET['scid'] ?? 0;
$categoryId = $_GET['cid'] ?? 0;

if (!$categoryId && $activeSubId) {
    $getCat = mysqli_query($con, "SELECT categoryid FROM subcategory WHERE id='$activeSubId'");
    $catRow = mysqli_fetch_array($getCat);
    $categoryId = $catRow['categoryid'] ?? 0;
}
?>

<style>
/* --- Compact Category Menu Layer --- */
.category-bar-wrapper {
  width: 100%;
  background-color: transparent;
  overflow-x: auto;
  padding-top: 4px; /* Reduced vertical footprint */
}

@media (max-width: 768px) {
  .category-bar-wrapper {
    padding-top: 2px;
  }
} 

.category-bar-container {
  background-color: transparent;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 12px;
}

.category-bar {
  background-color: transparent;
  display: flex;
  flex-wrap: nowrap;
  overflow-x: auto;
  padding: 4px 0; /* Minimized wrapper spacing */
  scroll-behavior: smooth;
  gap: 8px; /* Tighter item distribution */
  -webkit-overflow-scrolling: touch;
}

.category-bar::-webkit-scrollbar,
.subcategory-bar::-webkit-scrollbar {
  display: none;
}

@keyframes slideInCategory {
  0% {
    opacity: 0;
    transform: translateY(4px) scale(0.98);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.category-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 0 0 auto;
  text-decoration: none;
  padding: 6px 10px; /* Streamlined padding metrics */
  border-radius: 10px; /* Slimmer edges */
  min-width: 64px; /* Decreased widths */
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideInCategory 0.3s ease forwards;
  transform-origin: center;
}

.category-item i {
  font-size: 15px; /* Scaled down icons */
  margin-bottom: 3px; /* Reduced space */
  color: #64748b;
  transition: color 0.2s ease;
}

.category-item span {
  text-align: center;
  white-space: nowrap;
  font-size: 10px; /* Balanced down sizing rules */
  font-weight: 600;
  color: #475569;
  line-height: 1.1;
}

.category-item:hover {
  background-color: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.category-item:hover i {
  color: #0d9488;
}

/* Active Category State Overrides */
.category-item.active-category {
  background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
  border-color: transparent;
  box-shadow: 0 2px 8px rgba(13, 148, 136, 0.15);
}

.category-item.active-category i {
  color: #ffffff !important;
}

.category-item.active-category span {
  color: #ffffff !important;
  font-weight: 700;
}

/* --- Compact Subcategory Strip Layer --- */
.subcategory-bar-wrapper {
  width: 100%;
  background-color: transparent;
  margin-top: 4px; /* Trimmed layout height margins */
  padding-bottom: 2px;
}

@keyframes fadeInSubcategory {
  from { opacity: 0; transform: translateX(-6px); }
  to { opacity: 1; transform: translateX(0); }
}

.subcategory-bar {
  display: flex;
  overflow-x: auto;
  padding: 4px 12px; /* Denser horizontal distribution boundaries */
  gap: 6px;
  white-space: nowrap;
  animation: fadeInSubcategory 0.3s ease-out;
}

.subcategory-item {
  padding: 4px 12px; /* Slim configuration values */
  font-size: 12px; /* Scale text down safely */
  font-weight: 600;
  border-radius: 14px;
  text-decoration: none;
  color: #64748b;
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.subcategory-item:hover {
  background-color: #f1f5f9;
  color: #0d9488;
  border-color: #cbd5e1;
}

/* Active Subcategory State Overrides */
.subcategory-item.active-subcategory {
  background-color: #0f172a;
  color: #ffffff !important;
  border-color: #0f172a;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1);
}
</style>

<!-- Scroll Execution Logic Engines -->
<script>
window.addEventListener('DOMContentLoaded', function () {
  const activeCat = document.querySelector('.category-item.active-category');
  if (activeCat && activeCat.scrollIntoView) {
    activeCat.scrollIntoView({ inline: 'center', behavior: 'smooth', block: 'nearest' });
  }

  const activeSub = document.querySelector('.subcategory-item.active-subcategory');
  if (activeSub && activeSub.scrollIntoView) {
    activeSub.scrollIntoView({ inline: 'center', behavior: 'smooth', block: 'nearest' });
  }
});
</script>
