<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

// --- ENTER YOUR CLOUDINARY CLOUD NAME & PRESET NAME HERE ---
define('CLOUDINARY_CLOUD_NAME', 'dgirf891q'); 
define('CLOUDINARY_UPLOAD_PRESET', 'my_preset'); // Aapka preset name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dropped_image'])) {
    header('Content-Type: application/json');
    
    $file_tmp = $_FILES['dropped_image']['tmp_name'];

    if (!extension_loaded('gd')) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'PHP Extension Error: GD Library is not enabled.']);
        exit;
    }

    $info = getimagesize($file_tmp);
    if ($info === false) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid file format metadata. Upload images only.']);
        exit;
    }

    switch ($info['mime']) {
        case 'image/jpeg': $image = @imagecreatefromjpeg($file_tmp); break;
        case 'image/png': 
            $image = @imagecreatefrompng($file_tmp); 
            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            break;
        case 'image/gif': $image = @imagecreatefromgif($file_tmp); break;
        case 'image/webp': $image = @imagecreatefromwebp($file_tmp); break;
        default: $image = false; break;
    }

    if (!$image) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Image parsing failed. File might be corrupted.']);
        exit;
    }

    $temp_dir = sys_get_temp_dir();
    $temp_webp = $temp_dir . DIRECTORY_SEPARATOR . 'drop_' . uniqid() . '.webp';
    if (!@imagewebp($image, $temp_webp, 55)) {
        imagedestroy($image);
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'GD failed to write temporary compressed WebP file.']);
        exit;
    }
    imagedestroy($image);

    // --- UN SIGNED CLOUDINARY API PAYLOAD ---
    $url = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile($temp_webp),
        'upload_preset' => CLOUDINARY_UPLOAD_PRESET
    ]);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $curl_err = curl_error($ch);
        curl_close($ch);
        if (file_exists($temp_webp)) { unlink($temp_webp); }
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Network Connection Drop: ' . $curl_err]);
        exit;
    }
    curl_close($ch);

    if (file_exists($temp_webp)) { unlink($temp_webp); }

    $cloudinary_data = json_decode($response, true);

    ob_clean();
    if (isset($cloudinary_data['secure_url'])) {
        echo json_encode([
            'status' => 'success',
            'url' => $cloudinary_data['secure_url']
        ]);
    } else {
        $api_err = isset($cloudinary_data['error']['message']) ? $cloudinary_data['error']['message'] : 'Rejected by Cloudinary Service Engine.';
        echo json_encode(['status' => 'error', 'message' => 'Cloudinary API Rejection -> ' . $api_err]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instant Cloudinary Dropzone</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 40px; display: flex; justify-content: center; }
        .uploader-card { width: 100%; max-width: 600px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
        h2 { margin-top: 0; color: #2c3e50; }
        .dropzone { border: 3px dashed #3498db; background: #fdfefe; border-radius: 6px; padding: 50px 20px; cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
        .dropzone.dragover { background: #ebf5fb; border-color: #2980b9; transform: scale(1.02); }
        .dropzone p { margin: 10px 0 0 0; font-size: 16px; color: #566573; font-weight: 500; }
        .dropzone span { font-size: 13px; color: #bdc3c7; margin-top: 5px; }
        #preview-box { margin-top: 25px; display: none; text-align: center; border-top: 2px solid #f2f4f4; padding-top: 20px; }
        .live-preview { max-width: 100%; max-height: 250px; border-radius: 6px; border: 1px solid #d5dbdb; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .status-msg { margin-top: 15px; font-size: 14px; font-weight: bold; }
        .success-text { color: #27ae60; word-break: break-all; }
        .error-text { color: #e74c3c; font-family: monospace; text-align: left; background: #fadbd8; padding: 10px; border-radius: 4px; line-height: 1.5; white-space: pre-wrap; }
        .loader { display: none; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="uploader-card">
    <h2>Instant Cloudinary Dropzone</h2>
    <p style="color: #7f8c8d; margin-bottom: 25px; font-size: 14px;">Images will automatically compress to highly optimized WebP files runtime.</p>
    
    <div class="dropzone" id="dropzone" onclick="document.getElementById('file-input').click()">
        <div class="loader" id="loader"></div>
        <div id="drop-prompt">
            <svg width="48" height="48" fill="none" stroke="#3498db" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <p>Drag & Drop your image here</p>
            <span>or click to browse local files</span>
        </div>
        <input type="file" id="file-input" style="display: none;" accept="image/*" onchange="handleFileSelect(this.files[0])">
    </div>

    <div id="preview-box">
        <img src="" id="output-image" class="live-preview" alt="Cloudinary Live Preview">
        <div id="status-container" class="status-msg"></div>
    </div>
</div>

<script>
const dropzone = document.getElementById('dropzone');
const loader = document.getElementById('loader');
const dropPrompt = document.getElementById('drop-prompt');
const previewBox = document.getElementById('preview-box');
const outputImage = document.getElementById('output-image');
const statusContainer = document.getElementById('status-container');

['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    }, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    }, false);
});

dropzone.addEventListener('drop', (e) => {
    const droppedFiles = e.dataTransfer.files;
    if (droppedFiles.length > 0) {
        handleFileSelect(droppedFiles[0]);
    }
}, false);

function handleFileSelect(file) {
    if (!file || !file.type.startsWith('image/')) {
        alert("Please drop image files only!");
        return;
    }

    dropPrompt.style.display = 'none';
    loader.style.display = 'block';
    previewBox.style.display = 'none';
    statusContainer.innerHTML = '';

    const formData = new FormData();
    formData.append('dropped_image', file);

    fetch('cloudinary_uploader.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text().then(rawText => {
            try {
                return JSON.parse(rawText);
            } catch(e) {
                throw new Error("Raw Output Error From Server:\n" + rawText.substring(0, 300));
            }
        });
    })
    .then(data => {
        loader.style.display = 'none';
        dropPrompt.style.display = 'flex';
        previewBox.style.display = 'block';

        if (data.status === 'success') {
            outputImage.src = data.url;
            statusContainer.innerHTML = `<span class="success-text">🎉 Upload Success!<br><a href="${data.url}" target="_blank">${data.url}</a></span>`;
        } else {
            outputImage.src = 'https://placehold.co/400x200?text=Upload+Failed';
            statusContainer.innerHTML = `<div class="error-text"><strong>Server Check Failure:</strong>\n${data.message}</div>`;
        }
    })
    .catch(err => {
        loader.style.display = 'none';
        dropPrompt.style.display = 'flex';
        previewBox.style.display = 'block';
        outputImage.src = 'https://placehold.co/400x200?text=System+Error';
        statusContainer.innerHTML = `<div class="error-text"><strong>System Capture:</strong>\n${err.message}</div>`;
    });
}
</script>
</body>
</html>