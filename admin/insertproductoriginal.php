<?php

session_start();
if (!isset($_SESSION['admin_login'])) {
    header('location:index.php'); // index.php = your login page
    exit;
}

include('includes/config.php');

$msg = '';

if (isset($_POST['submit'])) {
    // Sanitize and validate inputs
    $category = intval($_POST['category']);
    $subcat = intval($_POST['subcategory']);
    $productname = mysqli_real_escape_string($conn, $_POST['productName']);
    $productcompany = mysqli_real_escape_string($conn, $_POST['productCompany']);
    $productprice = floatval($_POST['productprice']);
    $productpricebd = floatval($_POST['productpricebd']);
    $productdescription = mysqli_real_escape_string($conn, $_POST['productDescription']);
    $productscharge = floatval($_POST['productShippingcharge']);
    $productavailability = mysqli_real_escape_string($conn, $_POST['productAvailability']);

    // Insert basic product info
    $insert_sql = "INSERT INTO products (category, subCategory, productName, productCompany, productPrice, productDescription, shippingCharge, productAvailability, productImage1, productImage2, productImage3, productPriceBeforeDiscount) 
    VALUES ('$category', '$subcat', '$productname', '$productcompany', '$productprice', '$productdescription', '$productscharge', '$productavailability', '', '', '', '$productpricebd')";

    if (mysqli_query($conn, $insert_sql)) {
        $productid = mysqli_insert_id($conn);
        $dir = "productimages/$productid";

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                die("Failed to create folder: $dir");
            }
        }

        function uploadImage($file, $dir)
        {
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];

            if (isset($file) && $file['error'] === 0) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $mime = mime_content_type($file['tmp_name']);

                if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime)) {
                    $newname = uniqid() . '.' . $ext;
                    $destination = "$dir/$newname";

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        return $newname;
                    } else {
                        echo "Error: Failed to move file " . $file['name'] . "<br>";
                    }
                } else {
                    echo "Invalid file type for " . $file['name'] . "<br>";
                }
            } else {
                echo "Upload error: " . $file['error'] . " for file: " . $file['name'] . "<br>";
            }
            return '';
        }

        // Upload images
        $img1 = uploadImage($_FILES['productimage1'], $dir);
        $img2 = uploadImage($_FILES['productimage2'], $dir);
        $img3 = uploadImage($_FILES['productimage3'], $dir);

        // Update product record with image names
        $update_sql = "UPDATE products SET productImage1='$img1', productImage2='$img2', productImage3='$img3' WHERE id=$productid";
        mysqli_query($conn, $update_sql);

        $msg = "✅ Product inserted and images uploaded successfully!";
    } else {
        $msg = "❌ Error inserting product: " . mysqli_error($conn);
    }
}
?>

<!-- HTML FORM BELOW -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Insert Product</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/theme.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function getSubcat(val) {
        $.ajax({
            type: "POST",
            url: "get_subcat.php",
            data: { cat_id: val },
            success: function(data) {
                $("#subcategory").html(data);
            }
        });
    }
    </script>
    <style>
    .form-label {
    font-weight: 500;
    font-size: 14px;
  }

  .form-control-sm {
    font-size: 14px;
    padding: 6px 10px;
    height: auto;
  }

  textarea.form-control-sm {
    resize: vertical;
  }

  .btn-success {
    font-size: 15px;
    padding: 8px 16px;
  }
        .form-group {
    margin-bottom: 12px;
  }

  .form-group label {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
    display: block;
  }

  .form-control, .form-select, textarea {
    font-size: 14px;
    padding: 6px 10px;
    height: auto;
    border-radius: 6px;
  }

  textarea.form-control {
    resize: vertical;
  }

  .btn {
    padding: 6px 14px;
    font-size: 14px;
  }

  img.preview-thumb {
    display: none;
    margin-top: 8px;
    max-width: 100px;
    border: 1px solid #ccc;
    padding: 4px;
    border-radius: 4px;
  }
    </style>
</head>
<body>
        <?php include('includes/main-header.php') ?>
<div class="container">
    <h2>Insert Product</h2>
    <?php if ($msg): ?>
        <div class="alert alert-info"><?php echo htmlentities($msg); ?></div>
    <?php endif; ?>

  <!-- 🌟 Insert Product Form -->
<form method="post" enctype="multipart/form-data" class="p-3 bg-white rounded shadow-sm">
  <div class="row g-3">

    <div class="col-md-6">
      <label class="form-label">Category</label>
      <select name="category" class="form-select form-control-sm" onchange="getSubcat(this.value)" required>
        <option value="">Select Category</option>
        <?php 
          $query = mysqli_query($conn, "SELECT * FROM category");
          while ($row = mysqli_fetch_array($query)) {
              echo "<option value='{$row['id']}'>" . htmlentities($row['categoryName']) . "</option>";
          }
        ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Sub Category</label>
      <select name="subcategory" id="subcategory" class="form-select form-control-sm" required>
        <option value="">Select Subcategory</option>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Product Name</label>
      <input type="text" name="productName" class="form-control form-control-sm" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Product Company</label>
      <input type="text" name="productCompany" class="form-control form-control-sm" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Price Before Discount</label>
      <input type="number" name="productpricebd" class="form-control form-control-sm" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Price After Discount</label>
      <input type="number" name="productprice" class="form-control form-control-sm" required>
    </div>

    <div class="col-md-12">
      <label class="form-label">Description</label>
      <textarea name="productDescription" class="form-control form-control-sm" rows="3"></textarea>
    </div>

    <div class="col-md-6">
      <label class="form-label">Shipping Charge</label>
      <input type="number" name="productShippingcharge" class="form-control form-control-sm"  value="0" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Availability</label>
      <select name="productAvailability" class="form-select form-control-sm" required>
        <option value="">Select</option>
        <option value="In Stock" selected>In Stock</option>
        <option value="Out of Stock">Out of Stock</option>
      </select>
    </div>

    <!-- 📸 Image Uploads with Preview -->
    <div class="col-md-4">
      <label class="form-label">Product Image 1</label>
      <input type="file" name="productimage1" id="productimage1" accept="image/*" class="form-control form-control-sm" onchange="previewImage(this, 'preview1')" required>
      <img id="preview1" class="img-thumbnail mt-2" style="display:none; max-width:100px;">
    </div>

    <div class="col-md-4">
      <label class="form-label">Product Image 2</label>
      <input type="file" name="productimage2" id="productimage2" accept="image/*" class="form-control form-control-sm" onchange="previewImage(this, 'preview2')" required>
      <img id="preview2" class="img-thumbnail mt-2" style="display:none; max-width:100px;">
    </div>

    <div class="col-md-4">
      <label class="form-label">Product Image 3</label>
      <input type="file" name="productimage3" id="productimage3" accept="image/*" class="form-control form-control-sm" onchange="previewImage(this, 'preview3')">
      <img id="preview3" class="img-thumbnail mt-2" style="display:none; max-width:100px;">
    </div>

    <div class="col-md-12 text-end">
      <button type="submit" name="submit" class="btn btn-success px-4">
        <i class="fas fa-plus-circle"></i> Insert Product
      </button>
    </div>

  </div>
</form>
</div>
</body>
</html>
<script>
function previewImage(input, previewId) {
  const file = input.files[0];
  const preview = document.getElementById(previewId);

  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    preview.src = "#";
    preview.style.display = "none";
  }
}
</script>
