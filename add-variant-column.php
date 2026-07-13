<?php
include('includes/config.php');

echo "<h2>Adding variant_id Column to Orders Table</h2>";

// Check if variant_id column exists
$variant_check = mysqli_query($con, "SHOW COLUMNS FROM orders LIKE 'variant_id'");
if (mysqli_num_rows($variant_check) > 0) {
    echo "✅ variant_id column already exists in orders table<br>";
} else {
    echo "❌ variant_id column does not exist. Adding it now...<br>";
    
    // Add variant_id column
    $alter_query = "ALTER TABLE orders ADD COLUMN variant_id INT NULL AFTER productId";
    if (mysqli_query($con, $alter_query)) {
        echo "✅ Successfully added variant_id column to orders table<br>";
    } else {
        echo "❌ Error adding variant_id column: " . mysqli_error($con) . "<br>";
    }
}

// Show updated table structure
$structure = mysqli_query($con, "DESCRIBE orders");
if ($structure) {
    echo "<br><strong>Updated Orders Table Structure:</strong><br>";
    while ($col = mysqli_fetch_assoc($structure)) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
}

echo "<br><a href='index.php'>← Back to Home</a>";
?> 