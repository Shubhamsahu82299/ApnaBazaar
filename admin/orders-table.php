<?php

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$filterStatus = $_GET['filter_status'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'orders.id';
$sortOrder = strtolower($_GET['sort_order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$allowedStatuses = ['Pending', 'Processing', 'Delivered'];
$allowedSortFields = ['orders.id', 'orders.orderDate', 'users.name', 'products.productPrice'];
if (!in_array($filterStatus, $allowedStatuses)) $filterStatus = '';
if (!in_array($sortBy, $allowedSortFields)) $sortBy = 'orders.id';

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
            <?php $total = ($row['quantity'] * $row['productPrice']) + $row['shippingCharge']; ?>
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

<?php $conn->close(); ?>
