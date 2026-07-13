<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Load the dynamic environment and database configs
include('includes/config.php'); // Loads $con instance from framework configuration

$msg = '';

if (isset($_POST['submit'])) {
    $category = intval($_POST['category']);
    $subcat = intval($_POST['subcategory']);
    
    // Fixed DB connection context identifier from $conn to $con globally matching your .env file
    $productname = mysqli_real_escape_string($con, $_POST['productName']);
    $productcompany = mysqli_real_escape_string($con, $_POST['productCompany']);
    $productprice = floatval($_POST['productprice']);
    $productpricebd = floatval($_POST['productpricebd']);
    $productdescription = mysqli_real_escape_string($con, $_POST['productDescription']);
    $productscharge = floatval($_POST['productShippingcharge']);
    $productavailability = mysqli_real_escape_string($con, $_POST['productAvailability']);
    $unit_type = mysqli_real_escape_string($con, $_POST['unit_type']);
    $stock = intval($_POST['stock']);
    $stock_management_type = isset($_POST['stock_management_type']) ? $_POST['stock_management_type'] : 'independent'; 
    $base_buy_price = isset($_POST['base_buy_price']) ? floatval($_POST['base_buy_price']) : 0.00; 

    $insert_sql = "INSERT INTO products (category, subCategory, productName, productCompany, productPrice, productDescription, shippingCharge, productImage1, productImage2, productImage3, productPriceBeforeDiscount, unit_type, stock, stock_management_type, base_buy_price) 
    VALUES ('$category', '$subcat', '$productname', '$productcompany', '$productprice', '$productdescription', '$productscharge', '', '', '', '$productpricebd', '$unit_type', '$stock', '$stock_management_type', '$base_buy_price')";

    if (mysqli_query($con, $insert_sql)) {
        $productid = mysqli_insert_id($con);
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
        mysqli_query($con, $update_sql);

        if (isset($_POST['variant']) && is_array($_POST['variant'])) {
            foreach ($_POST['variant'] as $v) {
                $label = mysqli_real_escape_string($con, $v['name']);
                $price = floatval($v['price']);
                $buy_price = isset($v['buy_price']) ? floatval($v['buy_price']) : 0.00; 
                $vstock = isset($v['stock']) ? intval($v['stock']) : 0;
                if (!empty($label) && $price > 0) {
                    $insert_variant = "INSERT INTO product_variants (product_id, variant_label, price, variant_buy_price, stock) 
                                       VALUES ('$productid', '$label', '$price', '$buy_price', '$vstock')";
                    mysqli_query($con, $insert_variant);
                }
            }
        }
        
        // Update product availability based on total stock (product + variants)
        include('includes/stock-management.php');
        updateProductAvailabilityFromVariants($con, $productid);

        $msg = "✅ Product inserted successfully! Images are placed locally and queue is ready for Cloudinary processing.";
    } else {
        $msg = "❌ Error inserting product: " . mysqli_error($con);
    }
}
?>

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
</head>
<body class="bg-light">
    <?php include('includes/main-header.php') ?>
    <div class="container my-5" style="max-width: 900px;">
        <h2 class="mb-4 text-dark fw-bold">Insert Product Console</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlentities($msg); ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm border">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Category</label>
                    <select name="category" class="form-control" onchange="getSubcat(this.value)" required>
                        <option value="">Select Category</option>
                        <?php 
                        $query = mysqli_query($con, "SELECT * FROM category");
                        while ($row = mysqli_fetch_array($query)) {
                            echo "<option value='{$row['id']}'>" . htmlentities($row['categoryName']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sub Category</label>
                    <select name="subcategory" id="subcategory" class="form-control" required>
                        <option value="">Select Subcategory</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Product Name</label>
                    <input type="text" name="productName" class="form-control" required />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Product Company</label>
                    <input type="text" name="productCompany" class="form-control" value="ApnaBazaar"/>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="productDescription" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Price Before Discount</label>
                    <input type="number" step="0.01" name="productpricebd" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Price After Discount</label>
                    <input type="number" step="0.01" name="productprice" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Base Buy Price</label>
                    <input type="number" step="0.01" name="base_buy_price" class="form-control" value="0.00" required />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stock Management Type</label>
                    <select name="stock_management_type" class="form-control" required>
                        <option value="independent">Independent</option>
                        <option value="dependent">Dependent</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Shipping Charge</label>
                    <input type="number" step="0.01" name="productShippingcharge" class="form-control" value="0" required />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Availability</label>
                    <select name="productAvailability" class="form-control" required>
                        <option value="">Select</option>
                        <option value="In Stock" selected>In Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="stock">Initial Base Stock</label>
                    <input type="number" name="stock" class="form-control" value="0">
                </div>
                
                <div class="col-md-12 mt-4"><hr></div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Product Image 1 (Required)</label>
                    <input type="file" name="productimage1" accept="image/*" class="form-control" onchange="previewImage(this, 'preview1')" required />
                    <img id="preview1" class="img-thumbnail mt-2 shadow-sm" style="display:none; max-width:120px; max-height:120px; object-fit:contain;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Product Image 2</label>
                    <input type="file" name="productimage2" accept="image/*" class="form-control" onchange="previewImage(this, 'preview2')" />
                    <img id="preview2" class="img-thumbnail mt-2 shadow-sm" style="display:none; max-width:120px; max-height:120px; object-fit:contain;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Product Image 3</label>
                    <input type="file" name="productimage3" accept="image/*" class="form-control" onchange="previewImage(this, 'preview3')" />
                    <img id="preview3" class="img-thumbnail mt-2 shadow-sm" style="display:none; max-width:120px; max-height:120px; object-fit:contain;">
                </div>
                
                <div class="col-md-12 mt-4"><hr></div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Select Unit / Variant Type Matrix:</label>
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
                <div id="unit-options" class="col-md-12 mt-3"></div>
                
                <div class="col-md-12 text-end mt-4">
                    <button type="submit" name="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm">🚀 Insert New Product</button>
                </div>
            </div>
        </form>
    </div>
</body>
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
        html += '<label class="form-label fw-bold text-primary">Enter Color Variants with selling details:</label>';
        for (let i = 0; i < 5; i++) {
            html += `
                <div class="d-flex mb-2 gap-2">
                    <input type="text" name="variant[${i}][name]" placeholder="Color Name" class="form-control" />
                    <input type="number" step="0.01" name="variant[${i}][price]" placeholder="Selling Price" class="form-control" />
                    <input type="number" step="0.01" name="variant[${i}][buy_price]" placeholder="Buy Price" class="form-control" value="0.00" />
                    <input type="number" name="variant[${i}][stock]" placeholder="Stock" class="form-control" min="10" value="10" />
                </div>`;
        }
    } else if (optionsMap[type]) {
        html += `<label class="form-label fw-bold text-primary">Enter Configuration for ${type.toUpperCase()} Variants:</label>`;
        optionsMap[type].forEach((val, i) => {
            html += `
                <div class="d-flex mb-2 gap-2">
                    <input type="text" name="variant[${i}][name]" value="${val}" readonly class="form-control bg-light" />
                    <input type="number" step="0.01" name="variant[${i}][price]" placeholder="Selling Price" class="form-control" />
                    <input type="number" step="0.01" name="variant[${i}][buy_price]" placeholder="Buy Price" class="form-control" value="0.00" />
                    <input type="number" name="variant[${i}][stock]" placeholder="Stock" class="form-control" min="10" value="10" />
                </div>`;
        });
    }

    document.getElementById('unit-options').innerHTML = html;
});
</script>
</html>