<?php


include('includes/config.php');

$msg = '';

if (isset($_POST['submit'])) {
    $category = intval($_POST['category']);
    $subcat = intval($_POST['subcategory']);
    $productname = mysqli_real_escape_string($conn, $_POST['productName']);
    $productcompany = mysqli_real_escape_string($conn, $_POST['productCompany']);
    $productprice = floatval($_POST['productprice']);
    $productpricebd = floatval($_POST['productpricebd']);
    $productdescription = mysqli_real_escape_string($conn, $_POST['productDescription']);
    $productscharge = floatval($_POST['productShippingcharge']);
    $productavailability = mysqli_real_escape_string($conn, $_POST['productAvailability']);
    $unit_type = mysqli_real_escape_string($conn, $_POST['unit_type']);
    $stock = intval($_POST['stock']);
    $stock_management_type = isset($_POST['stock_management_type']) ? $_POST['stock_management_type'] : 'independent'; // ✅ NEW
    $base_buy_price = isset($_POST['base_buy_price']) ? floatval($_POST['base_buy_price']) : 0.00; // ✅ NEW

    $insert_sql = "INSERT INTO products (category, subCategory, productName, productCompany, productPrice, productDescription, shippingCharge, productImage1, productImage2, productImage3, productPriceBeforeDiscount, unit_type, stock, stock_management_type, base_buy_price) 
    VALUES ('$category', '$subcat', '$productname', '$productcompany', '$productprice', '$productdescription', '$productscharge', '', '', '', '$productpricebd', '$unit_type', '$stock', '$stock_management_type', '$base_buy_price')";

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
                    }
                }
            }
            return '';
        }

        $img1 = uploadImage($_FILES['productimage1'], $dir);
        $img2 = uploadImage($_FILES['productimage2'], $dir);
        $img3 = uploadImage($_FILES['productimage3'], $dir);

        $update_sql = "UPDATE products SET productImage1='$img1', productImage2='$img2', productImage3='$img3' WHERE id=$productid";
        mysqli_query($conn, $update_sql);

        if (isset($_POST['variant']) && is_array($_POST['variant'])) {
            foreach ($_POST['variant'] as $v) {
                $label = mysqli_real_escape_string($conn, $v['name']);
                $price = floatval($v['price']);
                $buy_price = isset($v['buy_price']) ? floatval($v['buy_price']) : 0.00; // ✅ NEW
                $vstock = isset($v['stock']) ? intval($v['stock']) : 0;
                if (!empty($label) && $price > 0) {
                    $insert_variant = "INSERT INTO product_variants (product_id, variant_label, price, variant_buy_price, stock) 
                                       VALUES ('$productid', '$label', '$price', '$buy_price', '$vstock')";
                    mysqli_query($conn, $insert_variant);
                }
            }
        }
        
        // Update product availability based on total stock (product + variants)
        include('includes/stock-management.php');
        updateProductAvailabilityFromVariants($conn, $productid);

        $msg = "✅ Product inserted, images uploaded & variants saved!";
    } else {
        $msg = "❌ Error inserting product: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Insert Product</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .form-card {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    h2 {
      font-weight: 600;
      color: #333;
      margin-bottom: 20px;
    }
    label {
      font-weight: 500;
      margin-bottom: 6px;
    }
    .form-control, .form-select {
      border-radius: 8px !important;
    }
    .btn-success {
      padding: 10px 22px;
      border-radius: 8px;
      font-weight: 500;
    }
    .img-thumbnail {
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    /* Responsive fix */
    @media (max-width: 767px) {
      .d-flex.mb-2 {
        flex-direction: column;
      }
      .d-flex.mb-2 input {
        margin-bottom: 8px;
      }
    }
  </style>

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

    function previewImage(input, previewId) {
        const file = input.files[0];
        const preview = document.getElementById(previewId);
        if (file && file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = "none";
        }
    }
  </script>
</head>
<body>
  <?php include('includes/main-header.php') ?>
  
  <div class="container py-4">
    <div class="form-card">
      <h2>➕ Insert New Product</h2>

      <?php if ($msg): ?>
        <div class="alert alert-info"><?php echo htmlentities($msg); ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="row g-3">

        <!-- Category & Subcategory -->
        <div class="col-md-6">
          <label>Category</label>
          <select name="category" class="form-select" onchange="getSubcat(this.value)" required>
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
          <label>Sub Category</label>
          <select name="subcategory" id="subcategory" class="form-select" required>
            <option value="">Select Subcategory</option>
          </select>
        </div>

        <!-- Basic Info -->
        <div class="col-md-6">
          <label>Product Name</label>
          <input type="text" name="productName" class="form-control" required />
        </div>
        <div class="col-md-6">
          <label>Product Company</label>
          <input type="text" name="productCompany" class="form-control" value="ApnaBazaar"/>
        </div>
        <div class="col-12">
          <label>Description</label>
          <textarea name="productDescription" class="form-control" rows="3"></textarea>
        </div>

        <!-- Pricing -->
        <div class="col-md-6">
          <label>Price Before Discount</label>
          <input type="number" name="productpricebd" class="form-control"/>
        </div>
        <div class="col-md-6">
          <label>Price After Discount</label>
          <input type="number" name="productprice" class="form-control"/>
        </div>
        <div class="col-md-6">
          <label>Base Buy Price</label>
          <input type="number" step="0.01" name="base_buy_price" class="form-control" required />
        </div>
        <div class="col-md-6">
          <label>Stock Management Type</label>
          <select name="stock_management_type" class="form-select" required>
            <option value="independent">Independent</option>
            <option value="dependent">Dependent</option>
          </select>
        </div>

        <!-- Stock & Shipping -->
        <div class="col-md-6">
          <label>Shipping Charge</label>
          <input type="number" name="productShippingcharge" class="form-control" value="0" required />
        </div>
        <div class="col-md-6">
          <label>Availability</label>
          <select name="productAvailability" class="form-select" required>
            <option value="">Select</option>
            <option value="In Stock" selected>In Stock</option>
            <option value="Out of Stock">Out of Stock</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="stock">Stock</label>
          <input type="number" name="stock" class="form-control" value="0">
        </div>

        <!-- Images -->
        <div class="col-md-4">
          <label>Product Image 1</label>
          <input type="file" name="productimage1" accept="image/*" class="form-control" onchange="previewImage(this, 'preview1')" required />
          <img id="preview1" class="img-thumbnail mt-2" style="display:none; max-width:100px;">
        </div>
        <div class="col-md-4">
          <label>Product Image 2</label>
          <input type="file" name="productimage2" accept="image/*" class="form-control" onchange="previewImage(this, 'preview2')" />
          <img id="preview2" class="img-thumbnail mt-2" style="display:none; max-width:100px;">
        </div>
        <div class="col-md-4">
          <label>Product Image 3</label>
          <input type="file" name="productimage3" accept="image/*" class="form-control" onchange="previewImage(this, 'preview3')" />
          <img id="preview3" class="img-thumbnail mt-2" style="display:none; max-width:100px;">
        </div>

        <!-- Variants -->
        <div class="col-12">
          <label><strong>Select Unit Type:</strong></label>
          <select id="unit-type" name="unit_type" class="form-select">
            <option value="">-- Select --</option>
            <option value="pcs">Pieces</option>
            <option value="kg">Kilogram</option>
            <option value="gm">Gram</option>
            <option value="ltr">Litre</option>
            <option value="ml">Millilitre</option>
            <option value="size">Size</option>
            <option value="color">Color</option>
          </select>
        </div>
        <div id="unit-options" class="col-12 mt-2"></div>

        <!-- Submit -->
        <div class="col-12 text-end">
          <button type="submit" name="submit" class="btn btn-success">🚀 Insert Product</button>
        </div>
      </form>
    </div>
  </div>

<script>
document.getElementById('unit-type').addEventListener('change', function() {
  const type = this.value;
  let html = '';
  const optionsMap = {
    pcs: ['1 pc','2 pcs','3 pcs','4 pcs','5 pcs'],
    kg: ['1 Kg','2 Kg','5 Kg','10 Kg'],
    gm: ['100 gm','250 gm','500 gm','1 Kg'],
    ltr: ['1 Ltr','2 Ltr','5 Ltr'],
    ml: ['100 ml','250 ml','500 ml','1 Ltr'],
    size: ['XS','S','M','L','XL'] 
  };

  if (type === 'color') {
    html += '<label>Enter Color Variants with Price, Buy Price and Stock:</label>';
    for (let i = 0; i < 5; i++) {
      html += `
        <div class="d-flex mb-2">
          <input type="text" name="variant[${i}][name]" placeholder="Color Name" class="form-control me-2" />
          <input type="number" name="variant[${i}][price]" placeholder="Price" class="form-control me-2" />
          <input type="number" name="variant[${i}][buy_price]" placeholder="Buy Price" class="form-control me-2" step="0.01" value="0.00" />
          <input type="number" name="variant[${i}][stock]" placeholder="Stock" class="form-control" min="10" value="10" />
        </div>`;
    }
  } else if (optionsMap[type]) {
    html += `<label>Enter Prices, Buy Price and Stock for ${type.toUpperCase()} Variants:</label>`;
    optionsMap[type].forEach((val, i) => {
      html += `
        <div class="d-flex mb-2">
          <input type="text" name="variant[${i}][name]" value="${val}" readonly class="form-control me-2" />
          <input type="number" name="variant[${i}][price]" placeholder="Price" class="form-control me-2" />
          <input type="number" name="variant[${i}][buy_price]" placeholder="Buy Price" class="form-control me-2" step="0.01" value="0.00" />
          <input type="number" name="variant[${i}][stock]" placeholder="Stock" class="form-control" min="10" value="10" />
        </div>`;
    });
  }
  document.getElementById('unit-options').innerHTML = html;
});
</script>
</body>
</html>

