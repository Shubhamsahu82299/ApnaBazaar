<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Set session configuration for better persistence
ini_set('session.cookie_lifetime', 60*60*24*30); // 30 days
ini_set('session.gc_maxlifetime', 60*60*24*30); // 30 days
ini_set('session.cookie_secure', false); // Set to true if using HTTPS
ini_set('session.cookie_httponly', true);
ini_set('session.use_only_cookies', true);

if (isset($_POST['change'])) {
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $password = md5($_POST['password']);
    $remember_me = isset($_POST['remember_me']);

    $query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND contactno='$contact'");
    $num = mysqli_fetch_array($query);

    if ($num > 0) {
        // Update password
        mysqli_query($con, "UPDATE users SET password='$password' WHERE email='$email' AND contactno='$contact'");

        // AUTO LOGIN - Set all required session variables
        $_SESSION['login'] = $email;
        $_SESSION['username'] = $num['name'];
        $_SESSION['id'] = $num['id'];
        
        // Force session write
        session_write_close();
        session_start();

        // Insert login log
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 1;
        mysqli_query($con, "INSERT INTO userlog(userEmail, userip, status) VALUES('$email', '$uip', '$status')");

        // Persistent login logic for password change
        if ($remember_me) {
            $selector = bin2hex(random_bytes(16));
            $validator = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
            // Remove old tokens for this user
            mysqli_query($con, "DELETE FROM auth_tokens WHERE user_id = " . intval($num['id']));
            // Store new token
            $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $num['id'], $selector, $hashed_validator, $expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // Set cookie with proper parameters
            setcookie('auth_token', "$selector:$validator", time() + 60*60*24*30, "/", "", false, true);
        }

        // Redirect to my-account.php
        header("Location: my-account.php");
        exit();
    } else {
        $_SESSION['errmsg'] = "Invalid Email or Contact No";
        header("Location: forgot-password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ApnaBazaar | Forgot Password</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" />
    <link href="assets/css/main.css" rel="stylesheet" />
   

    <style>
     body {
            
            padding-top: 50px;
        }
        @media (max-width: 768px) {
  body {
    padding-top: 90px; /* mobile header height */
  }
}
        .forgot-container {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            padding: 25px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .forgot-container h4 {
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            font-size: 14px;
        }

        .msg-box {
            margin-bottom: 15px;
            text-align: center;
            color: red;
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .forgot-container {
                margin-top: 25px;
                padding: 20px 15px;
            }
        }
    </style>

    <script>
        function valid() {
            const pass = document.getElementById("password").value;
            const confirm = document.getElementById("confirmpassword").value;
            if (pass !== confirm) {
                alert("Password and Confirm Password do not match!");
                document.getElementById("confirmpassword").focus();
                return false;
            }
            return true;
        }

        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const confirmPasswordInput = document.getElementById("confirmpassword");
            const showPasswordCheckbox = document.getElementById("showPassword");
            
            if (showPasswordCheckbox.checked) {
                passwordInput.type = "text";
                confirmPasswordInput.type = "text";
            } else {
                passwordInput.type = "password";
                confirmPasswordInput.type = "password";
            }
        }
    </script>
</head>
<body class="cnt-home">



<div class="container">
    <div class="forgot-container">
        <h4>Forgot Password</h4>

        <?php if (!empty($_SESSION['errmsg'])): ?>
            <div class="msg-box"><?php echo htmlentities($_SESSION['errmsg']); $_SESSION['errmsg'] = ""; ?></div>
        <?php endif; ?>

        <form method="post" name="register" onsubmit="return valid();">
            <div class="mb-3">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required />
            </div>

            <div class="mb-3">
                <label class="form-label">Contact No <span class="text-danger">*</span></label>
                <input type="text" name="contact" class="form-control" maxlength="10" required />
            </div>

            <div class="mb-3">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control" maxlength="6" required />
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="confirmpassword" id="confirmpassword" class="form-control" maxlength="6" required />
            </div>

            <!-- Show/hide password checkbox -->
            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()" />
                <label class="form-check-label" for="showPassword">Show Password</label>
            </div>

            <!-- Remember Me checkbox -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me" checked>
                <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>

            <button type="submit" name="change" class="btn btn-primary w-100">Change Password</button>
        </form>
    </div>
</div>

<?php include('includes/brands-slider.php'); ?>
<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>