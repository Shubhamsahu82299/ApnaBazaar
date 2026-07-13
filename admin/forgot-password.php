<?php
include('includes/config.php');

$error = '';
$success = '';

if (isset($_POST['change_password'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Validations
    if (empty($username) || empty($new_pass) || empty($confirm_pass)) {
        $error = "❌ Saare fields bharna zaroori hai!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "❌ Dono password aapas me match nahi ho rahe hain!";
    } else {
        // Check karein ki yeh admin user database me exist karta bhi hai ya nahi
        $check_user = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
        
        if (mysqli_num_rows($check_user) > 0) {
            // MD5 Encryption (Aapki login file ke algorithm ke mutabik)
            $hashed_password = md5(mysqli_real_escape_string($conn, $new_pass));

            // Direct Update Query
            $stmt = mysqli_prepare($conn, "UPDATE admin SET password=? WHERE username=?");
            mysqli_stmt_bind_param($stmt, 'ss', $hashed_password, $username);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "🎉 Password successfully update ho gaya hai! <br><br> <a href='login.php' style='color:#667eea; font-weight:bold;'>Yahan click karke Login karein</a>";
            } else {
                $error = "❌ Database update fail ho gaya. Kripya dobara try karein.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "❌ Yeh Username ('$username') system me nahi mila!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Direct Password Reset</title>
    <style>
        body { font-family: "Segoe UI", sans-serif; background: linear-gradient(135deg, #111827, #1f2937); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 30px 25px; width: 100%; max-width: 400px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); text-align: center; }
        h2 { margin-top: 0; color: #111827; }
        input { width: 100%; padding: 12px; margin: 8px 0; border-radius: 8px; border: 1px solid #d1d5db; font-size: 15px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; margin-top: 15px; border-radius: 8px; border: none; background: #2563eb; color: white; font-weight: bold; font-size: 16px; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; margin-top: 12px; font-size: 14px; font-weight: 500; }
        .success { color: #16a34a; margin-top: 12px; font-size: 15px; background: #f0fdf4; padding: 15px; border-radius: 6px; border: 1px solid #bbf7d0; line-height: 1.4; }
        .back-link { display: block; margin-top: 20px; font-size: 14px; color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🛠️ Direct Password Overwrite</h2>
        <p style="color:#6b7280; font-size:13px; margin-bottom:20px;">Username daaliye aur seedha naya password set kijiye.</p>
        
        <form method="post">
            <input type="text" name="username" placeholder="👤 Admin Username (e.g., admin)" required>
            <input type="password" name="new_password" placeholder="🔑 Enter New Password" required minlength="4">
            <input type="password" name="confirm_password" placeholder="🔄 Confirm New Password" required minlength="4">
            <button type="submit" name="change_password">Force Update Password</button>
        </form>

        <?php 
            if ($error) echo "<div class='error'>$error</div>"; 
            if ($success) echo "<div class='success'>$success</div>"; 
        ?>
        <a href="login.php" class="back-link">← Back to Login</a>
    </div>
</body>
</html>