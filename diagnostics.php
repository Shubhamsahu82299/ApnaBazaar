<?php
// Display errors for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$start_time = microtime(true);

// 1. Check if config.php exists and track load time
$config_start = microtime(true);
if (file_exists('includes/config.php')) {
    include('includes/config.php');
    $config_status = "✅ Found & Included Successfully";
} else if (file_exists('../includes/config.php')) {
    include('../includes/config.php');
    $config_status = "✅ Found & Included Successfully (from parent dir)";
} else {
    $config_status = "❌ NOT FOUND! Please check file path mapping.";
}
$config_time = (microtime(true) - $config_start) * 1000; // in milliseconds

// Determine which DB variable instance is active ($con or $conn)
$db_handle = null;
$db_var_name = '';
if (isset($con)) { $db_handle = $con; $db_var_name = '$con'; }
elseif (isset($conn)) { $db_handle = $conn; $db_var_name = '$conn'; }

// 2. Database Server Latency & Availability Check
$db_latency = 0;
$db_status = "❌ Offline / Connection Failed";
$db_host_info = "N/A";

if ($db_handle) {
    $db_ping_start = microtime(true);
    // Execute a lightweight query to test live response time
    if ($db_handle instanceof mysqli) {
        $ping_res = $db_handle->query("SELECT 1");
        if ($ping_res) {
            $db_latency = (microtime(true) - $db_ping_start) * 1000;
            $db_status = "✅ Online & Responsive (Variable: $db_var_name)";
            $db_host_info = $db_handle->host_info;
        }
    }
}

// 3. Check if getProductImage Function Hook works
$function_check = "❌ Not Declared in config.php";
$test_image_local = "N/A";
$test_image_cloud = "N/A";

if (function_exists('getProductImage')) {
    $function_check = "✅ Active & Available Globally";
    $test_image_local = getProductImage(45, "sample.jpg");
    $test_image_cloud = getProductImage(45, "https://res.cloudinary.com/demo/image/upload/sample.jpg");
}

$server_execution_time = (microtime(true) - $start_time) * 1000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApnaBazaar Server & DB Diagnostics Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .badge-speed { font-size: 1.1rem; padding: 6px 12px; border-radius: 8px; }
        .metric-title { font-weight: 600; font-size: 0.95rem; color: #64748b; }
        .metric-value { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
    </style>
</head>
<body class="p-4">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">⚡ ApnaBazaar Cloud System Health Check</h2>
            <p class="text-muted">Real-time Network Speed, Server Execution Time & Clever Cloud Connection Metrics</p>
        </div>

        <!-- 📡 NETWORK & SERVER SPEED CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3 bg-white text-center">
                    <span class="metric-title">💡 YOUR INTERNET LATENCY</span>
                    <div class="metric-value mt-2" id="client-latency">Measuring...</div>
                    <small class="text-muted mt-1">Browser to local server</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-white text-center">
                    <span class="metric-title">🖥️ SERVER TIME (TTFB)</span>
                    <div class="metric-value mt-2 text-success"><?= number_format($server_execution_time, 2) ?> ms</div>
                    <small class="text-muted mt-1">Time taken by PHP engine</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-white text-center">
                    <span class="metric-title">☁️ CLEVER CLOUD DB PING</span>
                    <div class="metric-value mt-2 <?= $db_latency > 250 ? 'text-warning' : 'text-primary' ?>">
                        <?= $db_latency > 0 ? number_format($db_latency, 2) . " ms" : "Offline" ?>
                    </div>
                    <small class="text-muted mt-1">Server to remote cloud DB</small>
                </div>
            </div>
        </div>

        <!-- 🎛️ DETAILED METRICS TABLE -->
        <div class="card p-4 bg-white mb-4">
            <h5 class="fw-bold mb-3 text-dark">🔍 Component & Configuration Diagnostics</h5>
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Check Validation Component</th>
                        <th>Status Output</th>
                        <th>Performance Logs / Path Mapping</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold">Config Loader Integrity</td>
                        <td><?= $config_status ?></td>
                        <td>Loaded in <strong class="text-dark"><?= number_format($config_time, 3) ?> ms</strong></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Clever Cloud Availability</td>
                        <td><span class="badge <?= $db_latency > 0 ? 'bg-success' : 'bg-danger' ?>"><?= $db_latency > 0 ? 'ONLINE' : 'OFFLINE' ?></span></td>
                        <td><?= $db_status ?><br><small class="text-muted"><?= $db_host_info ?></small></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Hybrid Image Resolver Engine</td>
                        <td><?= $function_check ?></td>
                        <td>
                            <small class="d-block text-truncate"><strong>Local Path Test:</strong> <?= htmlspecialchars($test_image_local) ?></small>
                            <small class="d-block text-truncate"><strong>Cloud Link Test:</strong> <?= htmlspecialchars($test_image_cloud) ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 💡 ANALYSIS REPORT -->
        <div class="card p-4 bg-dark text-white">
            <h5 class="fw-bold mb-2 text-warning">📊 Performance Bottleneck Analysis Report</h5>
            <div id="bottleneck-report" class="small">Analyzing parameters...</div>
        </div>
    </div>

    <!-- JAVASCRIPT FOR SPEED TESTING -->
    <script>
        // Track the exact timestamp when script request left the browser boundary
        const clientStart = performance.now();
        
        window.addEventListener('load', () => {
            const clientEnd = performance.now();
            const browserLatency = clientEnd - clientStart;
            
            document.getElementById('client-latency').innerText = browserLatency.toFixed(2) + " ms";
            
            // Generate Dynamic Recommendations based on server results
            const dbLatency = <?= floatval($db_latency) ?>;
            const phpTime = <?= floatval($server_execution_time) ?>;
            let reportHtml = "<ul class='mb-0 ps-3'>";
            
            if (dbLatency > 200) {
                reportHtml += "<li><strong>⚠️ High Database Latency detected (" + dbLatency.toFixed(1) + "ms):</strong> Clever Cloud servers are physically remote compared to your local environment (XAMPP). In live production servers (e.g., when you host the PHP code on Heroku or Clever Cloud app clusters directly alongside the DB), this network latency drop down to near &lt; 5ms automatically.</li>";
            } else if (dbLatency === 0) {
                reportHtml += "<li><strong>❌ Database Connection Error:</strong> Check if your system's outbound port 3306 is blocked by a local firewall, or double-check the credentials inside your <code>.env</code> file.</li>";
            } else {
                reportHtml += "<li><strong>✅ Excellent DB Response:</strong> Server connection to Clever Cloud infrastructure is healthy.</li>";
            }
            
            if (phpTime > 500) {
                reportHtml += "<li><strong>⚠️ Slow Script Execution:</strong> Avoid resource-heavy SQL parameters like <code>ORDER BY RAND()</code> in your grid loops. Fetch standard indexed lists instead to improve rendering speed!</li>";
            } else {
                reportHtml += "<li><strong>✅ Fast PHP Core:</strong> Script logic structure execution time is working well inside acceptable parameters.</li>";
            }
            
            reportHtml += "</ul>";
            document.getElementById('bottleneck-report').innerHTML = reportHtml;
        });
    </script>
</body>
</html>