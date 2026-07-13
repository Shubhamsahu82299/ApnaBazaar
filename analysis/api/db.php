<?php
// api/db.php
// ---- Database connection (edit credentials below) ----
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'u814646522_ApnaBazaars';
$DB_USER = getenv('DB_USER') ?: 'u814646522_ApnaBazaarspss';
$DB_PASS = getenv('DB_PASS') ?: 'ApnaBazaar967';

// Enable CORS (adjust in production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// JSON helpers
function json_out($data, $code=200) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

try {
  $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Exception $e) {
  json_out(['error' => 'DB connection failed', 'details' => $e->getMessage()], 500);
}
?>
