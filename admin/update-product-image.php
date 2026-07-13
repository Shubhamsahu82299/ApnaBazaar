<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/config.php'); 
session_start();

$msg = '';
$selectedProduct = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

// Handle image update
if (isset($_POST['update_image']) && $selectedProduct > 0) {
    $imgField = $_POST['img_field'];
    $oldImage = $_POST['old_image'];
    $dir = "productimages/$selectedProduct";

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $newImage = '';

    // Handle cropped base64 image
    if (!empty($_POST['cropped_image_data'])) {
        $data = $_POST['cropped_image_data'];
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $data = base64_decode($data);

            $ext = strtolower($type[1]);
            $newname = uniqid() . '.' . $ext;
            $destination = "$dir/$newname";

            if (file_put_contents($destination, $data)) {
                if (!empty($oldImage) && strpos($oldImage, 'https://res.cloudinary.com') === false) {
                    if (file_exists("$dir/$oldImage")) {
                        unlink("$dir/$oldImage");
                    }
                }
                $newImage = $newname;
            }
        }
    } else if (!empty($_FILES['product_image']['name'])) {
        // Fallback to regular file upload
        function uploadImage($file, $dir, $oldImage) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];

            if ($file['error'] === 0) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $mime = mime_content_type($file['tmp_name']);

                if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime)) {
                    $newname = uniqid() . '.' . $ext;
                    $destination = "$dir/$newname";

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        if (!empty($oldImage) && strpos($oldImage, 'https://res.cloudinary.com') === false) {
                            if (file_exists("$dir/$oldImage")) {
                                unlink("$dir/$oldImage");
                            }
                        }
                        return $newname;
                    }
                }
            }
            return '';
        }

        $newImage = uploadImage($_FILES['product_image'], $dir, $oldImage);
    }

    if ($newImage) {
        mysqli_query($conn, "UPDATE products SET $imgField = '$newImage' WHERE id = $selectedProduct");
        $msg = "✅ Image replaced locally! The background sync panel will compress and push it to Cloudinary shortly.";
    } else {
        $msg = "❌ Failed to process or upload image asset.";
    }
}

// Get selected product data
$productData = null;
if ($selectedProduct > 0) {
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $selectedProduct");
    $productData = mysqli_fetch_assoc($res);
}

