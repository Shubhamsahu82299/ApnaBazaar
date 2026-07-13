<?php
// ApnaBazaar app ka unique session name
session_name("ApnaBazaar_SESS");
session_start();

error_reporting(0);
include('includes/config.php');

// Persistent login: check cookie if session not set
if (!isset($_SESSION['login']) && isset($_COOKIE['auth_token'])) {
    list($selector, $validator) = explode(':', $_COOKIE['auth_token']);
    $stmt = mysqli_prepare($con, "SELECT * FROM auth_tokens WHERE selector = ? AND expires >= NOW()");
    mysqli_stmt_bind_param($stmt, 's', $selector);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $token = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($token && password_verify($validator, $token['validator'])) {
        // Token valid → login user
        $user_id = $token['user_id'];
        $user_query = mysqli_query($con, "SELECT * FROM users WHERE id = " . intval($user_id));
        $user = mysqli_fetch_array($user_query);

        if ($user) {
            $_SESSION['login'] = $user['email'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['name'];

            // Rotate token
            $new_selector = bin2hex(random_bytes(16));
            $new_validator = bin2hex(random_bytes(32));
            $new_hashed_validator = password_hash($new_validator, PASSWORD_DEFAULT);
            $new_expires = date('Y-m-d H:i:s', strtotime('+30 days'));

            mysqli_query($con, "DELETE FROM auth_tokens WHERE selector = '" . mysqli_real_escape_string($con, $selector) . "'");

            $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $user['id'], $new_selector, $new_hashed_validator, $new_expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            setcookie('auth_token', "$new_selector:$new_validator", time() + 60*60*24*30, "/", "", false, true);
        }
    } else {
        setcookie('auth_token', '', time() - 3600, "/");
    }
}

// === USER REGISTRATION ===
if (isset($_POST['submit'])) {
    $name = $_POST['fullname'];
    $email = $_POST['emailid'];
    $contactno = $_POST['contactno'];
    $password = md5($_POST['password']); // existing logic
    $remember_me = isset($_POST['remember_me']);

    $checkEmail = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        echo "<script>alert('Email already exists!');</script>";
    } else {
        $query = mysqli_query($con, "INSERT INTO users(name,email,contactno,password) VALUES('$name','$email','$contactno','$password')");
        if ($query) {
            $_SESSION['login'] = $email;
            $_SESSION['username'] = $name;
            $_SESSION['id'] = mysqli_insert_id($con);
            $uip = $_SERVER['REMOTE_ADDR'];
            $status = 1;
            mysqli_query($con, "INSERT INTO userlog(userEmail, userip, status) VALUES('$email','$uip','$status')");

            if ($remember_me) {
                $selector = bin2hex(random_bytes(16));
                $validator = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
                mysqli_query($con, "DELETE FROM auth_tokens WHERE user_id = " . intval($_SESSION['id']));
                $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'isss', $_SESSION['id'], $selector, $hashed_validator, $expires);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                setcookie('auth_token', "$selector:$validator", time() + 60*60*24*30, "/", "", false, true);
            }

            header("location:my-cart.php");
            exit();
        } else {
            echo "<script>alert('Not registered. Something went wrong.');</script>";
        }
    }
}

