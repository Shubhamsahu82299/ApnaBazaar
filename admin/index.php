<?php
session_name("admin_session");
session_start();

include('includes/config.php');

$error = '';
$success = '';

// =====================================
// 🔹 If already logged in → dashboard
// =====================================
if (isset($_SESSION['admin_login'])) {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// 🔹 AUTO LOGIN via Remember Me (Cookie)
// =====================================
if (!isset($_SESSION['admin_login']) && isset($_COOKIE['admin_auth'])) {
    list($selector, $validator) = explode(':', $_COOKIE['admin_auth']);

    $stmt = mysqli_prepare($conn, "SELECT admin_user, session_validator, session_expires 
                                   FROM admin_sessions 
                                   WHERE session_selector=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $selector);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $db_admin_user, $db_validator_hash, $db_expires);
        mysqli_stmt_fetch($stmt);

        if (strtotime($db_expires) > time() && password_verify($validator, $db_validator_hash)) {
            session_regenerate_id(true);
            $_SESSION['admin_login'] = $db_admin_user;
            header("Location: dashboard.php");
            exit;
        } else {
            // ❌ Expired or invalid cookie → clear it
            setcookie("admin_auth", "", time() - 3600, "/");
        }
    }
    mysqli_stmt_close($stmt);
}

// =====================================
// 🔹 Handle Login Form
// =====================================
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5(mysqli_real_escape_string($conn, $_POST['password'])); 

    $remember = isset($_POST['remember']); 

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    $admin = mysqli_fetch_array($query);

    if ($admin) {
        session_regenerate_id(true);
        $_SESSION['admin_login'] = $username;

        // 🔹 Save Persistent Login if "Remember Me" is checked
        if ($remember) {
            $selector = bin2hex(random_bytes(16));
            $validator = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+365 days')); 
            $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);

            // Old session cleanup
            $stmt_del = mysqli_prepare($conn, "DELETE FROM admin_sessions WHERE admin_user=?");
            mysqli_stmt_bind_param($stmt_del, 's', $username);
            mysqli_stmt_execute($stmt_del);
            mysqli_stmt_close($stmt_del);

            // Insert new session
            $stmt = mysqli_prepare($conn, "INSERT INTO admin_sessions 
                  (admin_user, session_selector, session_validator, session_expires) 
                  VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $username, $selector, $hashed_validator, $expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Secure cookie
            setcookie('admin_auth', "$selector:$validator", time()+60*60*24*365, "/", "", true, true);

            // LocalStorage backup (for one-time restore only)
            echo "<script>
                localStorage.setItem('admin_auth', '$selector:$validator');
            </script>";
        }

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "❌ Invalid username or password!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 30px 25px;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }
        .login-card h2 { margin-bottom: 20px; color: #333; }
        .login-card input {
            width: 100%; padding: 12px; margin: 8px 0;
            border-radius: 8px; border: 1px solid #ccc; font-size: 15px;
        }
        .login-card button {
            width: 100%; padding: 12px; margin-top: 15px;
            border-radius: 8px; border: none;
            background: #667eea; color: white; font-weight: bold; font-size: 16px;
            cursor: pointer; transition: background 0.3s;
        }
        .login-card button:hover { background: #5a67d8; }
        .remember { display: flex; align-items: center; margin: 10px 0; font-size: 14px; color: #444; }
        .remember input { margin-right: 8px; }
        .error { color: red; margin-top: 12px; font-size: 14px; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>🔐 Admin Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="👤 Username" required>
            <input type="password" name="password" placeholder="🔑 Password" required>
            
            <div class="remember">
                <input type="checkbox" id="rememberMe" name="remember" checked>
                <label for="rememberMe">Remember Me</label>
            </div>
            
            <button type="submit" name="login">Login</button>
        </form>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    </div>

    <!-- ✅ Cookie restore from localStorage (one-time only) -->
    <script>
        if(!document.cookie.includes("admin_auth") && localStorage.getItem("admin_auth")){
            let token = localStorage.getItem("admin_auth");
            document.cookie = "admin_auth=" + token + "; path=/; max-age=" + (60*60*24*365);
            localStorage.removeItem("admin_auth"); // 🔹 prevent infinite restore
            location.reload();
        }
    </script>
</body>
</html>
