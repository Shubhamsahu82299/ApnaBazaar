<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

$id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
if(!$id) die("Product ID missing");

$product = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM products WHERE id=$id"));
if(!$product) die("Product not found");

// ---------------- SAVE HANDLER ----------------
if(isset($_POST['save_all'])){
    // Update Product
    $name = mysqli_real_escape_string($con,$_POST['productName']);
    $price = floatval($_POST['price']);
    $buy_price = floatval($_POST['buy_price']);
    $stock = intval($_POST['stock']);
    $availability = mysqli_real_escape_string($con,$_POST['productAvailability']);
    $stock_mgmt = mysqli_real_escape_string($con,$_POST['stock_management_type']);
    $category = mysqli_real_escape_string($con,$_POST['category']);

   mysqli_query($con,"UPDATE products SET 
    productName='$name',
    productPrice=$price,
    base_buy_price=$buy_price,
    stock=$stock,
    category='$category',
    productAvailability='$availability',
    stock_management_type='$stock_mgmt'
    WHERE id=$id");

    // Update existing variants
    if(!empty($_POST['variant_id'])){
        $delete_variants = isset($_POST['delete_variant']) ? $_POST['delete_variant'] : [];

        foreach($_POST['variant_id'] as $k=>$vid){
            $vid = intval($vid);
            if(in_array($vid, $delete_variants)){
                mysqli_query($con,"DELETE FROM product_variants WHERE id=$vid");
            } else {
                $vl = mysqli_real_escape_string($con,$_POST['variant_label'][$k]);
                $vp = floatval($_POST['variant_price'][$k]);
                $vbp = floatval($_POST['variant_buy_price'][$k]);
                $vs = intval($_POST['variant_stock'][$k]);

                mysqli_query($con,"UPDATE product_variants SET 
                    variant_label='$vl',
                    price=$vp,
                    variant_buy_price=$vbp,
                    stock=$vs
                    WHERE id=$vid");
            }
        }
    }

    // Add new variant
    if(!empty($_POST['new_variant_label'])){
        $vl = mysqli_real_escape_string($con,$_POST['new_variant_label']);
        $vp = floatval($_POST['new_variant_price']);
        $vbp = floatval($_POST['new_variant_buy_price']);
        $vs = intval($_POST['new_variant_stock']);
        mysqli_query($con,"INSERT INTO product_variants 
            (product_id,variant_label,price,variant_buy_price,stock)
            VALUES ($id,'$vl',$vp,$vbp,$vs)");
    }

    // Dependent stock recompute
    if($stock_mgmt == 'dependent'){
        $rs = mysqli_query($con,"SELECT SUM(stock) as total FROM product_variants WHERE product_id=$id");
        $row = mysqli_fetch_assoc($rs);
        $total_stock = isset($row['total']) ? $row['total'] : 0;
        mysqli_query($con,"UPDATE products SET stock=$total_stock WHERE id=$id");
    }

    echo "<div class='alert alert-success'>✅ All changes saved</div>";
}

// ---------------- Fetch Variants ----------------
$variants=[];
$q=mysqli_query($con,"SELECT * FROM product_variants WHERE product_id=$id");
while($r=mysqli_fetch_assoc($q)) $variants[]=$r;
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Product Editor</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
<h2>🔧 Admin Product Editor</h2>

<form method="post" class="card p-3 mb-4">
  <input type="hidden" name="save_all" value="1">

  <!-- Product Fields -->
  <div class="row">
    <div class="col-md-6 mb-2">
      <label>Name</label>
      <input type="text" name="productName" class="form-control" value="<?= htmlspecialchars($product['productName']) ?>">
    </div>
    <div class="col-md-3 mb-2">
      <label>Price (default)</label>
      <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['productPrice'] ?>">
    </div>
    <div class="col-md-3 mb-2">
      <label>Buy Price</label>
      <input type="number" step="0.01" name="buy_price" class="form-control" value="<?= $product['base_buy_price'] ?>">
    </div>
    <div class="col-md-3 mb-2">
      <label>Stock</label>
      <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>">
    </div>
    <div class="col-md-3 mb-2">
      <label>Availability</label>
      <select name="productAvailability" class="form-select">
        <option value="In Stock" <?= $product['productAvailability']=='In Stock'?'selected':'' ?>>In Stock</option>
        <option value="Out of Stock" <?= $product['productAvailability']=='Out of Stock'?'selected':'' ?>>Out of Stock</option>
      </select>
    </div>
    <div class="col-md-3 mb-2">
      <label>Stock Management</label>
      <select name="stock_management_type" class="form-select">
        <option value="independent" <?= $product['stock_management_type']=='independent'?'selected':'' ?>>Independent</option>
        <option value="dependent" <?= $product['stock_management_type']=='dependent'?'selected':'' ?>>Dependent (Variants)</option>
      </select>
    </div>
    <div class="col-md-3 mb-2">
      <label>Category</label>
      <input type="text" name="category" class="form-control" value="<?= $product['category'] ?>">
    </div>
  </div>

  <!-- Variants -->
  <h3 class="mt-4">📦 Variants</h3>
  <table class="table table-bordered">
    <tr><th>ID</th><th>Label</th><th>Price</th><th>Buy Price</th><th>Stock</th><th>Delete?</th></tr>
    <?php foreach($variants as $k=>$v): ?>
      <tr>
        <td>
          <?= $v['id'] ?>
          <input type="hidden" name="variant_id[]" value="<?= $v['id'] ?>">
        </td>
        <td><input name="variant_label[]" value="<?= htmlspecialchars($v['variant_label']) ?>" class="form-control"></td>
        <td><input type="number" step="0.01" name="variant_price[]" value="<?= $v['price'] ?>" class="form-control"></td>
        <td><input type="number" step="0.01" name="variant_buy_price[]" value="<?= $v['variant_buy_price'] ?>" class="form-control"></td>
        <td><input type="number" name="variant_stock[]" value="<?= $v['stock'] ?>" class="form-control"></td>
        <td class="text-center">
          <input type="checkbox" name="delete_variant[]" value="<?= $v['id'] ?>">
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Add Variant -->
  <h4>Add New Variant</h4>
  <div class="row g-2 mb-3">
    <div class="col"><input type="text" name="new_variant_label" placeholder="Label" class="form-control"></div>
    <div class="col"><input type="number" step="0.01" name="new_variant_price" placeholder="Price" class="form-control"></div>
    <div class="col"><input type="number" step="0.01" name="new_variant_buy_price" placeholder="Buy Price" class="form-control"></div>
    <div class="col"><input type="number" name="new_variant_stock" placeholder="Stock" class="form-control"></div>
  </div>

  <!-- Single Save -->
  <button class="btn btn-primary mt-2">💾 Save All Changes</button>
</form>

</body>
</html>
