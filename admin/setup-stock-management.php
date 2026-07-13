<?php
include_once('includes/config.php');

echo "<h2>Stock Management Setup</h2>";

// Check if column exists
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock_management_type'");

if (mysqli_num_rows($checkColumn) == 0) {
    // Column doesn't exist, add it
    $sql = "ALTER TABLE products ADD COLUMN stock_management_type ENUM('independent', 'dependent') DEFAULT 'independent'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Column 'stock_management_type' added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Column 'stock_management_type' already exists!</p>";
}

// Show current table structure
echo "<h3>Current Products Table Structure:</h3>";
$result = mysqli_query($conn, "DESCRIBE products");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test update
echo "<h3>Testing Stock Management:</h3>";
$testUpdate = mysqli_query($conn, "UPDATE products SET stock_management_type = 'dependent' WHERE id = 1 LIMIT 1");
if ($testUpdate) {
    echo "<p style='color: green;'>✅ Test update successful!</p>";
} else {
    echo "<p style='color: red;'>❌ Test update failed: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='edit-products.php?id=1'>Test Edit Product</a></p>";
?> 