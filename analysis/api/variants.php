<?php
require_once __DIR__.'/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];

try {
  if ($method === 'GET') {
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    if ($product_id) {
      $stmt = $pdo->prepare("SELECT id, product_id, variant_label, price, stock, variant_buy_price FROM product_variants WHERE product_id=? ORDER BY id ASC");
      $stmt->execute([$product_id]);
      json_out($stmt->fetchAll());
    } else {
      $stmt = $pdo->query("SELECT id, product_id, variant_label, price, stock, variant_buy_price FROM product_variants ORDER BY id DESC LIMIT 100");
      json_out($stmt->fetchAll());
    }
  }
  elseif ($method === 'POST') {
    // Create
    $product_id = (int)($input['product_id'] ?? 0);
    $label = trim($input['variant_label'] ?? '');
    $price = (float)($input['price'] ?? 0);
    $stock = (int)($input['stock'] ?? 0);
    $buy  = (float)($input['variant_buy_price'] ?? 0);
    if (!$product_id || $label==='') json_out(['error'=>'product_id and variant_label required'], 400);
    $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, variant_label, price, stock, variant_buy_price) VALUES (?,?,?,?,?)");
    $stmt->execute([$product_id, $label, $price, $stock, $buy]);
    json_out(['ok'=>true, 'id'=>$pdo->lastInsertId()], 201);
  }
  elseif ($method === 'PUT' || $method === 'PATCH') {
    // Update inline
    $id = (int)($input['id'] ?? 0);
    if (!$id) json_out(['error'=>'id required'], 400);
    $fields = [];
    $params = [];
    foreach (['variant_label','price','stock','variant_buy_price'] as $col) {
      if (array_key_exists($col, $input)) {
        $fields[] = "$col = ?";
        $params[] = $input[$col];
      }
    }
    if (empty($fields)) json_out(['error'=>'no fields to update'], 400);
    $params[] = $id;
    $sql = "UPDATE product_variants SET ".implode(', ', $fields)." WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_out(['ok'=>true]);
  }
  elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($input['id'] ?? 0);
    if (!$id) json_out(['error'=>'id required'], 400);
    $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id=?");
    $stmt->execute([$id]);
    json_out(['ok'=>true]);
  } else {
    json_out(['error'=>'Unsupported method'], 405);
  }
} catch (Exception $e) {
  json_out(['error'=>$e->getMessage()], 500);
}