// === USER LOGIN ===
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $remember_me = isset($_POST['remember_me']);

    $query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' and password='$password'");
    $user = mysqli_fetch_array($query);

    if ($user > 0) {
        $_SESSION['login'] = $email;
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 1;
        mysqli_query($con, "INSERT INTO userlog(userEmail, userip, status) VALUES('$email','$uip','$status')");

        if ($remember_me) {
            $selector = bin2hex(random_bytes(16));
            $validator = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
            mysqli_query($con, "DELETE FROM auth_tokens WHERE user_id = " . intval($user['id']));
            $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $user['id'], $selector, $hashed_validator, $expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            setcookie('auth_token', "$selector:$validator", time() + 60*60*24*30, "/", "", false, true);
        }

        header("location:my-cart.php");
        exit();
    } else {
        $_SESSION['errmsg'] = "Invalid email or password";
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 0;
        mysqli_query($con, "INSERT INTO userlog(userEmail, userip, status) VALUES('$email','$uip','$status')");
        header("location:login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>ApnaBazaar | Login / Signup</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="shortcut icon" href="assets/images/title.png">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

  <style>
    * {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }
    body {
        background-color: #f8fafc !important;
        color: #1e293b;
    }
    .auth-card {
      max-width: 380px;
      margin: 60px auto;
      background: #ffffff;
      padding: 30px 24px;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
    }
    .auth-tabs {
      display: flex;
      justify-content: space-between;
      margin-bottom: 24px;
      background: #f1f5f9;
      padding: 4px;
      border-radius: 10px;
    }
    .auth-tabs button {
      flex: 1;
      background: none;
      border: none;
      font-size: 14px;
      font-weight: 700;
      padding: 8px;
      color: #64748b;
      border-radius: 8px;
      transition: all 0.2s ease;
    }
    .auth-tabs button.active {
      background: #ffffff;
      color: #0d9488;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .form-control {
      font-size: 14px;
      font-weight: 500;
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid #cbd5e1;
      color: #0f172a;
      background-color: #ffffff;
    }
    .form-control:focus {
      border-color: #0d9488;
      box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
      background-color: #ffffff;
    }
    .form-label, label {
      font-weight: 600;
      font-size: 13px;
      color: #475569;
      margin-bottom: 6px;
    }
    .form-check-input:checked {
        background-color: #0d9488;
        border-color: #0d9488;
    }
    .btn-primary-accent {
        background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
        color: #ffffff;
        border: none;
        font-weight: 700;
        font-size: 14px;
        padding: 11px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
        transition: opacity 0.2s;
    }
    .btn-primary-accent:hover {
        opacity: 0.95;
        color: #ffffff;
    }
    .btn-outline-custom {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    .btn-outline-custom:hover {
        background: #f8fafc;
        color: #0f172a;
    }
    .text-link-accent {
        color: #0d9488;
        font-weight: 600;
        text-decoration: none;
    }
    .text-link-accent:hover {
        color: #059669;
        text-decoration: underline;
    }
    @media(max-width:576px) {
      .auth-card {
        margin: 24px auto;
        padding: 20px 16px;
        border-radius: 12px;
      }
    }
  </style>
</head>
<body class="cnt-home">

<?php include('includes/top-header.php'); ?>

<div class="container">
  <div class="auth-card">
    <div class="auth-tabs">
      <button class="active" id="loginTab" onclick="toggleTab('login')">Login</button>
      <button id="signupTab" onclick="toggleTab('signup')">Sign Up</button>
    </div>

    <!-- Login Form -->
    <form method="post" id="loginForm">
      <div class="mb-2 text-danger small fw-semibold">
        <?php echo htmlentities($_SESSION['errmsg']); $_SESSION['errmsg'] = ""; ?>
      </div>
      <h5 class="mb-3 text-center fw-bold text-dark" style="font-size: 16px;">Welcome Back</h5>
      
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required />
      </div>
      
      <div class="mb-2">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" maxlength="6" required />
      </div>

      <!-- Show/hide checkbox -->
      <div class="mb-3 form-check">
        <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword('password')" />
        <label class="form-check-label select-none" for="showPassword" style="font-weight: 500; color: #64748b;">Show Password</label>
      </div>

      <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="form-check mb-0">
          <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me" checked>
          <label class="form-check-label select-none" for="rememberMe" style="font-weight: 500; color: #64748b;">Remember Me</label>
        </div>
        <div>
          <a href="forgot-password.php" class="text-link-accent small">Forgot password?</a>
        </div>
      </div>
      
      <button type="submit" name="login" class="btn btn-primary-accent w-100">Login</button>
    </form>

    <!-- Signup Form -->
    <form method="post" name="register" id="signupForm" style="display:none;" onsubmit="return validatePassword();">
      <h5 class="mb-3 text-center fw-bold text-dark" style="font-size: 16px;">Create Account</h5>
      
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" required />
      </div>
      
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="emailid" id="email" class="form-control" onblur="userAvailability()" required />
        <span id="user-availability-status1" class="small d-block mt-1"></span>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Contact No.</label>
        <input type="text" name="contactno" id="contactno" class="form-control" maxlength="10" required inputmode="numeric" pattern="[0-9]*" placeholder="Enter 10-digit number" />
        <span id="contactMsg" class="text-danger small mt-1" style="display:none;">Digits only (max 10)</span>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password" id="signupPass" class="form-control" style="border-top-right-radius:0; border-bottom-right-radius:0;" maxlength="6" required />
          <button type="button" class="btn btn-outline-custom" onclick="togglePasswordBtn('signupPass')">Show</button>
        </div>
        <span id="passLengthMsg" class="text-danger small d-block mt-1" style="display:none;">Maximum 6 digits allowed</span>
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <input type="password" name="confirmpassword" id="confirmPass" class="form-control" style="border-top-right-radius:0; border-bottom-right-radius:0;" maxlength="6" required />
          <button type="button" class="btn btn-outline-custom" onclick="togglePasswordBtn('confirmPass')">Show</button>
        </div>
      </div>

      <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="signupRememberMe" name="remember_me" checked>
        <label class="form-check-label select-none" for="signupRememberMe" style="font-weight: 500; color: #64748b;">Remember Me</label>
      </div>
      
      <button type="submit" name="submit" class="btn btn-primary-accent w-100">Sign Up</button>
    </form>
  </div>
</div>

<?php include('includes/brands-slider.php'); ?>
<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
  function togglePassword(id) {
    const passwordInput = document.getElementById(id);
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
  }

  function togglePasswordBtn(id) {
    const input = document.getElementById(id);
    const btn = input.nextElementSibling;
    if (input.type === "password") {
      input.type = "text";
      btn.textContent = "Hide";
    } else {
      input.type = "password";
      btn.textContent = "Show";
    }
  }

  const contactInput = document.getElementById('contactno');
  const contactMsg = document.getElementById('contactMsg');

  if(contactInput) {
    contactInput.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '');
      if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
        contactMsg.style.display = 'inline';
      } else {
        contactMsg.style.display = 'none';
      }
    });
  }

  const passwordInput = document.getElementById('signupPass');
  const message = document.getElementById('passLengthMsg');

  if(passwordInput) {
    passwordInput.addEventListener('input', function () {
      if (this.value.length > 6) {
        message.style.display = 'inline';
        this.value = this.value.slice(0, 6);
      } else {
        message.style.display = 'none';
      }
    });
  }

