<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once('includes/config.php');

if (strlen($_SESSION["aid"]) == 0) {
    header('location:logout.php');
    exit();
}

$pid = intval($_GET['id']);
$msg = "";

if (isset($_POST['submit'])) {
    $currentImage = $_POST['currentimage'];

    if ($_FILES['productimage1']['name']) {
        $imgName = $_FILES["productimage1"]["name"];
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imgExt, $allowedExts)) {
            $newImageName = md5(time() . $imgName) . "." . $imgExt;
            
            // Local folder paths configurations
            $uploadPath = "productimages/" . $pid . "/";

            // Create directory if not exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            if (move_uploaded_file($_FILES["productimage1"]["tmp_name"], $uploadPath . $newImageName)) {
                
                // Nayi image ka simple text name database me update karein (Taaki migration isko compress kar sake)
                mysqli_query($con, "UPDATE products SET productImage1 = '$newImageName' WHERE id = '$pid'");

                // ✨ SMART UNLINK LOGIC ✨
                // Agar purani image local file thi (Cloudinary link nahi thi), toh hi unlink chalayein
                if ($currentImage && strpos($currentImage, 'https://res.cloudinary.com') === false) {
                    if (file_exists($uploadPath . $currentImage)) {
                        unlink($uploadPath . $currentImage);
                    }
                }

                $msg = "✅ Image uploaded locally! The background sync matrix will migrate it to Cloudinary shortly.";
                echo "<script>alert('$msg'); window.location='edit-products.php?id=$pid';</script>";
                exit();
            } else {
                $msg = "❌ Failed to upload image.";
            }
        } else {
            $msg = "❌ Invalid file type. Only jpg, jpeg, png, gif allowed.";
        }
    }
}

// Fetch current image properties
$res = mysqli_query($con, "SELECT productName, productImage1 FROM products WHERE id='$pid'");
$data = mysqli_fetch_assoc($res);
$currentImage = $data['productImage1'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white p-4 rounded shadow" style="max-width: 600px; margin: 30px auto;">
    <h4>Update Image for: <?= htmlentities($data['productName']) ?></h4>
    <hr>

    <?php if ($msg): ?>
        <div class="alert alert-warning"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="currentimage" value="<?= htmlentities($currentImage) ?>">
        <div class="mb-3">
            <label class="form-label d-block fw-bold">Current Image Preview:</label>
            <?php if ($currentImage): ?>
                <!-- ✨ DYNAMIC HYBRID RESOLVER ENGINE APPLIED ✨ -->
                <!-- Agar database me local name hoga toh admin/productimages/... se fetch karega, else Cloudinary URL se -->
                <img src="<?php echo getProductImage($pid, $currentImage); ?>" 
                     width="200" 
                     class="mb-3 img-thumbnail rounded d-block" 
                     alt="Current Product Preview"
                     onerror="this.src='https://via.placeholder.com/200?text=File+Not+Found';">
            <?php else: ?>
                <p class="text-muted italic small">No image currently uploaded for this product grid context.</p>
            <?php endif; ?>
            
            <label class="form-label fw-bold">Select New Image File</label>
            <input type="file" name="productimage1" class="form-control" accept="image/*" required>
            <div class="form-text text-muted small">Allowed extensions layout structure: JPG, JPEG, PNG, GIF</div>
        </div>
        <hr>
        <button type="submit" name="submit" class="btn btn-primary px-4 fw-bold">Update Image</button>
        <a href="edit-products.php?id=<?= $pid ?>" class="btn btn-secondary px-4">Cancel</a>
    </form>
</div>
</body>
</html>