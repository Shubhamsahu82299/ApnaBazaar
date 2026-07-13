<?php
include('includes/config.php');

echo "<h2>Checking Orders Table Structure</h2>";

// Check orders table structure
$structure = mysqli_query($con, "DESCRIBE orders");
if ($structure) {
    echo "<strong>Current Orders Table Structure:</strong><br>";
    while ($col = mysqli_fetch_assoc($structure)) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
}

// Check if variant_id column exists
$variant_check = mysqli_query($con, "SHOW COLUMNS FROM orders LIKE 'variant_id'");
if (mysqli_num_rows($variant_check) > 0) {
    echo "<br>✅ variant_id column already exists in orders table<br>";
} else {
    echo "<br>❌ variant_id column does not exist in orders table<br>";
    echo "We need to add this column to store variant information.<br>";
}

// Show sample orders data
$orders = mysqli_query($con, "SELECT * FROM orders LIMIT 3");
if ($orders && mysqli_num_rows($orders) > 0) {
    echo "<br><strong>Sample Orders Data:</strong><br>";
    while ($row = mysqli_fetch_assoc($orders)) {
        echo "- Order ID: " . $row['id'] . ", Product ID: " . $row['productId'] . ", Quantity: " . $row['quantity'] . "<br>";
    }
}
?> 