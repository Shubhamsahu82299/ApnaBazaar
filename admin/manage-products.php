<?php
include('includes/main-header.php');
include('../includes/stock-management.php');

// Auto-update product availability when page loads (considering both product and variant stock)
bulkUpdateProductAvailabilityFromVariants($conn);

// Delete Product
if (isset($_GET['del']) && isset($_GET['id'])) {
    mysqli_query($conn, "DELETE FROM products WHERE id = '" . intval($_GET['id']) . "'");
    $msg = "Product deleted successfully!";
}

// Manual update availability button
if (isset($_GET['update_availability'])) {
    $results = bulkUpdateProductAvailabilityFromVariants($conn);
    $msg = "Product availability updated successfully! Updated " . $results['updated'] . " products based on total stock (product + variants). Errors: " . $results['errors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Products | ApnaBazaar Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        h2 { margin-bottom: 20px; }
        .action-icons a { margin-right: 10px; font-size: 18px; }
        .alert { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container table-container">
 <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Manage Products</h2>
    <div>
        <a href="update-product-image.php" class="btn btn-sm btn-outline-primary me-2">
            🔄 Change Product Image
        </a>
        <a href="update-product-availability.php" class="btn btn-sm btn-outline-info me-2">
            📊 Manage Availability
        </a>
        <a href="?update_availability=1" class="btn btn-sm btn-outline-success">
            🔄 Update Availability
        </a>
        <a href="upload_product_video.php" class="btn btn-sm btn-outline-success">
            Upload_Product_Video
        </a>
        <a href="manage_product_video.php" class="btn btn-sm btn-outline-success">
           manage_product_video
        </a>
        <a href="edit/index.php" class="btn btn-sm btn-outline-success">
           edit product variants
        </a>
    </div>
</div>


    <?php if (isset($msg)) { ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php } ?>

    <table id="productTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Company</th>
                <th>Product Stock</th>
                <th>Variants</th>
                <th>Variant Stock</th>
                <th>Total Stock</th>
                <th>Availability</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $query = mysqli_query($conn, "SELECT products.*, category.categoryName, subcategory.subcategory AS subcat 
                                      FROM products 
                                      JOIN category ON category.id = products.category 
                                      JOIN subcategory ON subcategory.id = products.subCategory");
        $cnt = 1;
        while ($row = mysqli_fetch_array($query)) {
            // Get variant stock for this product
           $variant_query = mysqli_query($conn, 
    "SELECT SUM(stock) as total_variant_stock, COUNT(*) as variant_count 
     FROM product_variants 
     WHERE product_id = " . $row['id'] . " 
       AND slot = '1'");

            $variant_result = mysqli_fetch_assoc($variant_query);
            $variant_stock = intval($variant_result['total_variant_stock']);
            $variant_count = intval($variant_result['variant_count']);
            
            // Calculate total stock
            $product_stock = intval($row['stock']);
            $total_stock = $product_stock + $variant_stock;
        ?>
            <tr>
                <td><?php echo $cnt++; ?></td>
                <td><?php echo htmlentities($row['productName']); ?></td>
                <td><?php echo htmlentities($row['categoryName']); ?></td>
                <td><?php echo htmlentities($row['subcat']); ?></td>
                <td><?php echo htmlentities($row['productCompany']); ?></td>
                <td>
                    <?php
                        if ($product_stock == 0) {
                            echo '<span class="badge bg-danger">0</span>';
                        } elseif ($product_stock <= 5) {
                            echo "<span class='badge bg-warning text-dark'>$product_stock</span>";
                        } else {
                            echo $product_stock;
                        }
                    ?>
                </td>
                <td>
                    <?php
                        if ($variant_count == 0) {
                            echo '<span class="badge bg-secondary">None</span>';
                        } else {
                            echo "<span class='badge bg-primary'>$variant_count</span>";
                        }
                    ?>
                </td>
                <td>
                    <?php
                        if ($variant_stock == 0) {
                            echo '<span class="badge bg-secondary">No Variants</span>';
                        } else {
                            // Get variant details for tooltip
                            $variant_details_query = mysqli_query($conn, "SELECT variant_label, stock FROM product_variants WHERE product_id = " . $row['id'] . " AND stock > 0");
                            $variant_details = [];
                            while ($v = mysqli_fetch_assoc($variant_details_query)) {
                                $variant_details[] = $v['variant_label'] . ': ' . $v['stock'];
                            }
                            $tooltip_text = implode('<br>', $variant_details);
                            
                            if ($variant_stock <= 5) {
                                echo "<span class='badge bg-warning text-dark' data-bs-toggle='tooltip' data-bs-html='true' title='$tooltip_text'>$variant_stock</span>";
                            } else {
                                echo "<span class='badge bg-info' data-bs-toggle='tooltip' data-bs-html='true' title='$tooltip_text'>$variant_stock</span>";
                            }
                        }
                    ?>
                </td>
                <td>
                    <?php
                        if ($total_stock == 0) {
                            echo '<span class="badge bg-danger">Out of Stock</span>';
                        } elseif ($total_stock <= 5) {
                            echo "<span class='badge bg-warning text-dark'>$total_stock Left</span>";
                        } else {
                            echo "<span class='badge bg-success'>$total_stock</span>";
                        }
                    ?>
                </td>
                <td>
                    <?php
                        $availability = $row['productAvailability'];
                        if ($availability == 'In Stock') {
                            echo '<span class="badge bg-success">In Stock</span>';
                        } else {
                            echo '<span class="badge bg-danger">Out of Stock</span>';
                        }
                    ?>
                </td>
                <td><?php echo htmlentities($row['postingDate']); ?></td>
                <td class="action-icons">
                    <a href="edit-products.php?id=<?php echo $row['id']; ?>" class="text-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                    <a href="?id=<?php echo $row['id']; ?>&del=delete" onclick="return confirm('Delete this product?')" class="text-danger" title="Delete"><i class="bi bi-trash-fill"></i></a>
                <a href="slot2-manage.php?id=<?php echo $row['id']; ?>" 
       class="text-warning" title="Manage Slot 2">
        <i class="bi bi-gear-fill">slot2</i>
    </a>
                </td>
                
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script>
$(document).ready(function() {
    $('#productTable').DataTable();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
</body>
</html>
