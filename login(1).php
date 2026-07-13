<?php
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
        // Token is valid, log the user in
        $user_id = $token['user_id'];
        $user_query = mysqli_query($con, "SELECT * FROM users WHERE id = " . intval($user_id));
        $user = mysqli_fetch_array($user_query);
        if ($user) {
            $_SESSION['login'] = $user['email'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            // Optionally, rotate token for security
            $new_selector = bin2hex(random_bytes(16));
            $new_validator = bin2hex(random_bytes(32));
            $new_hashed_validator = password_hash($new_validator, PASSWORD_DEFAULT);
            $new_expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            // Remove old token
            mysqli_query($con, "DELETE FROM auth_tokens WHERE selector = '" . mysqli_real_escape_string($con, $selector) . "'");
            // Store new token
            $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $user['id'], $new_selector, $new_hashed_validator, $new_expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            setcookie('auth_token', "$new_selector:$new_validator", time() + 60*60*24*30, "/", "", false, true);
        }
    } else {
        // Invalid token, remove cookie
        setcookie('auth_token', '', time() - 3600, "/");
    }
}

// === USER REGISTRATION ===
if (isset($_POST['submit'])) {
    $name = $_POST['fullname'];
    $email = $_POST['emailid'];
    $contactno = $_POST['contactno'];
    $password = md5($_POST['password']); // OLD method to match existing logic
    $remember_me = isset($_POST['remember_me']);

    // Check if email already exists
    $checkEmail = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        echo "<script>alert('Email already exists! Please use a different email or login with existing account.');</script>";
    } else {
        $query = mysqli_query($con, "INSERT INTO users(name,email,contactno,password) VALUES('$name','$email','$contactno','$password')");
        if ($query) {
            $_SESSION['login'] = $email;
            $_SESSION['username'] = $name;
            $_SESSION['id'] = mysqli_insert_id($con);
            $uip = $_SERVER['REMOTE_ADDR'];
            $status = 1;
            mysqli_query($con, "INSERT INTO userlog(userEmail, userip, status) VALUES('$email','$uip','$status')");
            // Persistent login logic for signup
            if ($remember_me) {
                $selector = bin2hex(random_bytes(16));
                $validator = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
                // Remove old tokens for this user
                mysqli_query($con, "DELETE FROM auth_tokens WHERE user_id = " . intval($_SESSION['id']));
                // Store new token
                $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'isss', $_SESSION['id'], $selector, $hashed_validator, $expires);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                // Set cookie
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

        // Persistent login logic
        if ($remember_me) {
            $selector = bin2hex(random_bytes(16));
            $validator = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
            // Remove old tokens for this user
            mysqli_query($con, "DELETE FROM auth_tokens WHERE user_id = " . intval($user['id']));
            // Store new token
            $stmt = mysqli_prepare($con, "INSERT INTO auth_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $user['id'], $selector, $hashed_validator, $expires);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // Set cookie
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
  <style>
    .auth-card {
      max-width: 360px;
      margin: 40px auto;
      background: #fff;
      padding: 25px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .auth-tabs {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .auth-tabs button {
      flex: 1;
      background: none;
      border: none;
      font-weight: bold;
      padding: 10px;
      border-bottom: 2px solid transparent;
    }
    .auth-tabs button.active {
      border-bottom: 2px solid #007bff;
      color: #007bff;
    }
    .form-control {
      font-size: 14px;
    }
    .form-label {
      font-weight: 500;
    }
    @media(max-width:576px){
      .auth-card {
        margin-top: 25px;
        padding: 20px 15px;
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
      <div class="mb-2 text-danger small">
        <?php echo htmlentities($_SESSION['errmsg']); $_SESSION['errmsg'] = ""; ?>
      </div>
      <h5 class="mb-3 text-center">Existing User</h5>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required />
      </div>
      <div class="mb-3">
  <label for="password">Password</label>
  <input type="password" id="password" name="password" class="form-control"  maxlength="6" required />
  
  <div class="form-check mt-2">

   
  </div>
</div>
<!-- Password input -->


<!-- Show/hide checkbox -->
<div class="form-check mt-2">
  <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword('password')" />
  <label class="form-check-label" for="showPassword">Show Password</label>
</div>

<script>
  function togglePassword(id) {
    const passwordInput = document.getElementById(id);
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
  }
</script>

<script>
  function togglePassword("login") {
    const passwordInput = document.getElementById("password");
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
  }
</script>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me" checked>
        <label class="form-check-label" for="rememberMe">Remember Me</label>
      </div>
      <div class="mb-3 text-end">
        <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
      </div>
      <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>

    <!-- Signup Form -->
    
    <form method="post" name="register" id="signupForm" style="display:none;" onsubmit="return validatePassword();">
         <h5 class="mb-3 text-center">New User</h5>
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="emailid" id="email" class="form-control" onblur="userAvailability()" required />
        <span id="user-availability-status1" class="small"></span>
      </div>
      <div class="mb-3">
        <label class="form-label">Contact No.</label>
       <input type="text" name="contactno" id="contactno" class="form-control" maxlength="10" required inputmode="numeric" pattern="[0-9]*" placeholder="Enter 10-digit number" />

<span id="contactMsg" class="text-danger small" style="display:none;">Digits only (max 10)</span>

<script>
  const contactInput = document.getElementById('contactno');
  const contactMsg = document.getElementById('contactMsg');

  contactInput.addEventListener('input', function () {
    // Remove all non-digit characters
    this.value = this.value.replace(/\D/g, '');

    // Show error message if user tries to type more than 10 digits
    if (this.value.length > 10) {
      this.value = this.value.slice(0, 10);
      contactMsg.style.display = 'inline';
    } else {
      contactMsg.style.display = 'none';
    }
  });
</script>

      </div>
     <div class="mb-3">
  <label class="form-label">Password</label>
  <div class="input-group">
    <input type="password" name="password" id="signupPass" class="form-control" maxlength="6" < required />
      <span id="passLengthMsg" class="text-danger small" style="display:none;">Maximum 6 digits allowed</span>
    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('signupPass')">Show</button>
  </div>
</div>
<script>
  const passwordInput = document.getElementById('signupPass');
  const message = document.getElementById('passLengthMsg');

  passwordInput.addEventListener('input', function () {
    if (this.value.length > 6) {
      message.style.display = 'inline';
      this.value = this.value.slice(0, 6);  // cut off extra characters
    } else {
      message.style.display = 'none';
    }
  });
</script>

<div class="mb-3">
  <label class="form-label">Confirm Password</label>
  <div class="input-group">
    <input type="password" name="confirmpassword" id="confirmPass" class="form-control" maxlength="6"  required />
    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmPass')">Show</button>
  </div>
</div>

<script>
  function togglePassword(id) {
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


</script>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="signupRememberMe" name="remember_me" checked>
        <label class="form-check-label" for="signupRememberMe">Remember Me</label>
      </div>
      <button type="submit" name="submit" class="btn btn-primary w-100">Sign Up</button>
    </form>
  </div>
</div>

<?php include('includes/brands-slider.php'); ?>
<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
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

</script>
</body>
</html>
