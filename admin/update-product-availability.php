<?php
include('includes/config.php');
include('includes/main-header.php');
include('../includes/stock-management.php');

$msg = '';

if (isset($_POST['update_all'])) {
    // Use the stock management function
    $results = bulkUpdateProductAvailability($conn);
    
    $msg = "✅ Updated " . $results['zero_stock_updated'] . " products to 'Out of Stock' and " . $results['in_stock_updated'] . " products to 'In Stock'";
}

if (isset($_POST['update_variants'])) {
    // Update all products availability based on variant stock
    $variant_stats = bulkUpdateProductAvailabilityFromVariants($conn);
    $msg = "✅ Updated {$variant_stats['updated']} products based on variant stock. Errors: {$variant_stats['errors']}";
}

// Get current statistics using the stock management function
$stats = getStockStatistics($conn);
$total_products = $stats['total_products'];
$zero_stock_products = $stats['zero_stock_products'];
$out_of_stock_products = $stats['out_of_stock_products'];
$in_stock_products = $stats['in_stock_products'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Update Product Availability | ApnaBazaar Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-top: 20px; }
        .stats-card { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Update Product Availability</h2>
        <a href="manage-products.php" class="btn btn-primary">← Back to Manage Products</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <h4><?php echo $total_products; ?></h4>
                <p class="text-muted mb-0">Total Products</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h4><?php echo $zero_stock_products; ?></h4>
                <p class="text-muted mb-0">Products with Stock = 0</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h4><?php echo $out_of_stock_products; ?></h4>
                <p class="text-muted mb-0">Marked as "Out of Stock"</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h4><?php echo $in_stock_products; ?></h4>
                <p class="text-muted mb-0">Marked as "In Stock"</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Update All Product Availability</h5>
            <p class="card-text">This will automatically update all products' availability status based on their current stock levels:</p>
            <ul>
                <li>Products with stock = 0 will be marked as "Out of Stock"</li>
                <li>Products with stock > 0 will be marked as "In Stock"</li>
            </ul>
            <form method="post" class="d-inline">
                <button type="submit" name="update_all" class="btn btn-success me-2" onclick="return confirm('Are you sure you want to update all product availability?')">
                    🔄 Update All Product Availability
                </button>
            </form>
            <form method="post" class="d-inline">
                <button type="submit" name="update_variants" class="btn btn-info" onclick="return confirm('Are you sure you want to update all products based on variant stock?')">
                    📦 Update Based on Variant Stock
                </button>
            </form>
        </div>
    </div>

    <div class="mt-4">
        <h5>Products that need attention:</h5>
        <?php
        $mismatched = getProductsNeedingAttention($conn, 10);
        
        if (!empty($mismatched)) {
            echo '<div class="table-responsive"><table class="table table-sm table-bordered">';
            echo '<thead><tr><th>ID</th><th>Product Name</th><th>Stock</th><th>Current Availability</th><th>Should Be</th></tr></thead><tbody>';
            
            foreach ($mismatched as $row) {
                $status_class = ($row['stock'] == 0) ? 'text-danger' : 'text-success';
                
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['productName']}</td>";
                echo "<td>{$row['stock']}</td>";
                echo "<td>{$row['productAvailability']}</td>";
                echo "<td class='$status_class'>{$row['should_be']}</td>";
                echo "</tr>";
            }
            
            echo '</tbody></table></div>';
        } else {
            echo '<div class="alert alert-success">✅ All products have correct availability status!</div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 