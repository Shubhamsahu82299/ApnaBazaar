<?php
session_start();
include('includes/config.php'); // $con from config

// fetch categories
$categories = [];
$res = mysqli_query($con, "
    SELECT s.id, s.subcategory, s.categoryid, c.categoryName 
    FROM subcategory s
    JOIN category c ON c.id = s.categoryid
    ORDER BY s.id ASC
");

while ($c = mysqli_fetch_assoc($res)) {
    $cid = (int)$c['id'];

    // count products
    $cnt = 0;
    $resCnt = mysqli_query($con, "SELECT COUNT(*) AS c FROM products WHERE subcategory=$cid");
    if ($resCnt) {
        $row = mysqli_fetch_assoc($resCnt);
        $cnt = $row['c'];
    }

    // 4 random images
    $imgs = [];
    $resImg = mysqli_query($con, "SELECT id, productImage1 
                                  FROM products 
                                  WHERE subcategory=$cid AND productImage1<>'' 
                                  ORDER BY RAND() LIMIT 4");
    while ($p = mysqli_fetch_assoc($resImg)) {
        $imgs[] = "admin/productimages/" . $p['id'] . "/" . $p['productImage1'];
    }
    while (count($imgs) < 4) {
        $imgs[] = "https://via.placeholder.com/300?text=Image";
    }

    $categories[] = [
        "id"       => $cid,
        "name"     => $c['subcategory'],
        "category" => $c['categoryName'],  // 👈 yaha category name bhi aa gaya
        "count"    => $cnt,
        "images"   => $imgs
    ];
}
$grouped = [];
foreach ($categories as $c) {
    $catName = $c['category'];
    if (!isset($grouped[$catName])) {
        $grouped[$catName] = [];
    }
    $grouped[$catName][] = $c;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories</title>
<style>
body{margin:0;font-family:Arial, sans-serif;background:#f9fafb}

/* category grid */
.cat-container{max-width:1200px;margin:0 auto;padding:0 8px 20px 0; }
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px}
@media(max-width:768px){.cat-grid{grid-template-columns:repeat(3,1fr)}}
.cat-card{position:relative;background: #f2f7c33d;border:1px solid #eee;border-radius:10px;padding:5px;cursor:pointer;transition:.2s}
.cat-card:hover{box-shadow:0 8px 20px rgba(0,0,0,.1);transform:translateY(-2px)}
.cat-thumbs{display:grid;grid-template-columns:repeat(2,1fr);gap:4px;}
.cat-thumb{width:100%;aspect-ratio:1/1;border-radius:8px;object-fit:contain;border:1px solid #eee;background-color:white;}
.cat-title{margin-top:10px;font-weight:700;font-size:15px;text-align:center}
.cat-pill{position:absolute;bottom:30px;left:18px;padding:2px 7px;font-size:12px;background: #f1f5f986;border:1px solid #ddd;border-radius:20px;}
a{text-decoration:none;color:inherit}
.cat-container h2{margin-bottom:15px;font-size:22px;font-weight:700;text-align:center}
.cat-pill {
  position: absolute;
  bottom: 30px;       /* base bottom position */
  left: 18px;
  padding: 2px 7px;
  font-size: 12px;
  background: #f1f5f986;
  border: 1px solid #ddd;
  border-radius: 20px;
  transform: translateY(-15px); /* shift upward by 15px */
}

</style>
</head>
<body>

<!-- Categories -->
<div class="cat-container" >
  <?php foreach($grouped as $catName => $subs): ?>
    <h2 class="cat-category-title"><?= htmlspecialchars($catName) ?></h2>
    <div class="cat-grid" >
      <?php foreach($subs as $c): ?>
        <a href="sub-category.php?scid=<?= $c['id'] ?>" style="text-decoration:none;color:black !important;">
          <div class="cat-card" >
            <div class="cat-thumbs">
              <?php foreach($c['images'] as $img): ?>
                <img src="<?= $img ?>" class="cat-thumb" 
                     onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
              <?php endforeach; ?>
            </div>
            <span class="cat-pill">+<?= $c['count'] ?> more</span>
            <div class="cat-title"><?= htmlspecialchars($c['name']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>


</body>
</html>
<script>
function setEqualCardHeightPerCategory() {
  // sab category grids select karo
  const grids = document.querySelectorAll('.cat-grid');

  grids.forEach(grid => {
    const cards = grid.querySelectorAll('.cat-card');
    let maxHeight = 0;

    // pehle sab card ki height reset
    cards.forEach(card => card.style.height = 'auto');

    // max height calculate karo
    cards.forEach(card => {
      const h = card.offsetHeight;
      if(h > maxHeight) maxHeight = h;
    });

    // sab cards ko max height set karo
    cards.forEach(card => card.style.height = maxHeight + 'px');
  });
}

// run on page load
window.addEventListener('load', setEqualCardHeightPerCategory);
// run on window resize
window.addEventListener('resize', setEqualCardHeightPerCategory);
</script>
