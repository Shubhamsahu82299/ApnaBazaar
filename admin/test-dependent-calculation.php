<?php
include_once('includes/config.php');
include_once('includes/stock-management.php'); // Ensure stock-management functions are available

echo "<h2>Testing Dependent Calculation Logic</h2>";

// Test scenario
$mainStock = 3; // kg
$orderQuantity = 2; // pcs
$variantLabel = "1 Kg";
$variantSize = 1; // kg

echo "<h3>Test Scenario:</h3>";
echo "<p><strong>Main Stock:</strong> $mainStock kg</p>";
echo "<p><strong>Order:</strong> $orderQuantity pcs × $variantLabel</p>";

// Calculate main stock consumed
$mainStockConsumed = $variantSize * $orderQuantity;
echo "<p><strong>Main Stock Consumed:</strong> $mainStockConsumed kg</p>";

// Calculate remaining stock
$remainingStock = $mainStock - $mainStockConsumed;
echo "<p><strong>Remaining Stock:</strong> $remainingStock kg</p>";

// Calculate new variant stocks
$variants = [
    ['label' => '1 Kg', 'size' => 1],
    ['label' => '2 Kg', 'size' => 2],
    ['label' => '5 Kg', 'size' => 5]
];

echo "<h3>New Variant Stocks:</h3>";
foreach ($variants as $variant) {
    $newStock = floor($remainingStock / $variant['size']);
    echo "<p><strong>{$variant['label']}:</strong> $newStock pcs</p>";
}

// Test the actual function
echo "<h3>Testing calculateMainStockFromVariant Function:</h3>";
$testConsumed = calculateMainStockFromVariant($variantLabel, $orderQuantity);
echo "<p><strong>Function Result:</strong> $testConsumed kg consumed</p>";

// Test with different scenarios
echo "<h3>Different Scenarios:</h3>";

$scenarios = [
    ['main' => 3, 'order' => 2, 'variant' => '1 Kg', 'expected' => 2],
    ['main' => 3, 'order' => 1, 'variant' => '2 Kg', 'expected' => 2],
    ['main' => 5, 'order' => 1, 'variant' => '1 Kg', 'expected' => 1],
    ['main' => 10, 'order' => 2, 'variant' => '2 Kg', 'expected' => 4]
];

foreach ($scenarios as $scenario) {
    $consumed = calculateMainStockFromVariant($scenario['variant'], $scenario['order']);
    $remaining = $scenario['main'] - $consumed;
    
    echo "<p><strong>Scenario:</strong> {$scenario['main']}kg - {$scenario['order']}pcs × {$scenario['variant']}</p>";
    echo "<p>Consumed: $consumed kg, Remaining: $remaining kg</p>";
    echo "<hr>";
}

// Test regex pattern
echo "<h3>Testing Regex Pattern:</h3>";
$testLabels = ['1 Kg', '2 Kg', '5 Kg', '250 gm', '500 gm', '1 Ltr', '1 pc', '5 pcs', '10 pcs', '1 Kg'];

foreach ($testLabels as $label) {
    if (preg_match('/(\d+(?:\.\d+)?)\s*(kg|Kg|gm|ltr|ml|pc|pcs)/i', $label, $matches)) {
        $size = floatval($matches[1]);
        $unit = strtolower($matches[2]);
        echo "<p><strong>$label:</strong> Size=$size, Unit=$unit</p>";
    } else {
        echo "<p><strong>$label:</strong> No match</p>";
    }
}

// Test current product data
echo "<h3>Current Product Data (ID=43):</h3>";
$productQuery = mysqli_query($conn, "SELECT id, productName, stock, stock_management_type, unit_type FROM products WHERE id = 43");
if ($productQuery && mysqli_num_rows($productQuery) > 0) {
    $product = mysqli_fetch_assoc($productQuery);
    echo "<p><strong>Product:</strong> {$product['productName']}</p>";
    echo "<p><strong>Main Stock:</strong> {$product['stock']} kg</p>";
    echo "<p><strong>Management Type:</strong> {$product['stock_management_type']}</p>";
    echo "<p><strong>Unit Type:</strong> {$product['unit_type']}</p>";
    
    // Show variants
    $variantsQuery = mysqli_query($conn, "SELECT variant_label, stock FROM product_variants WHERE product_id = 43");
    echo "<p><strong>Current Variants:</strong></p>";
    while ($variant = mysqli_fetch_assoc($variantsQuery)) {
        echo "<p>{$variant['variant_label']}: {$variant['stock']} pcs</p>";
    }
}

// Test order simulation
echo "<h3>Order Simulation:</h3>";
echo "<p>If user orders 1kg × 2 pcs:</p>";

$orderSize = 1; // 1kg
$orderQty = 2; // 2 pcs
$currentMainStock = $product['stock'] ?? 3;
$consumed = $orderSize * $orderQty;
$newMainStock = $currentMainStock - $consumed;

