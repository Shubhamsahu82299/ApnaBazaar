<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

// 1. DATABASE CONFIGURATION
$host = "localhost";
$username = "root"; 
$password = "";     
$dbname = "apnabazaar";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) { 
    header('Content-Type: application/json');
    echo json_encode(['status' => 'local_error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// 2. CLOUDINARY CONFIGURATION
define('CLOUDINARY_CLOUD_NAME', 'dgirf891q');
define('CLOUDINARY_UPLOAD_PRESET', 'my_preset'); 

$local_base_path = __DIR__ . '/admin/productimages/';

// --- AJAX REQUEST HANDLING ---
if (isset($_GET['action']) && $_GET['action'] == 'process_row') {
    header('Content-Type: application/json');
    ob_clean();
    
    $product_id = intval($_GET['id']);
    
    $sql = "SELECT productImage1 FROM products WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'local_error', 'message' => 'SQL Prepare Engine crash: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$res || empty($res['productImage1'])) {
        echo json_encode(['status' => 'skip', 'message' => 'No asset path declared in table grid row.']);
        exit;
    }

    $image_name = $res['productImage1'];
    $source_file = $local_base_path . $product_id . '/' . $image_name;

    if (!file_exists($source_file) || is_dir($source_file)) {
        echo json_encode(['status' => 'local_error', 'message' => 'Missing local disk file at: admin/productimages/' . $product_id . '/' . $image_name]);
        exit;
    }

    $original_size = filesize($source_file);
    $info = getimagesize($source_file);
    if ($info === false) {
        echo json_encode(['status' => 'local_error', 'message' => 'Invalid file metadata headers. Cannot convert.']);
        exit;
    }

    switch ($info['mime']) {
        case 'image/jpeg': $image = @imagecreatefromjpeg($source_file); break;
        case 'image/png': 
            $image = @imagecreatefrompng($source_file); 
            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            break;
        case 'image/gif': $image = @imagecreatefromgif($source_file); break;
        case 'image/webp': $image = @imagecreatefromwebp($source_file); break;
        default: $image = false; break;
    }

    if (!$image) {
        echo json_encode(['status' => 'local_error', 'message' => 'GD Library source creation crash.']);
        exit;
    }

    $temp_compressed = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmp_' . $product_id . '.webp';
    if (!@imagewebp($image, $temp_compressed, 55)) {
        imagedestroy($image);
        echo json_encode(['status' => 'local_error', 'message' => 'System temp folder directory permission block.']);
        exit;
    }
    imagedestroy($image);

    $compressed_size = filesize($temp_compressed);
    $url = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile($temp_compressed),
        'upload_preset' => CLOUDINARY_UPLOAD_PRESET
    ]);

    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    
    if (curl_errno($ch)) {
        $curl_err = curl_error($ch);
        curl_close($ch);
        if (file_exists($temp_compressed)) { unlink($temp_compressed); }
        echo json_encode(['status' => 'cloudinary_error', 'message' => 'cURL Network Connection Timeout: ' . $curl_err]);
        exit;
    }
    curl_close($ch);

    if (file_exists($temp_compressed)) { unlink($temp_compressed); }
    $cloudinary_data = json_decode($response, true);

    if (isset($cloudinary_data['secure_url'])) {
        $new_url = $cloudinary_data['secure_url'];

        $update_sql = "UPDATE products SET productImage1 = ? WHERE id = ?";
        $u_stmt = $conn->prepare($update_sql);
        $u_stmt->bind_param("si", $new_url, $product_id);
        $u_stmt->execute();
        $u_stmt->close();

        $time_taken = $end_time - $start_time;
        $speed_kbps = ($compressed_size / 1024) / ($time_taken > 0 ? $time_taken : 1);

        echo json_encode([
            'status' => 'success',
            'secure_url' => $new_url,
            'orig_size' => $original_size,
            'comp_size' => $compressed_size,
            'speed' => round($speed_kbps, 2),
            'id' => $product_id
        ]);
    } else {
        $api_err = isset($cloudinary_data['error']['message']) ? $cloudinary_data['error']['message'] : 'Unrecognized preset validation.';
        echo json_encode(['status' => 'cloudinary_error', 'message' => 'Cloudinary Rejection: ' . $api_err]);
    }
    exit;
}