// Get all products list tracker dropdown
$productList = mysqli_query($conn, "SELECT id, productName FROM products ORDER BY productName ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Product Images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet"/>
    <style>
        .current-img:hover {
            opacity: 0.85;
            border-color: #0d9488 !important;
            box-shadow: 0 4px 10px rgba(13,148,136,0.1);
        }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow-sm border" style="max-width: 1100px; margin: 20px auto;">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h2 class="mb-0 text-primary fw-bold">🖼️ Manage Product Images</h2>
                <small class="text-muted">Crop, change or upload high-resolution replacement image slots safely.</small>
            </div>
            <a href="manage-products.php" class="btn btn-sm btn-outline-secondary fw-semibold">
                🔙 Go Back
            </a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-info alert-dismissible fade show"><?php echo htmlentities($msg); ?></div>
        <?php endif; ?>

        <!-- Product Selector Component Form Layout Grid -->
        <form method="post" class="mb-4" id="productForm">
            <label class="form-label fw-bold">🔍 Search & Target Product Line:</label>
            <select name="product_id" class="form-select select2" onchange="document.getElementById('productForm').submit();" required>
                <option value="">-- Type name to search product... --</option>
                <?php while ($row = mysqli_fetch_assoc($productList)): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($selectedProduct == $row['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlentities($row['productName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($productData): ?>
            <h5 class="mb-4 text-secondary">📦 Selected Item: <strong class="text-dark"><?php echo htmlentities($productData['productName']); ?></strong></h5>
            <div class="row">
                <?php for ($i = 1; $i <= 3; $i++): 
                    $imgField = "productImage$i";
                    $imgValue = $productData[$imgField];
                    
                    $resolvedImgPath = getProductImage($productData['id'], $imgValue);
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border shadow-sm">
                        <div class="p-3 text-center bg-light border-bottom d-flex align-items-center justify-content-center" style="height: 220px;">
                            <?php if ($imgValue): ?>
                                <img src="<?php echo $resolvedImgPath; ?>" 
                                     class="img-fluid current-img border rounded p-1 bg-white" 
                                     style="max-height: 100%; object-fit: contain; cursor: pointer; transition: all 0.2s;" 
                                     data-img="<?php echo $resolvedImgPath; ?>"
                                     title="Click image box to recrop this item grid frame layout"
                                     onerror="this.src='https://via.placeholder.com/200x150?text=File+Not+Found';">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/200x150?text=No+Image+Uploaded" class="img-fluid opacity-50" style="max-height: 100%; object-fit: contain;">
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-3">
                            <form method="post" enctype="multipart/form-data" class="image-upload-form">
                                <input type="hidden" name="product_id" value="<?php echo $productData['id']; ?>">
                                <input type="hidden" name="img_field" value="<?php echo $imgField; ?>">
                                <input type="hidden" name="old_image" value="<?php echo $imgValue; ?>">
                                <input type="hidden" name="cropped_image_data" class="cropped-image-data">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Upload Replacement:</label>
                                    <input type="file" name="product_image" class="form-control form-control-sm image-input" accept="image/*">
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-warning w-100 crop-btn fw-bold text-dark">✂️ Initialize Crop Preview</button>
                                <button type="submit" name="update_image" class="btn btn-sm btn-success w-100 mt-2 fw-bold">Update Slot <?php echo $i; ?></button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endfor; ?> <!-- Fixed here: changed from endforeach to endfor -->
            </div>
        <?php endif; ?>
    </div>

    <!-- Fullscreen Cropper Modal Backdrop Panel -->
    <div class="modal fade" id="cropModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">✂️ Adjust Crop Box Alignment Framework</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="if(cropper) cropper.destroy();"></button>
                </div>
                <div class="modal-body text-center p-3 bg-secondary-subtle">
                    <div class="img-container rounded overflow-hidden shadow-inner bg-white" style="max-height: 500px;">
                        <img id="cropPreview" style="max-width: 100%; max-height: 500px;" alt="Crop Preview Buffer Canvas">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal" onclick="if(cropper) cropper.destroy();">Cancel</button>
                    <button id="cropBtn" class="btn btn-sm btn-primary px-4 fw-bold">Crop & Apply Selection ➔</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script>
        $('.select2').select2({
            width: '100%',
            allowClear: true
        });

        let cropper;
        let currentInput, currentForm;

        document.querySelectorAll('.crop-btn').forEach(button => {
            button.addEventListener('click', function () {
                const form = button.closest('form');
                const input = form.querySelector('.image-input');
                const file = input.files[0];
                if (!file) return alert("Please select a new local file from your system directory first.");

                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('cropPreview').src = e.target.result;
                    const modal = new bootstrap.Modal(document.getElementById('cropModal'));
                    modal.show();

                    if (cropper) cropper.destroy();
                    cropper = new Cropper(document.getElementById('cropPreview'), {
                        viewMode: 1,
                        autoCropArea: 0.9,
                        responsive: true
                    });

                    currentInput = input;
                    currentForm = form;
                };
                reader.readAsDataURL(file);
            });
        });

        document.querySelectorAll('.current-img').forEach(img => {
            img.addEventListener('click', function () {
                let imgSrc = this.dataset.img;
                if(imgSrc.startsWith('http')) {
                    alert("This image is already hosted on Cloudinary secure network storage layers. To re-crop, please choose a new file to upload.");
                    return;
                }
                
                document.getElementById('cropPreview').src = imgSrc;
                const modal = new bootstrap.Modal(document.getElementById('cropModal'));
                modal.show();

                if (cropper) cropper.destroy();
                cropper = new Cropper(document.getElementById('cropPreview'), {
                    viewMode: 1,
                    autoCropArea: 0.9
                });

                currentForm = img.closest('.card').querySelector('form');
                currentInput = currentForm.querySelector('.image-input');
            });
        });

        document.getElementById('cropBtn').addEventListener('click', function () {
            if (!cropper) return;
            cropper.getCroppedCanvas().toBlob(blob => {
                const reader = new FileReader();
                reader.onloadend = function () {
                    if (currentForm) {
                        currentForm.querySelector('.cropped-image-data').value = reader.result;
                        bootstrap.Modal.getInstance(document.getElementById('cropModal')).hide();
                        setTimeout(() => currentForm.submit(), 250);
                    }
                };
                reader.readAsDataURL(blob);
            }, 'image/jpeg', 0.9);
        });
    </script>
</body>
</html>