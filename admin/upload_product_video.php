<?php
include('includes/config.php');
session_start();

$msg = '';
$selectedProduct = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

// Remove video
if(isset($_POST['remove_video']) && $selectedProduct>0){
    $dir = "productimages/$selectedProduct";
    $res = mysqli_query($conn,"SELECT productVideo FROM products WHERE id=$selectedProduct");
    $row = mysqli_fetch_assoc($res);
    if($row['productVideo'] && file_exists("$dir/".$row['productVideo'])){
        unlink("$dir/".$row['productVideo']);
    }
    mysqli_query($conn,"UPDATE products SET productVideo='' WHERE id=$selectedProduct");
    $msg="✅ Video removed successfully.";
}

// Get selected product
$productData = null;
if($selectedProduct>0){
    $res = mysqli_query($conn,"SELECT * FROM products WHERE id=$selectedProduct");
    $productData = mysqli_fetch_assoc($res);
}

// Get all products
$productList = mysqli_query($conn,"SELECT id, productName FROM products ORDER BY productName ASC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Upload Product Video</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
#drop-area {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    color: #aaa;
    margin-bottom: 20px;
    cursor: pointer;
}
#drop-area.hover { border-color: #007bff; color: #007bff; }
#preview { margin-top: 20px; max-width: 100%; display:block; }
#progress-container { display:none; margin-top:10px; }
</style>
</head>
<body>
<div class="container mt-5">
    <h2>🎬 Upload Product Video</h2>

    <?php if($msg): ?>
        <div class="alert alert-info"><?php echo htmlentities($msg); ?></div>
    <?php endif; ?>

    <form method="post" class="mb-4" id="productForm">
        <label><strong>Select Product:</strong></label>
        <select name="product_id" class="form-select" onchange="document.getElementById('productForm').submit();" required>
            <option value="">-- Select Product --</option>
            <?php while($row=mysqli_fetch_assoc($productList)): ?>
                <option value="<?php echo $row['id'];?>" <?php echo ($selectedProduct==$row['id'])?'selected':''; ?>>
                    <?php echo htmlentities($row['productName']);?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if($productData): 
        $dir="productimages/".$productData['id'];
        $existingVideo = $productData['productVideo'] && file_exists("$dir/".$productData['productVideo']) ? "$dir/".$productData['productVideo'] : '';
    ?>
        <div id="drop-area">
            <p>Drag & Drop video here or click to select</p>
            <input type="file" id="videoInput" accept="video/*" style="display:none;">
        </div>

        <?php if($existingVideo): ?>
            <video id="preview" controls>
                <source src="<?php echo $existingVideo;?>" type="video/mp4">
                Your browser does not support video.
            </video>
            <form method="post" style="margin-top:10px;">
                <input type="hidden" name="product_id" value="<?php echo $productData['id'];?>">
                <button type="submit" name="remove_video" class="btn btn-danger">🗑 Remove Video</button>
            </form>
        <?php else: ?>
            <video id="preview" controls style="display:none;"></video>
        <?php endif; ?>

        <div id="progress-container" class="progress">
            <div id="progress-bar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
        </div>

        <button id="uploadBtn" class="btn btn-primary mt-3">Upload Video</button>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const dropArea = document.getElementById('drop-area');
const videoInput = document.getElementById('videoInput');
const preview = document.getElementById('preview');
const uploadBtn = document.getElementById('uploadBtn');
const progressBar = document.getElementById('progress-bar');
const progressContainer = document.getElementById('progress-container');

let selectedFile = null;

dropArea.addEventListener('click', ()=> videoInput.click());
['dragenter','dragover'].forEach(ev=>{
    dropArea.addEventListener(ev,e=>{ e.preventDefault(); e.stopPropagation(); dropArea.classList.add('hover'); });
});
['dragleave','drop'].forEach(ev=>{
    dropArea.addEventListener(ev,e=>{ e.preventDefault(); e.stopPropagation(); dropArea.classList.remove('hover'); });
});
dropArea.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
videoInput.addEventListener('change', e => handleFiles(videoInput.files));

function handleFiles(files){
    const file = files[0];
    if(!file.type.startsWith('video/')) return alert('Please select a video file.');
    selectedFile = file;

    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.style.display='block';
}

// Upload
uploadBtn.addEventListener('click', ()=>{
    const productId = document.querySelector('select[name="product_id"]').value;
    if(!productId) return alert('Select a product first.');
    if(!selectedFile) return alert('Select a video first.');

    uploadBtn.disabled = true;
    progressContainer.style.display='block';
    progressBar.style.width='0%';
    progressBar.textContent='0%';

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('product_video', selectedFile);
    formData.append('upload_video',1);

    const xhr = new XMLHttpRequest();
    xhr.open('POST','upload_product_video_progress.php',true);

    xhr.upload.addEventListener('progress', e=>{
        if(e.lengthComputable){
            const percent = Math.floor((e.loaded/e.total)*100);
            progressBar.style.width = percent+'%';
            progressBar.textContent = percent+'%';
        }
    });

    xhr.onload = function(){
        const res = JSON.parse(this.responseText);
        alert(res.message);
        uploadBtn.disabled=false;
        progressBar.style.width='100%';
        progressBar.textContent='100%';
        location.reload();
    };

    xhr.send(formData);
});
</script>
</body>
</html>
