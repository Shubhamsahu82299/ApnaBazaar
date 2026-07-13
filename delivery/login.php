<?php
session_start();
include_once('config.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id FROM delivery_users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password); // Note: For real apps, always hash passwords
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($deliveryId);
        $stmt->fetch();
        $_SESSION['delivery_login'] = true;
        $_SESSION['delivery_id'] = $deliveryId;
        header("Location: manage-orders.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container {
            background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px;
        }
        h2 { margin-bottom: 20px; text-align: center; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc;
        }
        button {
            width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px;
        }
        .error { color: red; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Delivery Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
