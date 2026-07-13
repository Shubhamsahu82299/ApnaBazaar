<?php
session_start();
include('includes/config.php');
if (!isset($_SESSION['admin_login'])) {
    header('location:index.php');
    exit;
}

// === Handle AJAX request for chart data ===
if (isset($_GET['ajax']) && $_GET['ajax'] == 'getChartData') {
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    $dates = [];
    $revenueData = [];
    $ordersData = [];
    $deliveredData = [];

    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-" . ($i - $offset) . " days"));
        $label = date('d M', strtotime($day));

        // Revenue
        $rev = mysqli_query($conn, "SELECT SUM(products.productPrice * orders.quantity + products.shippingCharge) as total
            FROM orders JOIN products ON orders.productId = products.id
            WHERE DATE(orders.orderDate) = '$day' AND orders.paymentMethod IS NOT NULL");
        $revenue = mysqli_fetch_assoc($rev)['total'] ?? 0;

        // Orders
        $ord = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
            WHERE DATE(orderDate) = '$day' AND paymentMethod IS NOT NULL");
        $orders = mysqli_fetch_assoc($ord)['count'] ?? 0;

        // Delivered
        $del = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
            WHERE DATE(orderDate) = '$day' AND orderStatus='Delivered'");
        $delivered = mysqli_fetch_assoc($del)['count'] ?? 0;

        $dates[] = $label;
        $revenueData[] = (int)$revenue;
        $ordersData[] = (int)$orders;
        $deliveredData[] = (int)$delivered;
    }

    echo json_encode([
        'dates' => $dates,
        'revenue' => $revenueData,
        'orders' => $ordersData,
        'delivered' => $deliveredData
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>7-Day Sales Report | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f9f9f9; font-family: 'Segoe UI', sans-serif; }
        .container { padding: 15px; }
        .card { border: none; box-shadow: 0 1px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2.title { font-size: 1.5rem; text-align: center; margin-bottom: 20px; }
        .nav-btns { text-align: center; margin: 10px 0; }
        @media (max-width: 576px) {
            canvas { max-width: 100% !important; height: auto !important; }
            .nav-btns button { margin: 2px; font-size: 14px; }
        }
    </style>
</head>
<body>
<?php include('includes/main-header.php'); ?>
<div class="container">
    <div class="card p-3">
        <h2 class="title">Revenue (Last 7 Days)</h2>
        <div class="nav-btns">
            <button onclick="shiftDays(-1)" class="btn btn-sm btn-outline-primary">&larr; Prev</button>
            <button onclick="resetDays()" class="btn btn-sm btn-outline-secondary">Today</button>
            <button onclick="shiftDays(1)" class="btn btn-sm btn-outline-primary">Next &rarr;</button>
        </div>
        <canvas id="revenueChart" height="250"></canvas>
    </div>

    <div class="card p-3">
        <h2 class="title">Orders vs Delivered (Last 7 Days)</h2>
        <canvas id="ordersChart" height="250"></canvas>
    </div>
</div>

<script>
let offset = 0;
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const ordersCtx = document.getElementById('ordersChart').getContext('2d');

const revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Revenue (₹)',
            data: [],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

const ordersChart = new Chart(ordersCtx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: [
            {
                label: 'Orders',
                data: [],
                backgroundColor: 'rgba(255, 206, 86, 0.7)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            },
            {
                label: 'Delivered',
                data: [],
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

function fetchChartData() {
    fetch(`?ajax=getChartData&offset=${offset}`)
        .then(res => res.json())
        .then(({ dates, revenue, orders, delivered }) => {
            revenueChart.data.labels = dates;
            revenueChart.data.datasets[0].data = revenue;
            revenueChart.update();

            ordersChart.data.labels = dates;
            ordersChart.data.datasets[0].data = orders;
            ordersChart.data.datasets[1].data = delivered;
            ordersChart.update();
        });
}

function shiftDays(change) {
    offset += change;
    fetchChartData();
}

function resetDays() {
    offset = 0;
    fetchChartData();
}

fetchChartData();
setInterval(fetchChartData, 5000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>