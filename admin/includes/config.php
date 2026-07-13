<?php
// dirname(__DIR__, 2) se hum 2 level upar root folder me aa gaye jahan .env file hai
$envFilePath = dirname(__DIR__, 2) . '/.env';

if (file_exists($envFilePath)) {
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Comments ko skip karne ke liye
        if (strpos(trim($line), '#') === 0) continue;

        // Key aur Value ko todte hain
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // Extra quotes/spaces hata rahe hain
        
        $_ENV[$name] = $value;
    }
} else {
    // 🧠 SMART BYPASS HOOK FOR VERCEL PRODUCTION (ADMIN PANEL)
    // Agar local .env nahi mili, toh Vercel ke variables ko $_ENV array me fill karo
    $cloud_vars = ['DB_SERVER', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT', 'DB_TIMEZONE', 'DB_OFFSET'];
    
    foreach ($cloud_vars as $var) {
        $val = getenv($var);
        if ($val !== false) {
            $_ENV[$var] = $val;
        }
    }
}

// Validation check taaki khali variables par crash na ho
if (!isset($_ENV['DB_SERVER']) || empty($_ENV['DB_SERVER'])) {
    die("Error: Production database configurations are missing in Admin Panel!");
}

// Session settings (Aapki session configurations)
// Agar session active NAHI hai, tabhi settings change karein
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 60*60*24*7); // 7 din
    ini_set('session.gc_maxlifetime', 60*60*24*7);
}

// ✨ CLEVER CLOUD LIVE DB CONNECTION WITH PORT SUPPORT (Object-Oriented) ✨
// .env se DB_PORT (3306) read kar rahe hain, agar nahi mila toh 3306 default backup rahega
$db_port = isset($_ENV['DB_PORT']) ? intval($_ENV['DB_PORT']) : 3306;

// new mysqli structure me port binding add kar di hai live connection drop bachane ke liye
$conn = new mysqli($_ENV['DB_SERVER'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME'], $db_port);

if ($conn->connect_error) {
    die("Live Clever Cloud Connection failed: " . $conn->connect_error);
}

// Live Server Timezone and Offset Configuration for Admin Panel
if (isset($_ENV['DB_TIMEZONE'])) {
    date_default_timezone_set($_ENV['DB_TIMEZONE']);
} else {
    date_default_timezone_set('Asia/Kolkata'); // Fallback default
}

if (isset($_ENV['DB_OFFSET'])) {
    $conn->query("SET time_zone = '" . $_ENV['DB_OFFSET'] . "'");
} else {
    $conn->query("SET time_zone = '+05:30'"); // Fallback default
}


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

        // Admin section ke liye "admin/productimages" nahi, sirf "productimages" path sahi hai
        return "productimages/" . $product_id . "/" . $db_image_value;
    }
}
?>