<?php
session_start();
include('includes/config.php'); // $con from config

// fetch categories
$categories = [];
$res = mysqli_query($con, "SELECT id, categoryName FROM category ORDER BY id ASC");
while($c = mysqli_fetch_assoc($res)){
    $cid = (int)$c['id'];

    // count products
    $cnt = 0;
    $resCnt = mysqli_query($con, "SELECT COUNT(*) AS c FROM products WHERE category=$cid");
    if($resCnt){
        $row = mysqli_fetch_assoc($resCnt);
        $cnt = $row['c'];
    }

    // 4 random images
    $imgs = [];
    $resImg = mysqli_query($con, "SELECT id, productImage1 FROM products 
                               WHERE category=$cid AND productImage1<>'' 
                               ORDER BY RAND() LIMIT 4");
    while($p = mysqli_fetch_assoc($resImg)){
        $db_img = $p['productImage1'];

        // ✨ DYNAMIC CLOUDINARY ENGINE RESOLUTION CHECK ✨
        // Agar database me link already 'https://res.cloudinary.com' se shuru ho raha hai:
        if (strpos($db_img, 'https://res.cloudinary.com') === 0) {
            $imgs[] = $db_img; // Direct network URL use karein
        } else {
            // Nahi toh puraana local file system path apply karein
         /*    $imgs[] = "admin/productimages/" . $p['id'] . "/" . $db_img; */
        }
    }
    
    // Fallback padding if target list has fewer than 4 items
    while(count($imgs) < 4){
        $imgs[] = "https://via.placeholder.com/300?text=Image";
    }

    $categories[] = [
        "id" => $cid,
        "name" => $c['categoryName'],
        "count" => $cnt,
        "images" => $imgs
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories</title>
<style>
body{margin:0; font-family:Arial, sans-serif; background:#f9fafb}

/* category grid layout components */
.cg-container{max-width:1200px; margin:0 auto; padding:0 8px 20px;}
.cg-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:8px}
@media(max-width:768px){.cg-grid{grid-template-columns:repeat(3,1fr)}}
.cg-card{position:relative; background: #f2f7c33d; border:1px solid #eee; border-radius:10px; padding:5px; cursor:pointer; transition:.2s}
.cg-card:hover{box-shadow:0 8px 20px rgba(0,0,0,.1); transform:translateY(-2px)}
.cg-thumbs{display:grid; grid-template-columns:repeat(2,1fr); gap:4px;}
.cg-thumb{width:100%; aspect-ratio:1/1; border-radius:8px; object-fit:cover; border:1px solid #eee; background-color:white;}
.cg-title{margin-top:10px; font-weight:700; font-size:15px; text-align:center}
.cg-pill{position:absolute; bottom:30px; left:18px; padding:2px 7px; font-size:12px; background: #f1f5f986; border:1px solid #ddd; border-radius:20px;}
a{text-decoration:none; color:inherit}
.cg-container h2{margin-bottom:15px; font-size:22px; font-weight:700; text-align:center}
.cg-banner{ text-align:center; border-bottom-left-radius:70% 40px; border-bottom-right-radius:70% 40px; margin-bottom:15px; margin-top:60px; padding:25px 15px 30px; overflow:hidden; }

/* 🌞 Day Theme */
.cg-banner.day{ background:linear-gradient(135deg,#fffde7,#fff59d); color:#000; box-shadow:0 3px 8px rgba(255,200,0,0.3); }
.cg-banner.day .emoji { display:inline-block; animation:sunPulse 2s infinite alternate; }

/* 🌙 Night Theme */
.cg-banner.night{ background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff; box-shadow:0 3px 8px rgba(0,0,50,0.3); }
.cg-banner.night .emoji { display:inline-block; animation:moonGlow 3s infinite alternate; }

/* Animations Keyframes */
@keyframes sunPulse{ from{transform: scale(1);} to{transform: scale(1.15);} }
@keyframes moonGlow{ from{opacity: 0.8;} to{opacity: 1;} }

.cg-banner h1{ margin:0; font-size:28px; font-weight:800; letter-spacing:0.5px; }
.cg-banner p{ margin:6px 0 0; font-size:14px; font-weight:500; opacity:0.9; }
@media(max-width:768px){.cg-banner{ margin-top:5px; }}
</style>
</head>
<body>

<!-- Welcome Dynamic Time Banner -->
<div class="cg-banner <?php
  $hour = date("H");
  if($hour >= 6 && $hour < 18){ echo 'day'; } else { echo 'night'; }
?>">
  <?php $emoji = ($hour >= 6 && $hour < 18) ? "🌞" : "🌙"; ?>
  <h1>WELCOME <span class="emoji"><?= $emoji ?></span></h1>
  <p>Order now & enjoy FREE delivery*</p>
</div>

<!-- Category Grid Container Components -->
<div class="cg-container">
   <h2>🔥 Bestsellers 🔥</h2>
  <div class="cg-grid">
    <?php foreach($categories as $c): ?>
      <a href="category.php?cid=<?= $c['id'] ?>">
        <div class="cg-card">
          <div class="cg-thumbs">
            <?php foreach($c['images'] as $img): ?>
              <!-- CSS template object-fit switched to cover for better responsive bounds grid alignment -->
              <img src="<?= $img ?>" class="cg-thumb" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
            <?php endforeach; ?>
          </div>
          <span class="cg-pill">+<?= $c['count'] ?> more</span>
          <div class="cg-title"><?= htmlspecialchars($c['name']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>