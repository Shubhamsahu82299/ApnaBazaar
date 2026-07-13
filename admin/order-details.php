<?php
// Include DB connection
// DB Connection
$host = "localhost";
$username = "u814646522_ApnaBazaar";
$password = "ApnaBazaar967";
$database = "u814646522_shopping";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Update order status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['status'];

    // Validate status input
    $validStatuses = ['Pending', 'Processing', 'Delivered'];
    if (in_array($newStatus, $validStatuses)) {
        $updateSql = "UPDATE orders SET orderStatus=? WHERE id=?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();
    }
}

// Sorting & filtering with validation
$filterStatus = $_GET['filter_status'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'orders.id';
$sortOrder = strtolower($_GET['sort_order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$allowedStatuses = ['Pending', 'Processing', 'Delivered'];
$allowedSortFields = ['orders.id', 'orders.orderDate', 'users.name', 'products.productPrice'];

if (!in_array($filterStatus, $allowedStatuses)) {
    $filterStatus = '';
}

if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'orders.id';
}
$sql = "SELECT orders.id AS orderId, orders.quantity, orders.orderDate, orders.paymentMethod, orders.orderStatus,
        users.name, users.contactno, users.shippingAddress, users.shippingCity, users.shippingState,
        users.shippingPincode, products.productName, products.productCompany, products.productPrice,
        products.shippingCharge
        FROM orders
        JOIN users ON orders.userId = users.id
        JOIN products ON orders.productId = products.id";

if ($filterStatus) {
    $sql .= " WHERE orders.orderStatus = '" . $conn->real_escape_string($filterStatus) . "'";
}

$sql .= " ORDER BY " . $conn->real_escape_string($sortBy) . " $sortOrder";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <style>
        table { width: 95%; margin: 20px auto; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
        .pending, .processing { background-color: #f8d7da; }
        .shipped, .delivered { background-color: #d4edda; }
        .filter-sort { text-align: center; margin-bottom: 20px; }
        form.inline { display: inline-block; }
    </style>
</head>
<body>
<a href="product_management.php">
    <button style="padding:10px 20px; background-color:#28a745; color:white; border:none; border-radius:5px; cursor:pointer;">
        Go to Product Management
    </button>
</a>
<!-- Add inside index.php body -->
<div class="container mt-4">
    <a href="add-category.php" class="btn btn-success">Add Category</a>
</div>
<h2 style="text-align:center;">Complete Order Management</h2>

<div class="filter-sort">
    <form method="GET" class="inline">
        <label>Status:</label>
        <select name="filter_status">
            <option value="">All</option>
            <?php foreach ($allowedStatuses as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= $filterStatus == $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Sort by:</label>
        <select name="sort_by">
            <option value="orders.id" <?= $sortBy == 'orders.id' ? 'selected' : '' ?>>Order ID</option>
            <option value="orders.orderDate" <?= $sortBy == 'orders.orderDate' ? 'selected' : '' ?>>Order Date</option>
            <option value="users.name" <?= $sortBy == 'users.name' ? 'selected' : '' ?>>Customer</option>
            <option value="products.productPrice" <?= $sortBy == 'products.productPrice' ? 'selected' : '' ?>>Price</option>
        </select>
        <select name="sort_order">
            <option value="asc" <?= $sortOrder == 'ASC' ? 'selected' : '' ?>>Ascending</option>
            <option value="desc" <?= $sortOrder == 'DESC' ? 'selected' : '' ?>>Descending</option>
        </select>
        <input type="submit" value="Apply">
    </form>
</div>
<table>
    <tr>
        <th>ID</th><th>Customer</th><th>Contact</th><th>Product</th><th>Company</th><th>Price</th>
        <th>Qty</th><th>Shipping</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th><th>Update</th><th>Invoice</th><th>Shipping Address</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
        <?php $cls = strtolower($row['orderStatus']); ?>
        <tr class="<?= htmlspecialchars($cls) ?>">
            <td><?= htmlspecialchars($row['orderId']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['contactno']) ?></td>
            <td><?= htmlspecialchars($row['productName']) ?></td>
            <td><?= htmlspecialchars($row['productCompany']) ?></td>
            <td>₹<?= htmlspecialchars($row['productPrice']) ?></td>
            <td><?= htmlspecialchars($row['quantity']) ?></td>
            <td>₹<?= htmlspecialchars($row['shippingCharge']) ?></td>
            <?php
                $total = ($row['quantity'] * $row['productPrice']) + $row['shippingCharge'];
            ?>
            <td>₹<?= htmlspecialchars($total) ?></td>
            <td><?= htmlspecialchars($row['paymentMethod']) ?></td>
            <td>
                <?php
                $dateUtc = new DateTime($row['orderDate'], new DateTimeZone('UTC'));
                $dateUtc->setTimezone(new DateTimeZone('Asia/Kolkata'));
                echo htmlspecialchars($dateUtc->format('d-m-Y H:i:s'));
                ?>
            </td>
            <td><?= htmlspecialchars($row['orderStatus']) ?></td>
            <td>
                <form method="POST" class="inline">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['orderId']) ?>">
                    <select name="status">
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option <?= $row['orderStatus'] == $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button name="update">Update</button>
                </form>
            </td>
            <td>
                <form method="GET" action="generateinvoice.php" class="inline">
                    <input type="hidden" name="oid" value="<?= htmlspecialchars($row['orderId']) ?>">
                    <button>Download</button>
                </form>
            </td>
            <td><?= htmlspecialchars($row['shippingAddress']) ?>, <?= htmlspecialchars($row['shippingCity']) ?>, <?= htmlspecialchars($row['shippingState']) ?> - <?= htmlspecialchars($row['shippingPincode']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>


</body>
</html>

<?php $conn->close(); ?>