<?php
include_once('includes/config.php');

echo "<h2>Debug Stock Management System</h2>";

// Check if column exists
echo "<h3>1. Checking Database Structure:</h3>";
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock_management_type'");

if (mysqli_num_rows($checkColumn) == 0) {
    echo "<p style='color: red;'>❌ Column 'stock_management_type' does NOT exist!</p>";
    echo "<p>Need to add the column first.</p>";
} else {
    echo "<p style='color: green;'>✅ Column 'stock_management_type' exists!</p>";
    
    // Show column details
    $columnInfo = mysqli_fetch_assoc($checkColumn);
    echo "<p><strong>Column Type:</strong> " . $columnInfo['Type'] . "</p>";
    echo "<p><strong>Default Value:</strong> " . $columnInfo['Default'] . "</p>";
}

// Check current product data
echo "<h3>2. Checking Product Data:</h3>";
$productQuery = mysqli_query($conn, "SELECT id, productName, stock, stock_management_type, unit_type FROM products WHERE id = 43");
if ($productQuery && mysqli_num_rows($productQuery) > 0) {
    $product = mysqli_fetch_assoc($productQuery);
    echo "<p><strong>Product ID:</strong> " . $product['id'] . "</p>";
    echo "<p><strong>Product Name:</strong> " . $product['productName'] . "</p>";
    echo "<p><strong>Main Stock:</strong> " . $product['stock'] . "</p>";
    echo "<p><strong>Stock Management Type:</strong> " . ($product['stock_management_type'] ?? 'NULL') . "</p>";
    echo "<p><strong>Unit Type:</strong> " . $product['unit_type'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ No product found with ID = 43</p>";
}

// Check variants
echo "<h3>3. Checking Variants:</h3>";
$variantsQuery = mysqli_query($conn, "SELECT id, variant_label, stock, price FROM product_variants WHERE product_id = 43");
if ($variantsQuery && mysqli_num_rows($variantsQuery) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Variant</th><th>Stock</th><th>Price</th></tr>";
    
    while ($variant = mysqli_fetch_assoc($variantsQuery)) {
        echo "<tr>";
        echo "<td>" . $variant['id'] . "</td>";
        echo "<td>" . $variant['variant_label'] . "</td>";
        echo "<td>" . $variant['stock'] . "</td>";
        echo "<td>" . $variant['price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No variants found for product ID = 43</p>";
}

// Test dependent calculation
echo "<h3>4. Testing Dependent Calculation:</h3>";
$mainStock = 3; // Your input
echo "<p><strong>Main Stock:</strong> " . $mainStock . "kg</p>";

$variants = [
    ['label' => '1 Kg', 'size' => 1],
    ['label' => '2 Kg', 'size' => 2],
    ['label' => '5 Kg', 'size' => 5]
];

echo "<p><strong>Expected Variants:</strong></p>";
foreach ($variants as $variant) {
    $expectedStock = floor($mainStock / $variant['size']);
    echo "<p>" . $variant['label'] . ": " . $expectedStock . " pcs</p>";
}

// Test order simulation
echo "<h3>5. Testing Order Simulation:</h3>";
echo "<p><strong>Order:</strong> 1kg variant × 2 pcs</p>";

$orderQuantity = 2;
$variantSize = 1; // 1kg
$mainStockConsumed = $variantSize * $orderQuantity;
$remainingStock = $mainStock - $mainStockConsumed;

echo "<p><strong>Main Stock Consumed:</strong> " . $mainStockConsumed . "kg</p>";
echo "<p><strong>Remaining Main Stock:</strong> " . $remainingStock . "kg</p>";

echo "<p><strong>New Variant Stocks:</strong></p>";
foreach ($variants as $variant) {
    $newStock = floor($remainingStock / $variant['size']);
    echo "<p>" . $variant['label'] . ": " . $newStock . " pcs</p>";
}

// Test update
echo "<h3>6. Testing Update:</h3>";
$updateQuery = "UPDATE products SET stock_management_type = 'dependent', stock = 3 WHERE id = 43";
$updateResult = mysqli_query($conn, $updateQuery);

if ($updateResult) {
    echo "<p style='color: green;'>✅ Update successful!</p>";
    
    // Check again
    $checkQuery = mysqli_query($conn, "SELECT stock_management_type, stock FROM products WHERE id = 43");
    if ($checkQuery && mysqli_num_rows($checkQuery) > 0) {
        $result = mysqli_fetch_assoc($checkQuery);
        echo "<p><strong>Updated Stock Management Type:</strong> " . $result['stock_management_type'] . "</p>";
        echo "<p><strong>Updated Main Stock:</strong> " . $result['stock'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Update failed: " . mysqli_error($conn) . "</p>";
}

// Show all products
echo "<h3>7. All Products:</h3>";
$allProducts = mysqli_query($conn, "SELECT id, productName, stock, stock_management_type FROM products LIMIT 5");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Product Name</th><th>Stock</th><th>Stock Management Type</th></tr>";

while ($row = mysqli_fetch_assoc($allProducts)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['productName'] . "</td>";
    echo "<td>" . $row['stock'] . "</td>";
    echo "<td>" . ($row['stock_management_type'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>8. Next Steps:</h3>";
echo "<p><a href='edit-products.php?id=43' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Test Edit Product</a></p>";
echo "<p><a href='run-sql-setup.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Run SQL Setup</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
</style> 