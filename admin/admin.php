<?php
// Database configuration
include_once('includes/config.php');
// Create connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allowed statuses and sort fields
$allowedStatuses = ['Pending', 'Processing', 'Delivered'];
$sortMap = [
    'order_id' => 'orders.id',
    'order_date' => 'orders.orderDate',
    'customer' => 'users.name',
    'price' => 'products.productPrice'
];

// Handle order status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['status'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $conn->prepare("UPDATE orders SET orderStatus = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to avoid resubmission and remove filter
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle filtering and sorting
$filterStatus = $_GET['filter_status'] ?? '';
$sortKey = $_GET['sort_by'] ?? 'order_id';
$sortOrder = strtolower($_GET['sort_order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$sortBy = $sortMap[$sortKey] ?? 'orders.id';
if (!in_array($filterStatus, $allowedStatuses)) {
    $filterStatus = '';
}

// Build query
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

$sql .= " ORDER BY $sortBy $sortOrder";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        table { width: 95%; margin: 20px auto; border-collapse: collapse; background: #fff; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #e9e9e9; }
        .pending, .processing { background-color: #fff3cd; }
        .delivered { background-color: #d4edda; }
        .filter-sort { text-align: center; margin-bottom: 20px; }
        form.inline { display: inline-block; margin: 0 5px; }
        button { padding: 5px 10px; cursor: pointer; }
        .btn-green { background: #28a745; color: white; border: none; border-radius: 5px; }
        .btn-blue { background: #007bff; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>

<div style="text-align:center; margin-top:20px;">
    <a href="product_management.php"><button class="btn-green">Go to Product Management</button></a>
</div>

<h2 style="text-align:center;">Complete Order Management</h2>

<div class="filter-sort">
    <form method="GET" class="inline">
        <label>Status:</label>
        <select name="filter_status">
            <option value="">All</option>
            <?php foreach ($allowedStatuses as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= $filterStatus == $status ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Sort by:</label>
        <select name="sort_by">
            <option value="order_id" <?= $sortKey == 'order_id' ? 'selected' : '' ?>>Order ID</option>
            <option value="order_date" <?= $sortKey == 'order_date' ? 'selected' : '' ?>>Order Date</option>
            <option value="customer" <?= $sortKey == 'customer' ? 'selected' : '' ?>>Customer</option>
            <option value="price" <?= $sortKey == 'price' ? 'selected' : '' ?>>Price</option>
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
    <?php while ($row = $result->fetch_assoc()): ?>
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
                            <option value="<?= htmlspecialchars($status) ?>" <?= $row['orderStatus'] == $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button name="update">Update</button>
                </form>
            </td>
            <td>
                <form method="GET" action="generateinvoice.php" class="inline">
                    <input type="hidden" name="oid" value="<?= htmlspecialchars($row['orderId']) ?>">
                    <button class="btn-blue">Download</button>
                </form>
            </td>
            <td><?= htmlspecialchars($row['shippingAddress']) ?>, <?= htmlspecialchars($row['shippingCity']) ?>, <?= htmlspecialchars($row['shippingState']) ?> - <?= htmlspecialchars($row['shippingPincode']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

<?php $conn->close(); ?>
