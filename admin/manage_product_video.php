<?php
include('includes/config.php');
session_start();

// Message
$msg = '';

// Handle assign video to product
if(isset($_POST['assign_video'])){
    $videoProductId = intval($_POST['product_id']);
    $videoFile = $_POST['video_file'];

    // Update productVideo column
    mysqli_query($conn,"UPDATE products SET productVideo='$videoFile' WHERE id=$videoProductId");
    $msg = "✅ Video assigned to product successfully.";
}

// Handle remove video from product
if(isset($_POST['remove_video'])){
    $videoProductId = intval($_POST['product_id']);
    mysqli_query($conn,"UPDATE products SET productVideo='' WHERE id=$videoProductId");
    $msg = "✅ Video removed from product successfully.";
}

// Get all products
$products = mysqli_query($conn,"SELECT id, productName, productVideo FROM products ORDER BY productName ASC");

// Collect all videos from productimages folder
$videoFiles = [];
$dirBase = 'productimages/';
$scanDirs = glob($dirBase.'*', GLOB_ONLYDIR);

foreach($scanDirs as $dir){
    $productId = basename($dir);
    $files = glob("$dir/*.{mp4,mov,webm,mkv}", GLOB_BRACE);
    foreach($files as $f){
        $videoFiles[] = ['file'=>$f, 'product_id'=>$productId];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Product Videos Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .video-card{border:1px solid #ddd; padding:10px; border-radius:5px; margin-bottom:15px;}
        .video-card video{width:100%; max-width:300px; height:180px; display:block; margin-bottom:5px;}
        .assigned-products{font-size:0.9rem; color:#555;}
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>🎬 All Product Videos Manager</h2>
    <?php if($msg): ?>
        <div class="alert alert-info"><?php echo htmlentities($msg); ?></div>
    <?php endif; ?>

    <div class="row">
        <?php foreach($videoFiles as $v): 
            $videoFileName = basename($v['file']);
            // Find all products using this video
            $usedProducts = [];
            foreach($products as $p){} // reset array pointer
            mysqli_data_seek($products,0);
            while($pRow = mysqli_fetch_assoc($products)){
                if($pRow['productVideo']==$videoFileName){
                    $usedProducts[] = $pRow;
                }
            }
        ?>
        <div class="col-md-4">
            <div class="video-card">
                <video controls>
                    <source src="<?php echo $v['file']; ?>" type="video/mp4">
                    Your browser does not support HTML5 video.
                </video>
                <div class="assigned-products mb-2">
                    <strong>Assigned to:</strong>
                    <?php if(count($usedProducts)>0){
                        foreach($usedProducts as $up){
                            echo htmlentities($up['productName'])."<br>";
                        }
                    } else {
                        echo "<em>None</em>";
                    } ?>
                </div>

                <!-- Assign to product -->
                <form method="post" class="mb-1">
                    <input type="hidden" name="video_file" value="<?php echo $videoFileName; ?>">
                    <select name="product_id" class="form-select select2" required>
                        <option value="">-- Select Product --</option>
                        <?php
                        mysqli_data_seek($products,0);
                        while($pRow = mysqli_fetch_assoc($products)){
                            echo '<option value="'.$pRow['id'].'">'.htmlentities($pRow['productName']).'</option>';
                        }
                        ?>
                    </select>
                    <button type="submit" name="assign_video" class="btn btn-success btn-sm mt-1 w-100">Assign to Product</button>
                </form>

                <!-- Remove from product -->
                <?php if(count($usedProducts)>0): ?>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo $usedProducts[0]['id']; ?>">
                    <button type="submit" name="remove_video" class="btn btn-warning btn-sm w-100">Remove from Product</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$('.select2').select2({width:'100%', placeholder:"Select product...", allowClear:true});
</script>
</body>
</html>
