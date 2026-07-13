<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('includes/config.php');
// Suppress all errors, warnings, and notices
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

function addToCart($id, $con) {
    if(isset($_SESSION['cart'][$id])){
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        // Check for variant_id in GET
        $variant_id = isset($_GET['variant_id']) ? intval($_GET['variant_id']) : 0;
        if ($variant_id) {
            $variant = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `product_variants` WHERE id = $variant_id"));
            if ($variant) {
                $_SESSION['cart'][$id] = [
                    "quantity" => 1,
                    "variant_id" => $variant_id,
                    "variant_label" => $variant['variant_label'],
                    "price" => $variant['price']
                ];
                echo json_encode(['success' => true]);
                exit;
            }
        }
        $sql_p = "SELECT * FROM products WHERE id={$id}";
        $query_p = mysqli_query($con, $sql_p);
        if(mysqli_num_rows($query_p) != 0){
            $row_p = mysqli_fetch_array($query_p);
            $_SESSION['cart'][$row_p['id']] = ["quantity" => 1, "price" => $row_p['productPrice']];
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

function addToWishlist($pid, $con) {
    if(strlen($_SESSION['login']) == 0) {
        header('location:login.php');
    } else {
        mysqli_query($con, "INSERT INTO wishlist(userId,productId) VALUES('{$_SESSION['id']}','$pid')");
        echo "<script>alert('Product added in wishlist');</script>";
        header('location:my-wishlist.php');
    }
}

function submitReview($pid, $con) {
    $qty = $_POST['quality'];
    $price = $_POST['price'];
    $value = $_POST['value'];
    $name = $_POST['name'];
    $summary = $_POST['summary'];
    $review = $_POST['review'];
    mysqli_query($con, "INSERT INTO productreviews(productId,quality,price,value,name,summary,review) VALUES('$pid','$qty','$price','$value','$name','$summary','$review')");
}

if(isset($_GET['ajax']) && $_GET['ajax'] == "addtocart") {
    $id = intval($_GET['id']);
    addToCart($id, $con);
}

if(isset($_GET['ajax']) && $_GET['ajax'] == "buynow") {
    $id = intval($_GET['id']);
    addToCart($id, $con);
    echo json_encode(['redirect' => 'my-cart.php']);
    exit;
}

$pid = intval($_GET['pid']);
if(isset($_GET['pid']) && isset($_GET['action']) && $_GET['action'] == "wishlist") {
    addToWishlist($pid, $con);
}

if(isset($_POST['submit'])) {
    submitReview($pid, $con);
}

$product = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM products WHERE id='$pid'"));
$reviews = mysqli_query($con, "SELECT * FROM productreviews WHERE productId='$pid'");
$related = mysqli_query($con, "SELECT * FROM products WHERE category='{$product['category']}' AND subCategory='{$product['subCategory']}' AND id != '{$product['id']}' LIMIT 6");

// Fetch product variants
$variant_query = mysqli_query($con, "SELECT id, variant_label, price FROM `product_variants` WHERE product_id = $pid AND slot = '1'" );
if (!$variant_query) {
    die("Variant query error: " . mysqli_error($con));
}
$variants = [];
while ($row = mysqli_fetch_assoc($variant_query)) {
    $variants[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlentities($product['productName']); ?> | Details</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        body {
            background-color: #f8fafc !important;
            color: #1e293b;
            padding-top: 130px !important;
        }
        @media (max-width: 768px) {
            body { padding-top: 115px !important; }
        }

        /* Fixed Navigation Adjustment Layer */
        .hh {
            position: fixed !important;
            top: 60px !important;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
        }
        @media (max-width: 768px) {
            .hh { top: 80px !important; z-index: 10; }
        }

        /* Highly Compact Main Canvas Card Container */
        .product-wrapper {
            margin-top: 16px;
            background-color: #ffffff !important;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        @media (max-width: 576px) {
            .product-wrapper { padding: 12px; margin-top: 10px; }
        }

        /* Slim Profile Slider Controls */
        .product-slider {
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 12px 0;
        }
        .product-slider img {
            height: 240px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        @media (max-width: 768px) {
            .product-slider img { height: 180px; }
        }

        /* Product Details Typography Controls */
        .product-info h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        .price-panel {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 14px;
        }
        .price {
            font-size: 22px;
            font-weight: 800;
            color: #0d9488;
        }
        .price-strike {
            text-decoration: line-through;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
        }
        .out-of-stock {
            color: #ef4444;
            font-weight: 700;
            font-size: 13px;
        }

        /* Compact Slim Variant Selector Dropdown */
        .variant-dropdown-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            background: #f8fafc;
            margin-bottom: 16px;
            max-width: 320px;
        }
        #variantSelect {
            border: none;
            background-color: transparent;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            width: 100%;
            outline: none;
            cursor: pointer;
        }

        /* Slim Dynamic Action Strips Buttons */
        .button-row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 16px;
            margin-bottom: 12px;
        }
        .custom-btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            text-decoration: none !important;
        }
        .custom-btn.add-to-cart {
            background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(13, 148, 136, 0.1);
        }
        .custom-btn.add-to-cart:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .custom-btn.buy-now {
            background: #0f172a;
            color: #ffffff;
        }
        .custom-btn.buy-now:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }
        .custom-btn.go-to-cart {
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        @media(max-width: 576px) {
            .custom-btn { flex: 1 1 auto; width: 100%; }
        }

        .btn-wishlist {
            border: 1px solid #e2e8f0;
            color: #ef4444;
            background: #ffffff;
            font-weight: 600;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }
        .btn-wishlist:hover {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        /* Streamlined Segment Sections Headers */
        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f1f5f9;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Compact Description Strips Layout */
        .product-description-section {
            margin-top: 20px;
            padding: 14px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .description-text {
            font-size: 13px;
            line-height: 1.6;
            color: #475569;
        }

        /* Slim Video Frame Overview Components */
        .product-video-section {
            margin-top: 20px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
        }

        /* Slick Unified Review Architecture Styles */
        .review-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }
        .review-header-container .section-title {
            margin-bottom: 0 !important;
            border-bottom: none !important;
            padding-bottom: 0 !important;
        }
        .btn-toggle {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-weight: 600;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        .btn-toggle:hover {
            background: #f8fafc;
            color: #0d9488;
            border-color: #0d9488;
        }
        .review-form {
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 10px;
            background: #f8fafc;
            max-width: 100%;
            margin-bottom: 16px;
        }
        .review-form .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 13px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
            color: #ffffff;
            border: none;
            font-weight: 700;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 13px;
        }

        .customer-reviews-section { margin-top: 20px; }
        .review-box {
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            background: #ffffff;
        }
        .review-summary { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .review-content { font-size: 12px; color: #475569; margin-bottom: 6px; line-height: 1.4; }
        .review-meta { font-size: 11px; color: #94a3b8; display: flex; gap: 10px; }

        /* Highly Compact Slim Laptop (6 Items) / Mobile (2 Items) Grid */
        .related-product-section { margin-top: 24px; }
        .product-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: center;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            border-color: #cbd5e1;
        }
        .product-image-wrapper {
            width: 100%;
            aspect-ratio: 1/1;
            overflow: hidden;
            border-radius: 6px;
            background: #f8fafc;
            padding: 4px;
        }
        .product-image-wrapper img { width: 100%; height: 100%; object-fit: contain; }
        .product-title {
            font-size: 11px;
            font-weight: 600;
            line-height: 1.3;
            color: #1e293b;
            margin: 6px 0 2px;
            height: 30px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-price { font-size: 12px; font-weight: 700; color: #0d9488; }
        .variant-item { display: inline-flex; gap: 2px; align-items: center; margin: 0 2px; font-size: 10px; }
        .variant-name { color: #64748b; }
        .variant-price { font-weight: 700; color: #0d9488; }
        
        .alert-success {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 12px;
        }
    </style>
</head>
<body>
<?php include('includes/top-header.php'); ?>
<?php include('includes/main-header.php'); ?>
<div class="hh">
   <?php include('includes/side-menu.php'); ?> 
</div>

<div class="container product-wrapper">
    <div class="row g-3">
        <!-- Image Slider Column Layout Frame -->
        <div class="col-lg-5">
            <div id="product-carousel" class="owl-carousel product-slider">
          <!-- New Code -->
<?php for ($i = 1; $i <= 3; $i++) {
    $img = $product['productImage' . $i];
    if ($img) {
        echo "<div><img src='" . getProductImage($product['id'], $img) . "' alt='View'></div>";
    }
} ?>
            </div>
        </div>
        
        <!-- Text & Action Description Layout Controls Frame -->
        <div class="col-lg-7">
            <div class="ps-lg-2">
                <h1><?php echo htmlentities($product['productName']); ?></h1>
                
                <!-- Variant Selection -->
                <?php if (count($variants) > 0): ?>
                    <div class="mb-3">
                        <label for="variantSelect" class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                            Select Variant
                        </label>
                        <div class="variant-dropdown-card">
                            <select id="variantSelect" onchange="updateVariantPrice()">
                                <?php foreach ($variants as $v): ?>
                                    <option value="<?= $v['id'] ?>" data-price="<?= $v['price'] ?>">
                                        <?= htmlspecialchars($v['variant_label']) ?> – ₹<?= number_format($v['price'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Price Aggregator Component -->
                <?php if ($product['productAvailability'] == 'In Stock') { ?>
                    <div class="price-panel">
                        <span id="productPrice" class="price">
                            Rs.<?= number_format($variants[0]['price'] ?? $product['productPrice'], 2) ?>
                        </span>
                        <?php if (empty($variants)): ?>
                            <span class="price-strike">Rs. <?php echo htmlentities($product['productPriceBeforeDiscount']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php } else { ?>
                    <div class="mb-2"><span class="out-of-stock">⚠️ Out of Stock</span></div>
                <?php } ?>

                <div id="cart-msg" class="alert alert-success"></div>

                <!-- Primary Core Dynamic Action Buttons String -->
                <?php if($product['productAvailability']=='In Stock'){ ?>
                    <div class="button-row">
                        <button class="custom-btn add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-shopping-basket me-1.5"></i> Add to Basket
                        </button>
                        <button class="custom-btn buy-now" onclick="buyNow(<?php echo $product['id']; ?>)">
                            <i class="fas fa-bolt me-1.5"></i> Buy Now
                        </button>
                        <a href="my-cart.php" id="go-to-cart-btn" class="custom-btn go-to-cart">
                            View Cart <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php } ?>
                
                <div class="mt-2">
                    <a href="product-details.php?pid=<?php echo $product['id']; ?>&action=wishlist" class="btn btn-wishlist">
                        <i class="far fa-heart me-1.5"></i> Save
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Description Block Layer Node -->
    <?php if(!empty($product['productDescription'])): ?>
       <div class="product-description-section">
            <h3 class="section-title">Product Description</h3>
            <p class="description-text mb-0"><?php echo nl2br(htmlentities($product['productDescription'])); ?></p>
        </div>
    <?php endif; ?>

    <!-- Context Video Engine Layer -->
    <?php
    $videoPath = null;
    if (!empty($product['productVideo']) && file_exists(__DIR__."/admin/productimages/".$product['id']."/".$product['productVideo'])) {
        $videoPath = "admin/productimages/".$product['id']."/".htmlentities($product['productVideo']);
    } else {
        $files = glob(__DIR__."/admin/productVideo/*.{mp4,webm,ogg}", GLOB_BRACE);
        if (!empty($files)) {
            $videoPath = "admin/productVideo/".basename($files[0]);
        }
    }
    ?>

    <?php if($videoPath): ?>
    <div class="product-video-section text-center">
        <h5 class="section-title">Product Overview</h5>
        <div class="video-wrapper" style="max-width: 320px; margin: 0 auto; position: relative; border-radius: 8px; overflow: hidden; cursor: pointer;">
            <video id="productVideo_<?php echo $product['id']; ?>" autoplay loop muted 
                   style="width: 100%; max-height: 160px; display: block;" 
                   controlsList="nodownload" oncontextmenu="return false;">
                <source src="<?php echo $videoPath; ?>" type="video/mp4">
            </video>
            <button onclick="toggleMute('productVideo_<?php echo $product['id']; ?>')" 
                    id="muteBtn_<?php echo $product['id']; ?>"
                    style="position: absolute; bottom: 8px; right: 8px; width: 30px; height: 30px; border-radius: 50%; border: none; background: rgba(15, 23, 42, 0.6); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                🔇
            </button>
        </div>
    </div>

    <!-- Fullscreen Floating Presentation Modal -->
    <div id="videoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.4); z-index: 10000000000; justify-content: center; align-items: center;">
        <div style="position: relative; max-width: 100%; max-height: 100%; display: flex; justify-content: center; align-items: center;">
            <video id="modalVideo" autoplay loop muted style="max-width: 85vw; max-height: 85vh; border-radius: 8px;" controlsList="nodownload" oncontextmenu="return false;">
                <source src="" type="video/mp4">
            </video>
            <button onclick="toggleModalMute()" id="modalMuteBtn" style="position: absolute; bottom: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; border: none; background: rgba(15, 23, 42, 0.7); color: #fff; font-size: 14px; cursor: pointer; z-index: 100001;">🔇</button>
            <button onclick="closeModal()" style="position: absolute; top: -36px; right: 0; border: none; background: rgba(255, 255, 255, 0.2); color: #fff; font-size: 16px; width: 28px; height: 28px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer;">✕</button>
        </div>
    </div>

    <script>
    function toggleMute(videoId){
        const vid = document.getElementById(videoId);
        const btn = document.getElementById('muteBtn_' + videoId.split('_')[1]);
        vid.muted = !vid.muted;
        btn.textContent = vid.muted ? '🔇' : '🔊';
    }

    document.getElementById('productVideo_<?php echo $product['id']; ?>').addEventListener('click', function(){
        const modal = document.getElementById('videoModal');
        const modalVid = document.getElementById('modalVideo');
        const src = this.querySelector('source').src;
        document.querySelectorAll('video').forEach(v => v.pause());
        modalVid.src = src;
        modal.style.display = 'flex';
        modalVid.play();
        modalVid.muted = false;
        document.getElementById('modalMuteBtn').textContent = '🔊';
        document.body.style.overflow = 'hidden';
    });

    function toggleModalMute(){
        const modalVid = document.getElementById('modalVideo');
        const btn = document.getElementById('modalMuteBtn');
        modalVid.muted = !modalVid.muted;
        btn.textContent = modalVid.muted ? '🔇' : '🔊';
    }

    function closeModal(){
        const modal = document.getElementById('videoModal');
        const modalVid = document.getElementById('modalVideo');
        modalVid.pause();
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    </script>
    <?php endif; ?>

    <!-- User Feedback & Review Integration Section -->
    <div class="customer-reviews-section">
        <div class="review-header-container">
            <h4 class="section-title">Customer Reviews</h4>
            <button class="btn btn-toggle" onclick="toggleReviewForm()">
                <i class="far fa-edit me-1"></i> Write a Review
            </button>
        </div>

        <!-- Hidden Write Form Node Container -->
        <form id="review-form" method="post" class="review-form" style="display: none;">
            <div class="form-group mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Your Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="form-group mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Review Summary</label>
                <input type="text" class="form-control" name="summary" required>
            </div>
            <div class="form-group mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Detailed Review</label>
                <textarea class="form-control" name="review" rows="2" required></textarea>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <label class="form-label fw-bold text-dark small mb-1">Quality</label>
                    <select name="quality" class="form-control text-center" required>
                        <option value="">-</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label fw-bold text-dark small mb-1">Price</label>
                    <select name="price" class="form-control text-center" required>
                        <option value="">-</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label fw-bold text-dark small mb-1">Value</label>
                    <select name="value" class="form-control text-center" required>
                        <option value="">-</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
                    </select>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" name="submit" class="btn btn-submit w-100">Submit Review</button>
            </div>
        </form>

        <!-- Display Reviews Content Blocks Loop -->
        <?php if(mysqli_num_rows($reviews) > 0): ?>
            <?php while($rw = mysqli_fetch_array($reviews)){ ?>
            <div class="review-box">
                <div class="review-summary"><?php echo htmlentities($rw['summary']); ?></div>
                <div class="review-content"><?php echo htmlentities($rw['review']); ?></div>
                <div class="review-meta">
                    <span><strong>By:</strong> <?php echo htmlentities($rw['name']); ?></span>
                    <span><strong>Quality:</strong> <?php echo $rw['quality']; ?>/5</span>
                    <span><strong>Price:</strong> <?php echo $rw['price']; ?>/5</span>
                    <span><strong>Value:</strong> <?php echo $rw['value']; ?>/5</span>
                </div>
            </div>
            <?php } ?>
        <?php endif; ?>
    </div>

    <!-- Responsive Recommendations Grid System Framework (Laptop: 6 / Mobile: 2) -->
    <div class="related-product-section">
        <h4 class="section-title">Related Products</h4>
        <div class="row g-2">
            <?php while($rel = mysqli_fetch_array($related)) { ?>
            <div class="col-6 col-md-4 col-lg-2 col-xl-2">
                <div class="product-card animate__animated animate__fadeIn">
                    <a href="product-details.php?pid=<?php echo $rel['id']; ?>">
                     <!-- New Code -->
<div class="product-image-wrapper">
    <img src="<?php echo getProductImage($rel['id'], $rel['productImage1']); ?>"
         alt="<?php echo htmlentities($rel['productName']); ?>"
         onerror="this.onerror=null; this.src='images/no-image.png';">
</div>

                        <div class="product-info">
                            <h2 class="product-title"><?php echo htmlentities($rel['productName']); ?></h2>
                            <?php  
                            $variantRes = mysqli_query($con, "SELECT variant_label, price FROM product_variants WHERE product_id=".$rel['id']." AND slot = '1' ORDER BY price ASC");
                            if(mysqli_num_rows($variantRes) > 0) {
                            ?>
                                <div class="product-variants-list">
                                <?php while($v = mysqli_fetch_assoc($variantRes)) { ?>
                                    <div class="variant-item">
                                        <span class="variant-name"><?php echo htmlentities($v['variant_label']); ?></span>
                                        <span class="variant-price">₹<?= number_format($v['price'], 0); ?></span>
                                    </div>
                                <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="product-price">
                                    ₹<?= number_format($rel['productPrice'], 0); ?>
                                    <?php if($rel['productPriceBeforeDiscount'] > $rel['productPrice']): ?>
                                        <span class="price-strike">₹<?= number_format($rel['productPriceBeforeDiscount'], 0); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php } ?>
                        </div>
                    </a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script>
function toggleReviewForm() {
    const form = document.getElementById('review-form');
    if(form.style.display === "none" || form.style.display === "") {
        $(form).slideDown(200);
        window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' });
    } else {
        $(form).slideUp(200);
    }
}
function updateVariantPrice() {
    var select = document.getElementById('variantSelect');
    if (!select) return;
    var price = select.options[select.selectedIndex].getAttribute('data-price');
    document.getElementById('productPrice').innerText = 'Rs.' + parseFloat(price).toFixed(2);
}
function addToCart(id) {
    var selectEl = document.getElementById('variantSelect');
    var variantId = selectEl ? selectEl.value : '';
    $.get('product-details.php', {ajax: 'addtocart', id: id, variant_id: variantId}, function(res){
        let data = JSON.parse(res);
        if(data.success){
            $('#cart-msg').html('<i class="fas fa-check-circle me-1.5"></i> Added to basket.').fadeIn();
            $('#go-to-cart-btn').fadeIn();
            setTimeout(() => {
                $('#cart-msg').fadeOut();
            }, 4000);
        }
    });
}
function buyNow(id) {
    var selectEl = document.getElementById('variantSelect');
    var variantId = selectEl ? selectEl.value : '';
    $.get('product-details.php', {ajax: 'addtocart', id: id, variant_id: variantId}, function(res){
        let data = JSON.parse(res);
        if(data.success){
           window.location.href = 'my-cart.php';
        }
    });
}
$(document).ready(function(){
    $("#product-carousel").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayHoverPause: true,
        dots: true
    });
});
</script>
</body>
</html>