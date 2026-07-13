<?php
session_start();
include('includes/config.php');


$msg = '';
$error = '';

// Get product_id from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($product_id <= 0){
    $error = 'Missing product_id parameter.';
}

// Handle updates
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_slot2']) && !$error){
    $variant_ids = $_POST['variant_id'] ?? [];
    $prices      = $_POST['variant_price'] ?? [];
    $buy_prices  = $_POST['variant_buy_price'] ?? [];
    $stocks      = $_POST['variant_stock'] ?? [];

    if (!is_array($variant_ids) || count($variant_ids) === 0) {
        $error = 'No variants to update.';
    } else {
        $upd = $conn->prepare("UPDATE product_variants 
                              SET price = ?, variant_buy_price = ?, stock = ? 
                              WHERE id = ? AND product_id = ? AND slot = '2'");
        if(!$upd){
            $error = 'Failed to prepare update statement.';
        } else {
            $updated = 0;
            for ($i=0; $i<count($variant_ids); $i++){
                $vid = (int)$variant_ids[$i];
                $p   = isset($prices[$i]) ? (float)$prices[$i] : 0;
                $bp  = isset($buy_prices[$i]) ? (float)$buy_prices[$i] : 0;
                $s   = isset($stocks[$i]) ? (int)$stocks[$i] : 0;

                $upd->bind_param("ddiii", $p, $bp, $s, $vid, $product_id);
                if($upd->execute()){ $updated++; }
            }
            $msg = $updated . ' slot 2 variant(s) updated successfully!';
            $upd->close();
        }
    }
}

// Fetch product info
$product = null;
if(!$error){
    $q = $conn->prepare("SELECT id, productName FROM products WHERE id=?");
    $q->bind_param("i", $product_id);
    $q->execute();
    $product = $q->get_result()->fetch_assoc();
    $q->close();
    if(!$product){
        $error = "Product not found!";
    }
}

// Fetch slot 2 variants
$variants = [];
if(!$error){
    $q = $conn->prepare("SELECT id, variant_label, price, variant_buy_price, stock 
                        FROM product_variants 
                        WHERE product_id = ? AND slot = '2' 
                        ORDER BY variant_label");
    $q->bind_param("i", $product_id);
    $q->execute();
    $res = $q->get_result();
    while($row = $res->fetch_assoc()){ $variants[] = $row; }
    $q->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | Manage Slot 2 Variants</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-3">
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-sliders-h text-primary me-2"></i>Manage Slot 2 Variants</h4>
    <a class="btn btn-sm btn-outline-secondary" href="manage-products.php"><i class="fas fa-box"></i> Back to Products</a>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg); ?>
      <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error); ?>
      <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if(!$error): ?>
  <div class="card">
    <div class="card-header">
      For Product: <strong><?= htmlspecialchars($product['productName']); ?></strong>
    </div>
    <form method="post">
      <div class="card-body">
       <?php if(count($variants) > 0): ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Variant</th>
            <th>Buy Price</th>
            <th>Selling Price</th>
            <th>Stock</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($variants as $v): ?>
          <tr>
            <td>
              <input type="hidden" name="variant_id[]" value="<?= (int)$v['id']; ?>">
              <?= htmlspecialchars($v['variant_label']); ?>
            </td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm"
                       name="variant_buy_price[]" value="<?= htmlspecialchars($v['variant_buy_price']); ?>" required></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm"
                       name="variant_price[]" value="<?= htmlspecialchars($v['price']); ?>" required></td>
            <td><input type="number" step="1" min="0" class="form-control form-control-sm"
                       name="variant_stock[]" value="<?= (int)$v['stock']; ?>" required></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php else: ?>
    <div class="text-center p-4">
      <p class="text-muted mb-3">No slot 2 variants found for this product.</p>
      <a href="add-slot2-variant.php?product_id=<?= $product_id ?>" class="btn btn-success">
        <i class="fas fa-plus-circle"></i> Add Slot 2 Variant
      </a>
    </div>
<?php endif; ?>

      </div>
      <div class="card-footer text-end">
        <button type="submit" name="update_slot2" class="btn btn-primary">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