function toggleTab(tab) {
  if (tab === 'login') {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('signupTab').classList.remove('active');
  } else {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'block';
    document.getElementById('loginTab').classList.remove('active');
    document.getElementById('signupTab').classList.add('active');
  }
}

function validatePassword() {
  const pass = document.getElementById('signupPass').value;
  const confirm = document.getElementById('confirmPass').value;
  if (pass !== confirm) {
    alert("Passwords do not match.");
    return false;
  }
  return true;
}

function userAvailability() {
  jQuery.ajax({
    url: "check_availability.php",
    data: 'email=' + $("#email").val(),
    type: "POST",
    success: function(data){
      $("#user-availability-status1").html(data);
    },
    error: function () {}
  });
}

function getCookie(name) {
    const value = "; " + document.cookie;
    const parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
}

function saveAuthToken(token) {
    document.cookie = "auth_token=" + token + "; path=/; max-age=" + (30*24*60*60) + "; secure; samesite=strict";
    localStorage.setItem("auth_token", token);
}

function restoreAuthToken() {
    if (!getCookie("auth_token") && localStorage.getItem("auth_token")) {
        document.cookie = "auth_token=" + localStorage.getItem("auth_token") + "; path=/; max-age=" + (30*24*60*60) + "; secure; samesite=strict";
        console.log("🔄 Auth token restored from localStorage");
        location.reload();
    }
}

function clearAuthToken() {
    document.cookie = "auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    localStorage.removeItem("auth_token");
}
</script>

</body>
</html>