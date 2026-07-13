<?php
require_once __DIR__.'/db.php';
$action = $_GET['action'] ?? '';

function date_floor($col) {
  // MariaDB: DATE() works to floor timestamp to date
  return "DATE($col)";
}

try {
  switch ($action) {
    case 'top_products':
      $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
      $sql = "SELECT p.id, p.productName,
                     SUM(o.quantity) AS total_sold
              FROM orders o
              JOIN products p ON p.id = o.productId
              WHERE o.orderStatus='Delivered'
              GROUP BY p.id, p.productName
              ORDER BY total_sold DESC
              LIMIT :lim";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
      $stmt->execute();
      json_out($stmt->fetchAll());
      break;

    case 'revenue_by_product':
      $sql = "SELECT p.id, p.productName,
                     SUM(o.quantity * o.sell_price_at_order_time) AS revenue
              FROM orders o
              JOIN products p ON p.id = o.productId
              WHERE o.orderStatus='Delivered'
              GROUP BY p.id, p.productName
              ORDER BY revenue DESC";
      $stmt = $pdo->query($sql);
      json_out($stmt->fetchAll());
      break;

    case 'revenue_daily':
      $sql = "SELECT ".date_floor('o.orderDate')." AS day,
                     SUM(o.quantity * o.sell_price_at_order_time) AS revenue
              FROM orders o
              WHERE o.orderStatus='Delivered'
              GROUP BY day
              ORDER BY day ASC";
      $stmt = $pdo->query($sql);
      json_out($stmt->fetchAll());
      break;

    case 'profit_by_product':
      // Profit = (sell - buy) * qty . Use variant buy price if present else product base_buy_price
      $sql = "SELECT p.id, p.productName,
                     SUM( o.quantity * (o.sell_price_at_order_time - 
                         COALESCE(pv.variant_buy_price, p.base_buy_price, 0)
                         )
                        ) AS profit
              FROM orders o
              JOIN products p ON p.id = o.productId
              LEFT JOIN product_variants pv ON pv.id = o.variant_id
              WHERE o.orderStatus='Delivered'
              GROUP BY p.id, p.productName
              ORDER BY profit DESC";
      $stmt = $pdo->query($sql);
      json_out($stmt->fetchAll());
      break;

    case 'order_status_split':
      $stmt = $pdo->query("SELECT orderStatus, COUNT(*) AS total FROM orders GROUP BY orderStatus");
      json_out($stmt->fetchAll());
      break;

    case 'payment_split':
      $stmt = $pdo->query("SELECT paymentMethod, COUNT(*) AS total FROM orders GROUP BY paymentMethod");
      json_out($stmt->fetchAll());
      break;

    case 'top_customers':
      $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
      $sql = "SELECT o.userId,
                     COUNT(*) AS orders,
                     SUM(o.quantity * o.sell_price_at_order_time) AS total_spent
              FROM orders o
              WHERE o.orderStatus='Delivered'
              GROUP BY o.userId
              ORDER BY total_spent DESC
              LIMIT :lim";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
      $stmt->execute();
      json_out($stmt->fetchAll());
      break;

    default:
      json_out(['error'=>'unknown action'], 400);
  }
} catch (Exception $e) {
  json_out(['error'=>$e->getMessage()], 500);
}
