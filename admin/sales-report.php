<?php
include('includes/config.php');
if (isset($_GET['ajax']) && $_GET['ajax'] == 'getChartData') {
    $type = $_GET['type'] ?? 'daily';
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    $labels = [];
    $revenueData = [];
    $ordersData = [];
    $deliveredData = [];
    $cancelledData = []; // 🔄 NEW CODE
    $returnedData = []; // 🔄 NEW CODE

    if ($type === 'daily') {
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-" . ($i - $offset) . " days"));
            $label = date('d M', strtotime($day));

            $rev = mysqli_query($conn, "SELECT SUM(products.productPrice * orders.quantity + products.shippingCharge) as total
                FROM orders JOIN products ON orders.productId = products.id
                WHERE DATE(orders.orderDate) = '$day' AND orders.paymentMethod IS NOT NULL");
            $revenue = mysqli_fetch_assoc($rev)['total'] ?? 0;

            $ord = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE(orderDate) = '$day' AND paymentMethod IS NOT NULL");
            $orders = mysqli_fetch_assoc($ord)['count'] ?? 0;

            $del = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE(orderDate) = '$day' AND orderStatus='Delivered'");
            $delivered = mysqli_fetch_assoc($del)['count'] ?? 0;

            $can = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE(orderDate) = '$day' AND orderStatus='Cancelled'"); // 🔄 NEW CODE
            $cancelled = mysqli_fetch_assoc($can)['count'] ?? 0;

            $ret = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE(orderDate) = '$day' AND orderStatus IN ('Returned', 'Exchange')"); // 🔄 NEW CODE
            $returned = mysqli_fetch_assoc($ret)['count'] ?? 0;

            $labels[] = $label;
            $revenueData[] = (int)$revenue;
            $ordersData[] = (int)$orders;
            $deliveredData[] = (int)$delivered;
            $cancelledData[] = (int)$cancelled; // 🔄
            $returnedData[] = (int)$returned; // 🔄
        }

    } elseif ($type === 'monthly') {
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-" . ($i - $offset) . " months"));
            $label = date('M Y', strtotime($month));

            $rev = mysqli_query($conn, "SELECT SUM(products.productPrice * orders.quantity + products.shippingCharge) as total
                FROM orders JOIN products ON orders.productId = products.id
                WHERE DATE_FORMAT(orders.orderDate, '%Y-%m') = '$month' AND orders.paymentMethod IS NOT NULL");
            $revenue = mysqli_fetch_assoc($rev)['total'] ?? 0;

            $ord = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE_FORMAT(orderDate, '%Y-%m') = '$month' AND paymentMethod IS NOT NULL");
            $orders = mysqli_fetch_assoc($ord)['count'] ?? 0;

            $del = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE_FORMAT(orderDate, '%Y-%m') = '$month' AND orderStatus='Delivered'");
            $delivered = mysqli_fetch_assoc($del)['count'] ?? 0;

            $can = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE_FORMAT(orderDate, '%Y-%m') = '$month' AND orderStatus='Cancelled'");
            $cancelled = mysqli_fetch_assoc($can)['count'] ?? 0;

            $ret = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE DATE_FORMAT(orderDate, '%Y-%m') = '$month' AND orderStatus IN ('Returned', 'Exchange')");
            $returned = mysqli_fetch_assoc($ret)['count'] ?? 0;

            $labels[] = $label;
            $revenueData[] = (int)$revenue;
            $ordersData[] = (int)$orders;
            $deliveredData[] = (int)$delivered;
            $cancelledData[] = (int)$cancelled;
            $returnedData[] = (int)$returned;
        }

    } elseif ($type === 'yearly') {
        for ($i = 4; $i >= 0; $i--) {
            $year = date('Y', strtotime("-" . ($i - $offset) . " years"));

            $rev = mysqli_query($conn, "SELECT SUM(products.productPrice * orders.quantity + products.shippingCharge) as total
                FROM orders JOIN products ON orders.productId = products.id
                WHERE YEAR(orders.orderDate) = '$year' AND orders.paymentMethod IS NOT NULL");
            $revenue = mysqli_fetch_assoc($rev)['total'] ?? 0;

            $ord = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE YEAR(orderDate) = '$year' AND paymentMethod IS NOT NULL");
            $orders = mysqli_fetch_assoc($ord)['count'] ?? 0;

            $del = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE YEAR(orderDate) = '$year' AND orderStatus='Delivered'");
            $delivered = mysqli_fetch_assoc($del)['count'] ?? 0;

            $can = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE YEAR(orderDate) = '$year' AND orderStatus='Cancelled'");
            $cancelled = mysqli_fetch_assoc($can)['count'] ?? 0;

            $ret = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders 
                WHERE YEAR(orderDate) = '$year' AND orderStatus IN ('Returned', 'Exchange')");
            $returned = mysqli_fetch_assoc($ret)['count'] ?? 0;

            $labels[] = $year;
            $revenueData[] = (int)$revenue;
            $ordersData[] = (int)$orders;
            $deliveredData[] = (int)$delivered;
            $cancelledData[] = (int)$cancelled;
            $returnedData[] = (int)$returned;
        }
    }

    echo json_encode([
        'labels' => $labels,
        'revenue' => $revenueData,
        'orders' => $ordersData,
        'delivered' => $deliveredData,
        'cancelled' => $cancelledData, // 🔄
        'returned' => $returnedData // 🔄
    ]);
    exit;
}
?>

<?php include('includes/main-header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Report Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container py-4">
  <h3 class="text-center mb-4">Sales Report</h3>

  <div class="row text-center mb-4">
    <div class="col"><div class="card p-2 bg-success-subtle"><h6>Total Revenue</h6><strong id="totalRevenue">₹0</strong></div></div>
    <div class="col"><div class="card p-2 bg-primary-subtle"><h6>Total Orders</h6><strong id="totalOrders">0</strong></div></div>
    <div class="col"><div class="card p-2 bg-info-subtle"><h6>Delivered</h6><strong id="totalDelivered">0</strong></div></div>
    <div class="col"><div class="card p-2 bg-danger-subtle"><h6>Cancelled</h6><strong id="totalCancelled">0</strong></div></div> <!-- 🔄 -->
    <div class="col"><div class="card p-2 bg-warning-subtle"><h6>Returned/Exchange</h6><strong id="totalReturned">0</strong></div></div> <!-- 🔄 -->
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <select id="reportType" class="form-select w-auto" onchange="fetchChartData()">
      <option value="daily">Last 7 Days</option>
      <option value="monthly">Monthly (Last 12)</option>
      <option value="yearly">Yearly (Last 5)</option>
    </select>
    <div>
      <button onclick="shiftOffset(-1)" class="btn btn-sm btn-outline-primary">&larr; Prev</button>
      <button onclick="resetOffset()" class="btn btn-sm btn-outline-secondary">Today</button>
      <button onclick="shiftOffset(1)" class="btn btn-sm btn-outline-primary">Next &rarr;</button>
    </div>
    <div>
      <input type="checkbox" id="autoRefresh" checked> Auto Refresh
    </div>
  </div>

  <div class="card p-3 mb-4">
    <h5 class="text-center">Revenue</h5>
    <canvas id="revenueChart" height="200"></canvas>
  </div>

  <div class="card p-3">
    <h5 class="text-center">Orders vs Delivered</h5>
    <canvas id="ordersChart" height="200"></canvas>
  </div>
  <div class="card p-3 mt-4">
  <h5 class="text-center">Order Status Distribution</h5>
  <canvas id="statusPieChart" height="200"></canvas>
</div>
</div>

<script>
let reportType = 'daily';
let offset = 0;
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const ordersCtx = document.getElementById('ordersChart').getContext('2d');

const revenueChart = new Chart(revenueCtx, {
  type: 'bar',
  data: { labels: [], datasets: [{ label: 'Revenue (₹)', data: [], backgroundColor: '#4e73df' }] },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

const ordersChart = new Chart(ordersCtx, {
  type: 'bar',
  data: {
    labels: [],
    datasets: [
      { label: 'Orders', data: [], backgroundColor: '#f6c23e' },
      { label: 'Delivered', data: [], backgroundColor: '#1cc88a' }
    ]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

function fetchChartData() {
  reportType = document.getElementById('reportType').value;
  fetch(`?ajax=getChartData&type=${reportType}&offset=${offset}`)
    .then(res => res.json())
    .then(({ labels, revenue, orders, delivered, cancelled, returned }) => {
      revenueChart.data.labels = labels;
      revenueChart.data.datasets[0].data = revenue;
      revenueChart.update();

      ordersChart.data.labels = labels;
      ordersChart.data.datasets[0].data = orders;
      ordersChart.data.datasets[1].data = delivered;
      ordersChart.update();

      document.getElementById('totalRevenue').textContent = '₹' + revenue.reduce((a, b) => a + b, 0);
      document.getElementById('totalOrders').textContent = orders.reduce((a, b) => a + b, 0);
      document.getElementById('totalDelivered').textContent = delivered.reduce((a, b) => a + b, 0);
      document.getElementById('totalCancelled').textContent = cancelled.reduce((a, b) => a + b, 0); // 🔄
      document.getElementById('totalReturned').textContent = returned.reduce((a, b) => a + b, 0); // 🔄
      statusPieChart.data.datasets[0].data = [
  delivered.reduce((a, b) => a + b, 0),
  cancelled.reduce((a, b) => a + b, 0),
  returned.reduce((a, b) => a + b, 0)
];
statusPieChart.update();

    });
}
const statusPieCtx = document.getElementById('statusPieChart').getContext('2d');
const statusPieChart = new Chart(statusPieCtx, {
  type: 'pie',
  data: {
    labels: ['Delivered', 'Cancelled', 'Returned/Exchange'],
    datasets: [{
      label: 'Order Status',
      data: [0, 0, 0],
      backgroundColor: ['#1cc88a', '#e74a3b', '#f0ad4e'],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom'
      }
    }
  }
});

function shiftOffset(val) {
  offset += val;
  fetchChartData();
}

function resetOffset() {
  offset = 0;
  fetchChartData();
}

setInterval(() => {
  if (document.getElementById('autoRefresh').checked) fetchChartData();
}, 10000);

fetchChartData();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