echo "<p><strong>Current Main Stock:</strong> $currentMainStock kg</p>";
echo "<p><strong>Order Consumes:</strong> $consumed kg</p>";
echo "<p><strong>New Main Stock:</strong> $newMainStock kg</p>";

if ($newMainStock <= 0) {
    echo "<p style='color: red;'><strong>⚠️ Problem:</strong> Main stock will be 0 or negative!</p>";
    echo "<p>This is why product goes 'Out of Stock'</p>";
} else {
    echo "<p style='color: green;'><strong>✅ OK:</strong> Main stock remains positive</p>";
}

// Test GM scenarios with exact calculations
echo "<h3>Testing GM Scenarios (Exact Calculations):</h3>";
$gmScenarios = [
    ['main' => 1.0, 'order' => 1, 'variant' => '100 gm', 'expected' => 0.1],
    ['main' => 1.0, 'order' => 2, 'variant' => '250 gm', 'expected' => 0.5],
    ['main' => 2.0, 'order' => 1, 'variant' => '500 gm', 'expected' => 0.5],
    ['main' => 1.0, 'order' => 4, 'variant' => '100 gm', 'expected' => 0.4],
    ['main' => 5.0, 'order' => 1, 'variant' => '1 Kg', 'expected' => 1.0],
    ['main' => 2.0, 'order' => 1, 'variant' => '500 gm', 'expected' => 0.5],
    ['main' => 1.0, 'order' => 1, 'variant' => '250 gm', 'expected' => 0.25]
];

foreach ($gmScenarios as $scenario) {
    $consumed = calculateMainStockFromVariant($scenario['variant'], $scenario['order']);
    $remaining = $scenario['main'] - $consumed;
    
    echo "<p><strong>Scenario:</strong> {$scenario['main']}kg - {$scenario['order']}pcs × {$scenario['variant']}</p>";
    echo "<p>Consumed: $consumed kg, Remaining: $remaining kg</p>";
    echo "<hr>";
}

// Test PCS scenarios with exact calculations
echo "<h3>Testing PCS Scenarios (Exact Calculations):</h3>";
$pcsScenarios = [
    ['main' => 10, 'order' => 1, 'variant' => '2 pcs', 'expected' => 2],
    ['main' => 20, 'order' => 1, 'variant' => '5 pcs', 'expected' => 5],
    ['main' => 15, 'order' => 3, 'variant' => '1 pc', 'expected' => 3],
    ['main' => 8, 'order' => 2, 'variant' => '2 pcs', 'expected' => 4],
    ['main' => 12, 'order' => 1, 'variant' => '1 pc', 'expected' => 1]
];

foreach ($pcsScenarios as $scenario) {
    $consumed = calculateMainStockFromVariant($scenario['variant'], $scenario['order']);
    $remaining = $scenario['main'] - $consumed;
    
    echo "<p><strong>Scenario:</strong> {$scenario['main']}pcs - {$scenario['order']}pcs × {$scenario['variant']}</p>";
    echo "<p>Consumed: $consumed pcs, Remaining: $remaining pcs</p>";
    echo "<hr>";
}

// Test Float Value Support
echo "<h3>Testing Float Value Support:</h3>";
echo "<p><strong>Example:</strong> Main stock 1.0kg, Order 100gm × 1pc</p>";
$mainStockFloat = 1.0;
$orderFloat = calculateMainStockFromVariant("100 gm", 1);
$remainingFloat = $mainStockFloat - $orderFloat;

echo "<p><strong>Main Stock:</strong> $mainStockFloat kg</p>";
echo "<p><strong>Order Consumes:</strong> $orderFloat kg (100gm = 0.1kg)</p>";
echo "<p><strong>Remaining:</strong> $remainingFloat kg</p>";

if ($remainingFloat == 0.9) {
    echo "<p style='color: green;'><strong>✅ Perfect!</strong> Float calculation working correctly</p>";
} else {
    echo "<p style='color: red;'><strong>❌ Issue:</strong> Float calculation not working</p>";
}

// Test more GM conversions
echo "<h3>Testing GM to KG Conversions:</h3>";
$gmTests = [
    ['variant' => '500 gm', 'expected' => 0.5],
    ['variant' => '100 gm', 'expected' => 0.1],
    ['variant' => '250 gm', 'expected' => 0.25],
    ['variant' => '750 gm', 'expected' => 0.75]
];

foreach ($gmTests as $test) {
    $consumed = calculateMainStockFromVariant($test['variant'], 1);
    echo "<p><strong>{$test['variant']}:</strong> $consumed kg (Expected: {$test['expected']} kg)</p>";
}

?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
p { margin: 5px 0; }
hr { margin: 15px 0; }
</style> 