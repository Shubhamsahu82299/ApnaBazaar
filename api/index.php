<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = trim($requestUri, '/');

if (strpos($requestUri, 'api/') === 0) {
    $requestUri = substr($requestUri, 4);
}

if ($requestUri === 'api/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

if ($requestUri === '' || $requestUri === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

$targetFile = realpath(__DIR__ . '/../' . $requestUri);

// ================================================================
// 🛠️ SMART INTEGRATION: Auto-append .php if file is not found directly
// ================================================================
if ((!$targetFile || !file_exists($targetFile)) && substr($requestUri, -4) !== '.php') {
    $guessedFile = realpath(__DIR__ . '/../' . $requestUri . '.php');
    if ($guessedFile && file_exists($guessedFile) && is_file($guessedFile)) {
        $targetFile = $guessedFile;
        $requestUri .= '.php'; // Internal URL structure match karne ke liye
    }
}
// ================================================================

if ($targetFile && file_exists($targetFile) && is_file($targetFile)) {
    $_SERVER['SCRIPT_FILENAME'] = $targetFile;
    $_SERVER['PHP_SELF'] = '/' . $requestUri;
    require $targetFile;
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>The requested route <b>/" . htmlspecialchars($requestUri) . "</b> does not exist.</p>";
}