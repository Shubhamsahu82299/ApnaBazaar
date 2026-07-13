<?php 
session_start();
error_reporting(0);
include('includes/config.php');

if (empty($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['submit'])) {
    if (isset($_POST['paymethod'], $_SESSION['id'])) {
        $paymethod = $_POST['paymethod'];
        $userId = $_SESSION['id'];

        $stmt = $con->prepare("UPDATE orders SET paymentMethod=? WHERE userId=? AND paymentMethod IS NULL");
        $stmt->bind_param("si", $paymethod, $userId);
        if ($stmt->execute()) {
           // --- Email code start ---
    // Get latest order for this user with paymentMethod just set
    $order_query = mysqli_query($con, "SELECT * FROM orders WHERE userId=$userId AND paymentMethod='$paymethod' ORDER BY orderDate DESC LIMIT 1");
    $order = mysqli_fetch_assoc($order_query);

    $user_query = mysqli_query($con, "SELECT * FROM users WHERE id=$userId");
    $user = mysqli_fetch_assoc($user_query);

    $to = "ApnaBazaar";
    $subject = "New Order Placed: #" . $order['id'];
    $message = "New order placed!\n\n";
    $message .= "Order ID: " . $order['id'] . "\n";
    $message .= "User Name: " . $user['name'] . "\n";
    $message .= "User Email: " . $user['email'] . "\n";
    $message .= "Order Amount: " . $order['totalAmount'] . "\n";
    $message .= "Order Date: " . $order['orderDate'] . "\n";
    $message .= "Payment Method: " . $order['paymentMethod'] . "\n";
    // Add more details if needed

    mail($to, $subject, $message);
    mail($to2, $subject, $message);
    // --- Email code end ---

            header('Location: order-history.php');
            exit;
        } else {
            $error = "Error updating payment method. Please try again.";
        }
    } else {
        $error = "Payment method or user ID missing.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ApnaBazaar | Payment Method</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body style="margin:; padding:0; background:#f5f5f7; font-family: 'Roboto', sans-serif; overflow-x:hidden;">

<?php include('includes/top-header.php'); ?>
<?php include('includes/main-header.php'); ?>
<?php include('includes/menu-bar.php'); ?>

<!-- Main Container -->
<div style="max-width:480px; width:100%; margin:100px auto 10px auto; background:#fff; padding:30px 15px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.1); box-sizing:border-box;">

  <h2 style="
  text-align: center;
  font-size: 1rem;
  font-weight: 500;
  color: #333;
  position: relative;
  display: inline-block;
  margin: 30px auto 40px;
  padding-bottom: 10px;
  border-bottom: 3px solid #ff6f00;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  ">
  💳 Choose Payment Method
</h2>


  <?php if (!empty($error)): ?>
      <div style="background:#f8d7da; color:#842029; padding: 12px 20px; border-radius: 5px; margin-bottom: 20px; border:1px solid #f5c2c7;">
          <?= htmlspecialchars($error) ?>
      </div>
  <?php endif; ?>

  <form method="post" name="paymentForm" id="paymentForm" novalidate style="width:100%; box-sizing:border-box;">

      <div style="border:1px solid #ddd; border-radius:6px; padding:20px; margin-bottom:16px; cursor:pointer; display:flex; align-items:center; gap:16px; transition:border-color 0.3s ease;">
          <input 
              type="radio" 
              id="cod" 
              name="paymethod" 
              value="COD" 
              checked 
              required
              style="display:none;"
          />
          <label for="cod" 
              style="flex-grow:1; display:flex; align-items:center; cursor:pointer; user-select:none; gap:16px; font-size:1.1rem; word-break: break-word;">
              <i class="fa-solid fa-money-bill-wave" style="font-size:1.8rem; color:#007bff; min-width:30px;"></i>
              Cash On Delivery 
              <small style="color:#6c757d; margin-left:8px; font-weight:400;">(Pay in cash or via QR)</small>
          </label>
      </div>

    <button 
  type="submit" 
  name="submit" 
  id="placeOrderBtn"
  style="
    width: 100%;
    background-color: #0d6efd;
    border: none;
    color: #fff;
    padding: 12px 0;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s ease;
    position: relative;
    overflow: hidden;
  "
  onmouseover="if(!this.disabled) this.style.backgroundColor='#0056d2'"
  onmouseout="if(!this.disabled) this.style.backgroundColor='#0d6efd'"
  onclick="handleOrderSubmit(event)"
>
  <span id="btnText">Place Order</span>
  <span 
    id="btnSpinner"
    style="
      display: none;
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    "
  >
    <i class="fa fa-spinner fa-spin" style="font-size: 1.3rem;"></i>
  </span>
</button>
  </form>
</div>

<!-- Footer -->
<div style=""><?php include('includes/footer.php'); ?></div>

<script>
  // Highlight selected payment method on change
  const paymentOptions = document.querySelectorAll('input[name="paymethod"]');
  paymentOptions.forEach(option => {
      option.addEventListener('change', () => {
          document.querySelectorAll('div[style*="border"]').forEach(div => {
              div.style.borderColor = '#ddd';
              div.style.backgroundColor = '#fff';
              div.style.boxShadow = 'none';
          });

          const parentDiv = option.closest('div[style*="border"]');
          if(parentDiv){
              parentDiv.style.borderColor = '#0d6efd';
              parentDiv.style.backgroundColor = '#e7f1ff';
              parentDiv.style.boxShadow = '0 0 8px rgba(13, 110, 253, 0.5)';
          }
      });
  });

  // Apply highlight to initially checked radio
  document.querySelector('input[name="paymethod"]:checked').dispatchEvent(new Event('change'));
</script>

</body>
</html>
