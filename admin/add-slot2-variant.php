<?php
session_start();
include('includes/config.php');

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$msg = '';
$error = '';

if ($product_id <= 0) {
    $error = "Missing or invalid product_id.";
}

// Save Slot 2 Variants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_slot2']) && !$error) {
    $names = $_POST['variant_name'] ?? [];
    $buy_prices = $_POST['variant_buy_price'] ?? [];
    $prices = $_POST['variant_price'] ?? [];
    $stocks = $_POST['variant_stock'] ?? [];

    if (count($names) > 0) {
        $insert = $conn->prepare("INSERT INTO product_variants (product_id, variant_label, price, variant_buy_price, stock, slot) 
                                 VALUES (?, ?, ?, ?, ?, '2')");
        $inserted = 0;
        for ($i = 0; $i < count($names); $i++) {
            $label = trim($names[$i]);
            if ($label === '') continue;
            $bp = isset($buy_prices[$i]) ? (float)$buy_prices[$i] : 0;
            $p  = isset($prices[$i]) ? (float)$prices[$i] : 0;
            $s  = isset($stocks[$i]) ? (int)$stocks[$i] : 0;
            $insert->bind_param("isddi", $product_id, $label, $p, $bp, $s);
            if ($insert->execute()) $inserted++;
        }
        $insert->close();
        $msg = "$inserted Slot 2 variant(s) added successfully!";
    } else {
        $error = "No variants submitted.";
    }
}

// Fetch Slot 1 Variants as reference
$slot1_variants = [];
if (!$error) {
    $q = $conn->prepare("SELECT id, variant_label, price, variant_buy_price, stock 
                        FROM product_variants 
                        WHERE product_id = ? AND slot = '1' ORDER BY variant_label");
    $q->bind_param("i", $product_id);
    $q->execute();
    $res = $q->get_result();
    while ($row = $res->fetch_assoc()) { $slot1_variants[] = $row; }
    $q->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Slot 2 Variants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-plus-circle text-success me-2"></i>Add Slot 2 Variants</h4>
    <a href="slot2-manage.php?id=<?= $product_id ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to Slot 2 Manage
    </a>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if(!$error): ?>
  <form method="post">
    <div class="card">
      <div class="card-header">Based on Slot 1 Variants</div>
      <div class="card-body">
        <?php if(count($slot1_variants) > 0): ?>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Variant Label</th>
                <th>Buy Price (Slot 2)</th>
                <th>Selling Price (Slot 2)</th>
                <th>Stock (Slot 2)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($slot1_variants as $v): ?>
              <tr>
                <td>
                  <input type="hidden" name="variant_name[]" value="<?= htmlspecialchars($v['variant_label']) ?>">
                  <strong><?= htmlspecialchars($v['variant_label']) ?></strong>
                </td>
                <td><input type="number" step="0.01" min="0" name="variant_buy_price[]" class="form-control form-control-sm" value="0"></td>
                <td><input type="number" step="0.01" min="0" name="variant_price[]" class="form-control form-control-sm" value="0"></td>
                <td><input type="number" step="1" min="0" name="variant_stock[]" class="form-control form-control-sm" value="0"></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p class="text-muted">No Slot 1 variants found for this product.</p>
        <?php endif; ?>
      </div>
      <div class="card-footer text-end">
        <button type="submit" name="save_slot2" class="btn btn-success"><i class="fas fa-save"></i> Save Slot 2 Variants</button>
      </div>
    </div>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
