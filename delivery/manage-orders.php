<?php
// manage-orders.php
include_once('config.php');
session_start();
if (!isset($_SESSION['delivery_login'])) {
    header('location:index.php');
    exit;
}

$allowedStatuses = ['Accepted', 'Processing', 'Shipped from ApnaBazaar', 'Payment_Done', 'Delivered'];
$allowedSortFields = ['orders.id', 'orders.orderDate', 'users.name', 'products.productPrice'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <!-- 🌐 Delivery Panel Top Bar -->
<div style="
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #343a40;
    padding: 12px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    color: #ffffff;
    font-family: 'Segoe UI', sans-serif;
    position: sticky;
    top: 0;
    z-index: 1000;
">
    <h2 style="margin: 0; font-size: 22px; letter-spacing: 0.5px;">
        🚚 Delivery Panel
    </h2>

    <form action="logout.php" method="post" style="margin: 0;">
        <button type="submit" style="
            padding: 8px 16px;
            background-color: #dc3545;
            color: #fff;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        " onmouseover="this.style.backgroundColor='#c82333'" onmouseout="this.style.backgroundColor='#dc3545'">
            🚪 Logout
        </button>
    </form>
</div>

    
   

    <style>
        table { width: 95%; margin: 20px auto; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
        .pending, .processing { background-color: #f8d7da; }
        .shipped, .delivered, .payment_done { background-color: #d4edda; }
        .filter-sort { text-align: center; margin-bottom: 20px; }
        form.inline { display: inline-block; }
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: none;
            z-index: 9999;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="filter-sort">
    <form id="filterForm" class="inline">
        <label>Status:</label>
        <select name="filter_status" id="filter_status">
            <option value="">All</option>
            <?php foreach ($allowedStatuses as $status): ?>
                <option value="<?= $status ?>"><?= $status ?></option>
            <?php endforeach; ?>
        </select>

        <label>Sort by:</label>
        <select name="sort_by" id="sort_by">
            <option value="orders.id">Order ID</option>
            <option value="orders.orderDate">Order Date</option>
            <option value="users.name">Customer</option>
            <option value="products.productPrice">Price</option>
        </select>

        <select name="sort_order" id="sort_order">
            <option value="desc" selected>Descending</option>
            <option value="asc">Ascending</option>
        </select>

        <input type="submit" value="Apply">
    </form>
</div>

<div id="orderTableContainer"></div>

<!-- Toast and Audio -->
<div id="toast">🔔 New Order Received!</div>
<audio id="alertSound"><source src="assets/audio/notification.mp3" type="audio/mpeg"></audio>

<script>
let lastOrderId = 0;

function fetchOrders() {
    const status = $('#filter_status').val();
    const sortBy = $('#sort_by').val();
    const sortOrder = $('#sort_order').val();

    $.get('fetch-orders.php', {
        filter_status: status,
        sort_by: sortBy,
        sort_order: sortOrder
    }, function (data) {
        const $data = $(data);
        const $ordersTable = $data.filter('table');
        const newOrderFlag = $data.filter('[data-neworder="yes"]');

        $('#orderTableContainer').html($ordersTable);

        // Play sound + toast if new order
        if (newOrderFlag.length) {
            $('#toast').fadeIn(200).delay(2000).fadeOut(400);
            const audio = document.getElementById('alertSound');
            audio.play();
        }

        // Re-bind form submit for status update
        $('.updateForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            $.post('update-order-status.php', $(this).serialize(), function () {
                fetchOrders(); // Refresh again
            });
        });
    });
}

fetchOrders();
setInterval(fetchOrders, 5000);

$('#filterForm').on('submit', function (e) {
    e.preventDefault();
    fetchOrders();
});
</script>
</body>
</html>
