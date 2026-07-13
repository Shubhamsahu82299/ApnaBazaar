<?php
// Script to add price columns to orders table
include 'includes/config.php';

echo "Adding price columns to orders table...\n";

// Check if columns already exist
$check_query = "SHOW COLUMNS FROM orders LIKE 'buy_price_at_order_time'";
$result = mysqli_query($con, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "Columns already exist! Skipping...\n";
} else {
    // Add the columns
    $alter_query = "ALTER TABLE orders 
                    ADD COLUMN buy_price_at_order_time DECIMAL(10,2) DEFAULT 0.00 AFTER deliveryPaymentMethod,
                    ADD COLUMN sell_price_at_order_time DECIMAL(10,2) DEFAULT 0.00 AFTER buy_price_at_order_time";
    
    if (mysqli_query($con, $alter_query)) {
        echo "✅ Successfully added price columns to orders table!\n";
        echo "Added columns:\n";
        echo "- buy_price_at_order_time\n";
        echo "- sell_price_at_order_time\n";
    } else {
        echo "❌ Error adding columns: " . mysqli_error($con) . "\n";
    }
}

// Show the updated table structure
echo "\nUpdated orders table structure:\n";
$structure_query = "DESCRIBE orders";
$result = mysqli_query($con, $structure_query);

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

mysqli_close($con);
echo "\nDatabase changes completed!\n";
?>
