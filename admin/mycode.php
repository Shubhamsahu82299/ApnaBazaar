<?php
session_start();
include('includes/config.php');

$msg = '';

// ---------- Handle AJAX for Subcategory ----------
if (isset($_GET['ajax']) && $_GET['ajax']=='subcat') {
    $cat = (int)$_GET['cat'];
    $res = $conn->query("SELECT id,subcategoryName FROM subcategory WHERE categoryid='$cat'");
    echo "<option value=''>Select Subcategory</option>";
    while($row = $res->fetch_assoc()){
        echo "<option value='{$row['id']}'>{$row['subcategoryName']}</option>";
    }
    exit;
}

// ---------- Handle Form Submit ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat = $_POST['category'] ?? '';
    $subcat = $_POST['subcategory'] ?? '';
    $pname = $_POST['pname'] ?? '';
    $company = $_POST['company'] ?? '';
    $desc = $_POST['description'] ?? '';
    $pricebefore = $_POST['pricebefore'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $buyprice = $_POST['buyprice'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $stocktype = $_POST['stocktype'] ?? 'independent';

    // --- Insert main product ---
    $stmt = $conn->prepare("INSERT INTO products(category,subcategory,productName,company,description,pricebefore,price,buyPrice,stock,stocktype) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssddis", $cat,$subcat,$pname,$company,$desc,$pricebefore,$price,$buyprice,$stock,$stocktype);
    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;

        // --- Variants insert ---
        if (!empty($_POST['variant_name'])) {
            foreach ($_POST['variant_name'] as $i => $vname) {
                $vprice = $_POST['variant_price'][$i] ?? 0;
                $vbuy = $_POST['variant_buy'][$i] ?? 0;
                $vstock = $_POST['variant_stock'][$i] ?? 0;
                $conn->query("INSERT INTO product_variants(product_id,name,price,buyprice,stock) 
                              VALUES ('$product_id','$vname','$vprice','$vbuy','$vstock')");
            }
        }

        // --- Images upload ---
        for ($i=1;$i<=3;$i++) {
            if (!empty($_FILES["img$i"]['name'])) {
                $fname = time().'_'.basename($_FILES["img$i"]["name"]);
                $target = "uploads/".$fname;
                if (move_uploaded_file($_FILES["img$i"]["tmp_name"], $target)) {
                    $conn->query("INSERT INTO product_images(product_id,image) VALUES('$product_id','$fname')");
                }
            }
        }

        $msg = "✅ Product inserted successfully!";
    } else {
        $msg = "❌ Error: ".$conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Insert Product</title>
<link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body { background:#f5f7fa; font-size:14px; }
.form-card { background:#fff; border-radius:12px; padding:15px; margin-bottom:15px; box-shadow:0 2px 6px rgba(0,0,0,0.06); }
label { font-weight:600; margin-bottom:4px; font-size:13px; }
.form-control, select { font-size:14px; padding:6px 10px; border-radius:8px; }
textarea { resize:none; }
.variant-row input { font-size:13px; padding:6px 8px; }
.readonly { background:#f1f8f1 !important; }
.btn-sm { font-size:13px; padding:5px 10px; border-radius:8px; }
.section-title { font-size:15px; font-weight:700; margin-bottom:8px; color:#444; }
</style>
</head>
<body>
<div class="container my-3">
    <h4 class="mb-3 text-center fw-bold">➕ Insert Product</h4>
    <?php if($msg){ echo "<div class='alert alert-info'>$msg</div>"; } ?>

    <form method="post" enctype="multipart/form-data">

        <!-- ✅ Category -->
        <div class="form-card">
            <div class="section-title">Category</div>
            <div class="row g-2">
                <div class="col-md-6 col-12">
                    <label>Category</label>
                    <select name="category" id="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php
                        $res = $conn->query("SELECT id,categoryName FROM category");
                        while($row = $res->fetch_assoc()){
                            echo "<option value='{$row['id']}'>{$row['categoryName']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 col-12">
                    <label>Sub Category</label>
                    <select name="subcategory" id="subcategory" class="form-control" required>
                        <option value="">Select Subcategory</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ✅ Info -->
        <div class="form-card">
            <div class="section-title">Product Info</div>
            <div class="row g-2">
                <div class="col-md-6 col-12">
                    <label>Product Name</label>
                    <input type="text" name="pname" class="form-control" required>
                </div>
                <div class="col-md-6 col-12">
                    <label>Company</label>
                    <input type="text" name="company" class="form-control" value="ApnaBazaar">
                </div>
                <div class="col-12">
                    <label>Description</label>
                    <textarea name="description" rows="2" class="form-control"></textarea>
                </div>
                <div class="col-md-4 col-6">
                    <label>Price Before Discount</label>
                    <input type="number" name="pricebefore" class="form-control">
                </div>
                <div class="col-md-4 col-6">
                    <label>Selling Price</label>
                    <input type="number" name="price" class="form-control">
                </div>
                <div class="col-md-4 col-12">
                    <label>Base Buy Price</label>
                    <input type="number" step="0.01" name="buyprice" class="form-control">
                </div>
            </div>
        </div>

        <!-- ✅ Stock -->
        <div class="form-card">
            <div class="section-title">Stock & Management</div>
            <div class="row g-2">
                <div class="col-md-6 col-12">
                    <label>Total Stock</label>
                    <input type="number" name="stock" class="form-control">
                </div>
                <div class="col-md-6 col-12">
                    <label>Stock Type</label><br>
                    <input type="radio" name="stocktype" value="independent" checked> Independent
                    <input type="radio" name="stocktype" value="dependent"> Dependent
                </div>
            </div>
        </div>

        <!-- ✅ Variants -->
        <div class="form-card">
            <div class="section-title">Variants</div>
            <button type="button" id="addVariant" class="btn btn-sm btn-success mb-2">➕ Add Variant</button>
            <div id="variant-box">
                <div class="row g-2 variant-row mb-1">
                    <div class="col-3"><input type="text" name="variant_name[]" class="form-control" placeholder="Name"></div>
                    <div class="col-3"><input type="number" name="variant_price[]" class="form-control" placeholder="Price"></div>
                    <div class="col-3"><input type="number" name="variant_buy[]" class="form-control" placeholder="Buy Price"></div>
                    <div class="col-3 d-flex">
                        <input type="number" name="variant_stock[]" class="form-control" placeholder="Stock">
                        <button type="button" class="btn btn-danger btn-sm ms-1 removeVariant">✖</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Images -->
        <div class="form-card">
            <div class="section-title">Product Images</div>
            <div class="row g-2">
                <div class="col-md-4 col-12"><input type="file" name="img1" class="form-control"></div>
                <div class="col-md-4 col-12"><input type="file" name="img2" class="form-control"></div>
                <div class="col-md-4 col-12"><input type="file" name="img3" class="form-control"></div>
            </div>
        </div>

        <!-- ✅ Submit -->
        <div class="text-center mb-3">
            <button class="btn btn-primary px-4 py-2 rounded-pill">Insert Product</button>
        </div>
    </form>
</div>

<script>
// --- Load Subcategory dynamically (same page ajax) ---
$('#category').on('change', function(){
    var catid = $(this).val();
    $('#subcategory').html('<option>Loading...</option>');
    $.get('insert-product.php',{ajax:'subcat',cat:catid}, function(data){
        $('#subcategory').html(data);
    });
});

// --- Add/remove variant rows ---
$('#addVariant').on('click', function(){
    var row = `<div class="row g-2 variant-row mb-1">
        <div class="col-3"><input type="text" name="variant_name[]" class="form-control" placeholder="Name"></div>
        <div class="col-3"><input type="number" name="variant_price[]" class="form-control" placeholder="Price"></div>
        <div class="col-3"><input type="number" name="variant_buy[]" class="form-control" placeholder="Buy Price"></div>
        <div class="col-3 d-flex">
            <input type="number" name="variant_stock[]" class="form-control" placeholder="Stock">
            <button type="button" class="btn btn-danger btn-sm ms-1 removeVariant">✖</button>
        </div>
    </div>`;
    $('#variant-box').append(row);
});

$(document).on('click','.removeVariant',function(){
    $(this).closest('.variant-row').remove();
});
</script>
</body>
</html>
