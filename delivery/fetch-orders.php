<?php
// fetch-orders.php
session_start();
include_once('config.php');

$allowedStatuses = ['Accepted', 'Processing', 'Shipped from ApnaBazaar', 'Payment_Done', 'Delivered'];
$allowedSortFields = ['orders.id', 'orders.orderDate', 'users.name', 'products.productPrice'];

$filterStatus = $_GET['filter_status'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'orders.id';
$sortOrder = strtolower($_GET['sort_order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

if (!in_array($filterStatus, $allowedStatuses)) $filterStatus = '';
if (!in_array($sortBy, $allowedSortFields)) $sortBy = 'orders.id';

// Check for new unaccepted orders within last 10 seconds
$newOrder = false;
$sqlNew = "SELECT id FROM orders WHERE TIMESTAMPDIFF(SECOND, orderDate, NOW()) <= 10 AND (orderStatus IS NULL OR orderStatus != 'Accepted') LIMIT 1";
$resNew = $conn->query($sqlNew);
if ($resNew->num_rows > 0) {
    $newOrder = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order List</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 10px;
      background: #f9f9f9;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
      font-weight: 600;
    }

    tr:nth-child(even) {
      background-color: #fafafa;
    }

    .inline {
      display: flex;
      gap: 5px;
      flex-wrap: wrap;
    }

    button, select {
      padding: 4px 6px;
      font-size: 13px;
      cursor: pointer;
    }

    @media screen and (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 15px;
        background: #fff;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
      }

      td {
        border: none;
        position: relative;
        padding-left: 50%;
        padding-top: 8px;
        padding-bottom: 8px;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 45%;
        padding-right: 10px;
        font-weight: bold;
        white-space: nowrap;
      }

      form.inline {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

<?php
$sql = "SELECT orders.id AS orderId, orders.quantity, orders.orderDate, orders.paymentMethod, orders.orderStatus, orders.deliveryCharge,
        users.name, users.contactno, users.shippingAddress, users.shippingCity, users.shippingState,
        users.shippingPincode, products.productName, products.productCompany, products.productPrice
        FROM orders
        JOIN users ON orders.userId = users.id
        JOIN products ON orders.productId = products.id";

if ($filterStatus) {
    $sql .= " WHERE orders.orderStatus = '" . $conn->real_escape_string($filterStatus) . "'";
}
$sql .= " ORDER BY $sortBy $sortOrder";

$result = $conn->query($sql);

if ($newOrder) {
    echo '<div data-neworder="yes"></div>';
    echo '<audio id="alertSound" autoplay><source src="assets/audio/notification.mp3" type="audio/mpeg"></audio>';
}

echo '<table>
<thead>
<tr>
<th>ID</th><th>Customer</th><th>Contact</th><th>Product</th><th>Company</th><th>Price</th>
<th>Qty</th><th>Shipping</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th><th>Update</th><th>Shipping Address</th>
</tr>
</thead><tbody>';

while ($row = $result->fetch_assoc()) {
    $cls = strtolower(str_replace(' ', '_', $row['orderStatus']));
    $total = ($row['quantity'] * $row['productPrice']) + $row['deliveryCharge'];

    $orderDate = new DateTime($row['orderDate']);
    $orderDate->setTimezone(new DateTimeZone('Asia/Kolkata'));

    echo '<tr class="' . htmlspecialchars($cls) . '">';
    echo '<td data-label="ID">' . htmlspecialchars($row['orderId']) . '</td>';
    echo '<td data-label="Customer">' . htmlspecialchars($row['name']) . '</td>';
    echo '<td data-label="Contact">' . htmlspecialchars($row['contactno']) . '</td>';
    echo '<td data-label="Product">' . htmlspecialchars($row['productName']) . '</td>';
    echo '<td data-label="Company">' . htmlspecialchars($row['productCompany']) . '</td>';
    echo '<td data-label="Price">₹' . htmlspecialchars($row['productPrice']) . '</td>';
    echo '<td data-label="Qty">' . htmlspecialchars($row['quantity']) . '</td>';
    echo '<td data-label="Shipping">₹' . htmlspecialchars($row['deliveryCharge']) . '</td>';
    echo '<td data-label="Total">₹' . htmlspecialchars($total) . '</td>';
    echo '<td data-label="Payment">' . htmlspecialchars($row['paymentMethod']) . '</td>';
    echo '<td data-label="Date">' . htmlspecialchars($orderDate->format('d-m-Y H:i:s')) . '</td>';
    echo '<td data-label="Status">' . htmlspecialchars($row['orderStatus']) . '</td>';

    echo '<td data-label="Update"><form class="updateForm inline">';
    echo '<input type="hidden" name="order_id" value="' . htmlspecialchars($row['orderId']) . '">';
    echo '<select name="status">';
    foreach ($allowedStatuses as $status) {
        $selected = ($row['orderStatus'] === $status) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($status) . '" ' . $selected . '>' . htmlspecialchars($status) . '</option>';
    }
    echo '</select><button type="submit">Update</button></form></td>';



    echo '<td data-label="Shipping Address">' . htmlspecialchars($row['shippingAddress']) . ', ' . htmlspecialchars($row['shippingCity']) . ', ' . htmlspecialchars($row['shippingState']) . ' - ' . htmlspecialchars($row['shippingPincode']) . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';
$conn->close();
?>

</body>
</html>