$preview_products = [];
$res = $conn->query("SELECT id, productName, productImage1 FROM products WHERE productImage1 NOT LIKE 'https://res.cloudinary.com%' AND productImage1 != '' ORDER BY id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) { $preview_products[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step-by-Step Production Migration Console</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; padding: 25px; color: #333; }
        .wrapper { max-width: 1200px; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin: 0 auto; }
        .header-panel { border-bottom: 3px solid #0366d6; padding-bottom: 15px; margin-bottom: 25px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .card { background: #f8f9fa; border: 1px solid #e1e4e8; padding: 15px; text-align: center; border-radius: 8px; }
        .card h4 { margin: 0 0 5px 0; color: #586069; font-size: 12px; text-transform: uppercase; }
        .card p { margin: 0; font-size: 22px; font-weight: bold; color: #2c3e50; }
        .progress-container { background: #e1e4e8; height: 30px; border-radius: 6px; overflow: hidden; position: relative; margin-bottom: 25px; }
        .bar { background: linear-gradient(90deg, #28a745, #2ecc71); height: 100%; width: 0%; transition: width 0.3s ease; }
        .percent-lbl { position: absolute; width: 100%; text-align: center; top: 6px; font-weight: bold; font-size: 14px; color: #24292e; }
        .action-bar { margin-bottom: 25px; display: flex; gap: 15px; }
        .btn { color: #fff; border: none; padding: 14px 40px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        #trigger-btn { background: #0366d6; }
        #stop-btn { background: #cf222e; display: none; }
        .table-wrapper { max-height: 450px; overflow-y: auto; border: 1px solid #e1e4e8; border-radius: 8px; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; background: #fff; font-size: 13px; }
        th { background: #f6f8fa; position: sticky; top: 0; padding: 14px 12px; border-bottom: 2px solid #e1e4e8; text-align: left; z-index: 10; }
        td { padding: 12px; border-bottom: 1px solid #e1e4e8; vertical-align: middle; }
        tr.active-processing-row { background-color: #f1f7fe !important; font-weight: 500; }
        .img-preview { width: 55px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid #d1d5da; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .badge-pending { background: #f1f8ff; color: #0366d6; border: 1px solid #c8e1ff; }
        .badge-success { background: #dcffe4; color: #1a7f37; border: 1px solid #abefb7; }
        .badge-error { background: #ffeef0; color: #cf222e; border: 1px solid #ffd8d8; font-family: monospace; }
        .row-progress-box { width: 100%; background: #e1e4e8; height: 12px; border-radius: 10px; overflow: hidden; display: none; margin-top: 5px; }
        .row-bar { height: 100%; width: 0%; background: #0366d6; }
        .meta-text { font-size: 11px; color: #586069; margin-top: 4px; display: block; }
        .cld-link { color: #1a7f37; font-weight: bold; text-decoration: none; word-break: break-all; font-size: 12px; border-bottom: 1px dashed #28a745; }
        .cld-link:hover { color: #115e29; }
        #error-log-zone { background: #1e1e1e; color: #f8f8f2; font-family: 'Courier New', monospace; padding: 15px; border-radius: 6px; max-height: 180px; overflow-y: auto; font-size: 12px; border: 1px solid #444; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="header-panel">
        <h2>Live Step-by-Step Cloudinary Migration Workspace</h2>
        <p style="margin:5px 0 0 0; color:#586069;">Highly crunching images down to ultra-efficient WebP file layers directly into Cloudinary.</p>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h4>Queue Load Remaining</h4>
            <p id="total-lbl"><?php echo count($preview_products); ?></p>
        </div>
        <div class="card">
            <h4>Synchronized Counter</h4>
            <p id="processed-lbl">0</p>
        </div>
        <div class="card">
            <h4>Transfer Speed</h4>
            <p id="global-speed-lbl">0.00 KB/s</p>
        </div>
        <div class="card">
            <h4>Est. Time Remaining</h4>
            <p id="eta-lbl">00:00:00</p>
        </div>
    </div>

    <div class="progress-container">
        <div class="bar" id="global-bar"></div>
        <div class="percent-lbl" id="global-text">0% Engine Standby Mode</div>
    </div>

    <div class="action-bar">
        <button class="btn" id="trigger-btn" onclick="initializeCloudinaryMigration()">Start Migration Now</button>
        <button class="btn" id="stop-btn" onclick="stopMigrationNow()">Stop / Pause</button>
    </div>

    <h3>Step 1: Live Record Queue Sync Matrix</h3>
    <div class="table-wrapper">
        <table id="migration-table">
            <thead>
                <tr>
                    <th width="10%">Preview</th>
                    <th width="10%">Product ID</th>
                    <th width="20%">Product Name</th>
                    <th width="20%">Local Filename</th>
                    <th width="20%">Active Status Analytics</th>
                    <th width="20%">Cloudinary Secured Link</th> <!-- NEW COLUMN -->
                </tr>
            </thead>
            <tbody>
                <?php if (empty($preview_products)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:#1a7f37; font-weight:bold; font-size:15px;">
                            🎉 Migration complete! All files successfully mapped to Cloudinary.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($preview_products as $prod): ?>
                        <tr id="row-<?php echo $prod['id']; ?>">
                            <td>
                                <img id="img-thumb-<?php echo $prod['id']; ?>" 
                                     src="admin/productimages/<?php echo $prod['id']; ?>/<?php echo htmlspecialchars($prod['productImage1']); ?>" 
                                     class="img-preview" 
                                     alt="Preview"
                                     onerror="this.src='https://placehold.co/60x60?text=No+File'; this.style.opacity='0.4';">
                            </td>
                            <td><strong>#<?php echo $prod['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($prod['productName']); ?></td>
                            <td><code style="color:#6f42c1; font-size:11px;"><?php echo htmlspecialchars($prod['productImage1']); ?></code></td>
                            <td id="status-<?php echo $prod['id']; ?>">
                                <span class="badge badge-pending" id="badge-lbl-<?php echo $prod['id']; ?>">Ready in Queue</span>
                                <div class="row-progress-box" id="progress-box-<?php echo $prod['id']; ?>">
                                    <div class="row-bar" id="row-bar-<?php echo $prod['id']; ?>"></div>
                                </div>
                                <span class="meta-text" id="meta-<?php echo $prod['id']; ?>"></span>
                            </td>
                            <!-- Dynamic cloud URL display box cell holder -->
                            <td id="url-cell-<?php echo $prod['id']; ?>">
                                <span style="color:#bbb; font-style:italic;">Waiting...</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h3>System Terminal Crash Logs (Detailed Debugger):</h3>
    <div id="error-log-zone">System healthy. Waiting for execution triggers...</div>
</div>

<script>
const queueIds = <?php echo json_encode(array_column($preview_products, 'id')); ?>;
const totalRecords = queueIds.length;
let pointerIndex = 0;
let timestampsTrackerList = [];
let isMigrationRunning = false;

function printDebugLog(msg, color = '#f8f8f2') {
    const logZone = document.getElementById('error-log-zone');
    if(logZone.innerText.includes('System healthy.')) logZone.innerHTML = '';
    logZone.innerHTML += `<div style="color:${color}; border-bottom:1px solid #333; padding:4px 0;">[${new Date().toLocaleTimeString()}] ${msg}</div>`;
    logZone.scrollTop = logZone.scrollHeight;
}

function stopMigrationNow() {
    isMigrationRunning = false;
    document.getElementById('stop-btn').style.display = 'none';
    document.getElementById('trigger-btn').disabled = false;
    document.getElementById('trigger-btn').innerText = 'Resume Migration';
    printDebugLog("⚠️ Migration engine PAUSED by the user.", "orange");
}

function initializeCloudinaryMigration() {
    if(totalRecords === 0) return;
    isMigrationRunning = true;
    document.getElementById('trigger-btn').disabled = true;
    document.getElementById('stop-btn').style.display = 'inline-block';
    document.getElementById('global-text').innerText = "Processing batch lines...";
    if(timestampsTrackerList.length === 0) timestampsTrackerList.push(performance.now());
    processRowChunk();
}

function processWithAttemptLimit(url, targetId, maxAttempts = 3) {
    let rowBar = document.getElementById(`row-bar-${targetId}`);
    let dummyInterval = setInterval(() => {
        let currentWidth = parseFloat(rowBar.style.width) || 0;
        if (currentWidth < 85) rowBar.style.width = (currentWidth + Math.random() * 15) + '%';
    }, 100);

    return fetch(url)
        .then(response => {
            clearInterval(dummyInterval);
            if (!response.ok) {
                return response.text().then(bodyText => {
                    throw new Error(`HTTP ${response.status}: Server script execution crashed completely.`);
                });
            }
            return response.json();
        })
        .then(parsedData => {
            if (parsedData.status === 'local_error' || parsedData.status === 'cloudinary_error') {
                throw new Error(parsedData.message);
            }
            rowBar.style.width = '100%';
            return parsedData;
        })
        .catch(error => {
            clearInterval(dummyInterval);
            if (maxAttempts > 1 && isMigrationRunning) {
                printDebugLog(`[RETRY STAGE] Product #${targetId} failed due to: ${error.message}. Retrying step count...`, 'yellow');
                return new Promise(res => setTimeout(res, 2000))
                    .then(() => processWithAttemptLimit(url, targetId, maxAttempts - 1));
            }
            throw error;
        });
}

function calculateGlobalETA(completed, total) {
    let elapsed = (performance.now() - timestampsTrackerList[0]) / 1000;
    let averageTimePerRow = elapsed / completed;
    let totalSecondsLeft = Math.round(averageTimePerRow * (total - completed));
    let hrs = Math.floor(totalSecondsLeft / 3600);
    let mins = Math.floor((totalSecondsLeft % 3600) / 60);
    let secs = totalSecondsLeft % 60;
    return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

function processRowChunk() {
    if (!isMigrationRunning) return;

    if (pointerIndex >= totalRecords) {
        document.getElementById('trigger-btn').disabled = false;
        document.getElementById('stop-btn').style.display = 'none';
        document.getElementById('global-text').innerText = "100% Core Migration Operations Finished!";
        alert("Bulk Batch Operations Finished Successfully!");
        return;
    }

    let targetId = queueIds[pointerIndex];
    let rowTr = document.getElementById(`row-${targetId}`);
    let rowStatusTd = document.getElementById(`status-${targetId}`);
    let rowUrlTd = document.getElementById(`url-cell-${targetId}`);
    
    rowTr.classList.add('active-processing-row');
    rowTr.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    document.getElementById(`badge-lbl-${targetId}`).className = "badge badge-processing";
    document.getElementById(`badge-lbl-${targetId}`).innerText = "Syncing...";
    document.getElementById(`progress-box-${targetId}`).style.display = "block";
    document.getElementById(`row-bar-${targetId}`).style.width = "10%";
    rowUrlTd.innerHTML = `<span style="color:#0366d6; animation: blink 1s linear infinite;">Uploading...</span>`;

    let currentPath = window.location.pathname;
    let requestUrl = `${currentPath}?action=process_row&id=${targetId}`;

    processWithAttemptLimit(requestUrl, targetId, 3)
        .then(resData => {
            rowTr.classList.remove('active-processing-row');
            pointerIndex++;

            let progressPercent = Math.round((pointerIndex / totalRecords) * 100);
            document.getElementById('global-bar').style.width = progressPercent + '%';
            document.getElementById('global-text').innerText = `${progressPercent}% Completed (${pointerIndex}/${totalRecords})`;
            document.getElementById('processed-lbl').innerText = pointerIndex;
            document.getElementById('global-speed-lbl').innerText = resData.speed + " KB/s";
            document.getElementById('eta-lbl').innerText = calculateGlobalETA(pointerIndex, totalRecords);

            document.getElementById(`img-thumb-${targetId}`).src = resData.secure_url;
            document.getElementById(`progress-box-${targetId}`).style.display = "none";
            
            let ratio = Math.round((1 - (resData.comp_size / resData.orig_size)) * 100);
            rowStatusTd.innerHTML = `
                <span class="badge badge-success">Success</span>
                <span class="meta-text">⚡ Crunch: -${ratio}% size</span>
            `;

            // DYNAMIC UPDATE: Inject the clean responsive green link right into the new grid cell row
            rowUrlTd.innerHTML = `<a href="${resData.secure_url}" target="_blank" class="cld-link">View Image Live ↗</a>`;

            setTimeout(processRowChunk, 150);
        })
        .catch(finalErr => {
            rowTr.classList.remove('active-processing-row');
            document.getElementById(`progress-box-${targetId}`).style.display = "none";
            rowStatusTd.innerHTML = `<span class="badge badge-error">Terminal Drop</span>`;
            rowUrlTd.innerHTML = `<span style="color:#cf222e; font-weight:bold;">❌ Failed</span>`;
            
            printDebugLog(`❌ CRITICAL EXCEPTION on Product ID #${targetId}: ${finalErr.message}`, '#ff5555');

            pointerIndex++;
            setTimeout(processRowChunk, 150);
        });
}
</script>
</body>
</html>