<?php
// =========================================================================
// 🔒 UNIFIED SESSION SECURITY ARCHITECTURE (Fixes Infinite Logout Loop)
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    // Force a unique unified session identifier name across all routes & background polls
    session_name("ApnaBazaar_SESS");
    
    // Modern secure cookie parameters profile config mapping
    ini_set('session.cookie_lifetime', 60*60*24*30); // 30 Days
    ini_set('session.gc_maxlifetime', 60*60*24*30);
    ini_set('session.cookie_httponly', true);       // Blocks Cross-Site Scripting cookie steals
    ini_set('session.use_only_cookies', true);
    
    // Dynamic fallback matrix tracking deployment protocols (Auto-detects Local HTTP vs Live HTTPS)
    $is_secure_conn = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1) 
                      || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 60*60*24*30,
        'path' => '/',
        'domain' => '',
        'secure' => $is_secure_conn, // Auto sets true on production HTTPS, false on local http://localhost
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// .env file ka path detect kar rahe hain (Change path if needed)
$envFilePath = dirname(__DIR__) . '/.env';

if (file_exists($envFilePath)) {
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Comments ko skip karne ke liye
        if (strpos(trim($line), '#') === 0) continue;

        // Key aur Value ko alag karne ke liye
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'"); // Quotes ko trim kar rahe hain
            
            // Environment variables aur Constants dono me set kar rahe hain
            if (!defined($name)) {
                define($name, $value);
            }
            $_ENV[$name] = $value;
        }
    }
} else {
    // 🧠 SMART BYPASS HOOK FOR VERCEL PRODUCTION PRODUCTION
    // Agar local .env nahi mili, toh cloud engine ke variables ko constants me define karo
    $cloud_vars = ['DB_SERVER', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT', 'DB_TIMEZONE', 'DB_OFFSET'];
    
    foreach ($cloud_vars as $var) {
        $val = getenv($var);
        if ($val !== false && !defined($var)) {
            define($var, $val);
        }
    }
}

// Check karne ke liye ki constants define hue ya nahi (live failsafe verification)
if (!defined('DB_SERVER')) {
    die("Error: Production database configurations are missing!");
}

// ✨ CLEVER CLOUD LIVE DB CONNECTION WITH PORT SUPPORT ✨
// .env se milne wale port (3306) ko fetch karke integer me parse kar rahe hain
$db_port = defined('DB_PORT') ? intval(DB_PORT) : 3306;

// Five-parameter structure use kar rahe hain connection timeout handle karne ke liye
$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME, $db_port);

// Check connection safely using die execution flag
if (mysqli_connect_errno()) {
    die("Live Clever Cloud MySQL Connection Failed: " . mysqli_connect_error());
}

// Timezone Configuration
date_default_timezone_set(defined('DB_TIMEZONE') ? DB_TIMEZONE : 'Asia/Kolkata');
$db_offset = defined('DB_OFFSET') ? DB_OFFSET : '+05:30';
mysqli_query($con, "SET time_zone = '" . $db_offset . "'");


// =========================================================================
// ✨ GLOBAL IMAGE RESOLVER ENGINE (Hybrid Flow Integration) ✨
// =========================================================================
if (!function_exists('getProductImage')) {
    function getProductImage($product_id, $db_image_value) {
        // Agar database column khali hai toh empty placeholder bypass karein
        if (empty($db_image_value)) {
            return "https://via.placeholder.com/300?text=No+Image";
        }

        // Agar database row matrix me live Cloudinary URL already patch ho chuka hai:
        if (strpos($db_image_value, 'https://res.cloudinary.com') === 0) {
            return $db_image_value; // Direct Cloudinary CDN Network URL pass karein
        }

        // Agar abhi tak purana plain text file mapping save hai
        return "admin/productimages/" . $product_id . "/" . $db_image_value;
    }
}
?>