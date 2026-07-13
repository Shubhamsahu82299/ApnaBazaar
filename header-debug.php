<?php
// Complete Error Reporting Enable karein taaki hidden issues visible hon
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);

session_start();
include('includes/config.php');

echo "<div style='padding: 24px; font-family: sans-serif; background: #0f172a; color: #f8fafc; border-radius: 12px; margin: 20px;'>";
echo "<h2 style='color: #38bdf8; border-bottom: 1px solid #334155; padding-bottom: 8px;'>🔧 ApnaBazaar Header Account Section Debugger</h2>";

// 1. Session Variables State Matrix Monitoring
echo "<h3>1. Session Matrix Check</h3>";
echo "<pre style='background: #1e293b; padding: 12px; border-radius: 6px; color: #34d399;'>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['login'])) {
    echo "<p style='color: #4ade80;'>✅ Session 'login' active hai: <strong>" . htmlspecialchars($_SESSION['login']) . "</strong></p>";
} else {
    echo "<p style='color: #f87171;'>❌ Session 'login' set nahi hai. (System Guest flow execute karega).</p>";
}

if (isset($_SESSION['username'])) {
    echo "<p style='color: #4ade80;'>✅ Session 'username' active hai: <strong>" . htmlspecialchars($_SESSION['username']) . "</strong></p>";
} else {
    echo "<p style='color: #fbbf24;'>⚠️ Session 'username' khali hai. Database configuration check karein.</p>";
}

// 2. Cookie Verification Matrix
echo "<h3>2. Auth Token Cookie Matrix</h3>";
if (isset($_COOKIE['auth_token'])) {
    echo "<p style='color: #4ade80;'>✅ Auto-Login Cookie 'auth_token' detected: <code>" . htmlspecialchars($_COOKIE['auth_token']) . "</code></p>";
} else {
    echo "<p style='color: #94a3b8;'>ℹ️ Koi 'auth_token' cookie set nahi hai (Remember Me active nahi hai).</p>";
}

// 3. Database Connection Sanity Probe
echo "<h3>3. DB Connection Link Probe</h3>";
if (isset($con) && $con instanceof mysqli) {
    if (mysqli_ping($con)) {
        echo "<p style='color: #4ade80;'>✅ Database active aur properly connected hai.</p>";
    } else {
        echo "<p style='color: #f87171;'>❌ Database variable set hai par ping response fail ho gaya: " . mysqli_error($con) . "</p>";
    }
} else {
    echo "<p style='color: #f87171;'>❌ Connection resource variable '\$con' invalid ya null hai. includes/config.php verified karein.</p>";
}

// 4. Live Component Execution Sandbox
echo "<h3>4. Component Live Rendering Sandbox</h3>";
echo "<div style='background: #f8fafc; padding: 20px; border: 2px dashed #38bdf8; border-radius: 8px; margin-top: 12px;'>";
?>

<!-- Live Execution Wrapper Sandbox Elements -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<div class="account-wrapper" style="border: 2px solid #ef4444; padding: 10px; display: flex !important; visibility: visible !important;">
<?php 
// Test logic verification parameters
if (isset($_SESSION['login']) && $_SESSION['login']) {
    $hour = date('H');
    if ($hour >= 5 && $hour < 12) { $greeting = "Good Morning"; $icon = "fas fa-sun"; $colorClass = "morning"; }
    elseif ($hour >= 12 && $hour < 17) { $greeting = "Good Afternoon"; $icon = "fas fa-leaf"; $colorClass = "afternoon"; }
    elseif ($hour >= 17 && $hour < 21) { $greeting = "Good Evening"; $icon = "fas fa-moon"; $colorClass = "evening"; }
    else { $greeting = "Good Night"; $icon = "fas fa-star"; $colorClass = "night"; }
?>
  <button class="account-toggle-btn <?php echo $colorClass; ?>" onclick="alert('Component click active!')" style="display: flex !important; visibility: visible !important;">
    <i class="<?php echo $icon; ?>"></i>
    <div class="text-container">
      <span class="greeting"><?php echo $greeting; ?> (SANDBOX)</span>
      <span class="username"><?php echo isset($_SESSION['username']) ? htmlentities($_SESSION['username']) : 'No Name Data'; ?></span>
    </div>
  </button>
<?php 
} else {
?>
  <a href="#" class="account-toggle-btn night" onclick="alert('Login Redirect Test')" style="display: flex !important; visibility: visible !important;">
    <i class="fas fa-user-circle"></i>
    <div class="text-container">
      <span class="greeting">Welcome (SANDBOX)</span>
      <span class="username">Login / Guest</span>
    </div>
  </a>
<?php 
}
?>
</div>

<style>
/* CSS Visibility Trace Injection */
.account-wrapper { display: flex !important; visibility: visible !important; justify-content: flex-end; position: relative; }
.account-toggle-btn { display: flex !important; visibility: visible !important; align-items: center; gap: 6px; padding: 2px 10px; width: 140px; height: 35px; border-radius: 10px; border: 1px solid #cbd5e1; background-color: #ffffff; cursor: pointer; text-decoration: none; }
.text-container { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.1; color: #1e293b; }
.greeting { font-size: 9px; font-weight: 600; color: #64748b; text-transform: uppercase; }
.username { font-size: 11px; font-weight: 700; color: #0f172a; }
.account-toggle-btn.morning i { color: #f59e0b; }
.account-toggle-btn.afternoon i { color: #10b981; }
.account-toggle-btn.evening i { color: #f43f5e; }
.account-toggle-btn.night i { color: #6366f1; }
</style>

<?php
echo "</div>";
echo "<p style='color: #94a3b8; font-size: 12px; margin-top: 15px;'>Upar agar <strong>Lal rang (Red border)</strong> ke andar button dikh raha hai toh front-end safe hai, check karein ki data sessions fetch ho rahe hain ya nahi.</p>";
echo "</div>";
?>