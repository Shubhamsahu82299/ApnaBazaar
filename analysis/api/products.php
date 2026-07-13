<?php
require_once __DIR__.'/db.php';

$action = $_GET['action'] ?? 'list';

try {
  switch ($action) {
    case 'list':
      // minimal list for dropdowns
      $stmt = $pdo->query("SELECT id, productName, stock, stock_management_type FROM products ORDER BY productName ASC");
      json_out($stmt->fetchAll());
      break;

    case 'low_stock':
      $threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10;
      $sql = "SELECT p.id, p.productName, p.stock_management_type,
                     p.stock AS product_stock,
                     pv.id AS variant_id, pv.variant_label, pv.stock AS variant_stock
              FROM products p
              LEFT JOIN product_variants pv ON pv.product_id = p.id
              WHERE (p.stock_management_type='independent' AND (p.stock IS NOT NULL AND p.stock <= :th))
                 OR (p.stock_management_type='dependent' AND (pv.stock IS NOT NULL AND pv.stock <= :th))
              ORDER BY p.productName";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':th'=>$threshold]);
      json_out($stmt->fetchAll());
      break;

    case 'out_of_stock':
      $stmt = $pdo->query("SELECT id, productName, productAvailability FROM products WHERE productAvailability='Out of Stock'");
      json_out($stmt->fetchAll());
      break;

    case 'recompute_stock':
      // For dependent products: set products.stock = SUM(variant stock)
      $product_id = (int)($_POST['product_id'] ?? 0);
      if (!$product_id) json_out(['error'=>'product_id required'], 400);
      $pdo->beginTransaction();
      $get = $pdo->prepare("SELECT stock_management_type FROM products WHERE id=? FOR UPDATE");
      $get->execute([$product_id]);
      $row = $get->fetch();
      if (!$row) { $pdo->rollBack(); json_out(['error'=>'product not found'], 404); }
      if ($row['stock_management_type'] !== 'dependent') {
        $pdo->rollBack();
        json_out(['error'=>'product is not dependent-managed'], 400);
      }
      $sum = $pdo->prepare("SELECT COALESCE(SUM(stock),0) AS total FROM product_variants WHERE product_id=?");
      $sum->execute([$product_id]);
      $total = (int)$sum->fetchColumn();
      $upd = $pdo->prepare("UPDATE products SET stock=? WHERE id=?");
      $upd->execute([$total, $product_id]);
      $pdo->commit();
      json_out(['ok'=>true, 'product_id'=>$product_id, 'new_stock'=>$total]);
      break;

    default:
      json_out(['error'=>'unknown action'], 400);
  }
} catch (Exception $e) {
  json_out(['error'=>$e->getMessage()], 500);
}
