<?php
// Script to update existing orders with their original prices
// This will populate the new price columns for historical orders
// Properly handles variants vs base product prices

include 'includes/config.php';

echo "Starting to update existing orders with original prices...\n";
echo "This will handle both variants and base product prices correctly.\n\n";

// Get all orders that don't have prices set
$query = "SELECT o.id, o.productId, o.variant_id, o.quantity 
          FROM orders o 
          WHERE o.buy_price_at_order_time = 0.00 
          OR o.sell_price_at_order_time = 0.00
          OR o.buy_price_at_order_time IS NULL 
          OR o.sell_price_at_order_time IS NULL";

$result = mysqli_query($con, $query);

if (!$result) {
    echo "Error: " . mysqli_error($con) . "\n";
    exit;
}

$updated_count = 0;
$error_count = 0;
$variant_orders = 0;
$base_orders = 0;

while ($order = mysqli_fetch_assoc($result)) {
    $order_id = $order['id'];
    $product_id = $order['productId'];
    $variant_id = $order['variant_id'];
    $quantity = $order['quantity'];
    
    $buy_price = 0.00;
    $sell_price = 0.00;
    
    // Get product base prices first
    $product_query = "SELECT base_buy_price, productPrice FROM products WHERE id = ?";
    $stmt = mysqli_prepare($con, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    $product_data = mysqli_fetch_assoc($product_result);
    
    if (!$product_data) {
        echo "❌ Product not found for order $order_id (Product ID: $product_id)\n";
        $error_count++;
        continue;
    }
    
    $base_buy_price = $product_data['base_buy_price'] ?? 0.00;
    $base_sell_price = $product_data['productPrice'] ?? 0.00;
    
    // If variant exists, get variant prices
    if ($variant_id) {
        $variant_query = "SELECT variant_buy_price, price FROM product_variants WHERE id = ?";
        $stmt = mysqli_prepare($con, $variant_query);
        mysqli_stmt_bind_param($stmt, "i", $variant_id);
        mysqli_stmt_execute($stmt);
        $variant_result = mysqli_stmt_get_result($stmt);
        $variant_data = mysqli_fetch_assoc($variant_result);
        
        if ($variant_data) {
            $buy_price = $variant_data['variant_buy_price'] ?? $base_buy_price;
            $sell_price = $variant_data['price'] ?? $base_sell_price;
            $variant_orders++;
            echo "✅ Order $order_id: Using VARIANT prices (Buy: ₹$buy_price, Sell: ₹$sell_price)\n";
        } else {
            // Variant not found, use base product prices
            $buy_price = $base_buy_price;
            $sell_price = $base_sell_price;
            $base_orders++;
            echo "⚠️ Order $order_id: Variant $variant_id not found, using BASE product prices (Buy: ₹$buy_price, Sell: ₹$sell_price)\n";
        }
    } else {
        // No variant, use base product prices
        $buy_price = $base_buy_price;
        $sell_price = $base_sell_price;
        $base_orders++;
        echo "✅ Order $order_id: Using BASE product prices (Buy: ₹$buy_price, Sell: ₹$sell_price)\n";
    }
    
    // Update the order with prices
    $update_query = "UPDATE orders SET 
                     buy_price_at_order_time = ?, 
                     sell_price_at_order_time = ? 
                     WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, "ddi", $buy_price, $sell_price, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $updated_count++;
    } else {
        echo "❌ Failed to update order $order_id: " . mysqli_error($con) . "\n";
        $error_count++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 UPDATE SUMMARY:\n";
echo "Total orders processed: " . ($updated_count + $error_count) . "\n";
echo "✅ Successfully updated: $updated_count orders\n";
echo "❌ Failed to update: $error_count orders\n";
echo "🔄 Variant orders: $variant_orders\n";
echo "📦 Base product orders: $base_orders\n";
echo str_repeat("=", 50) . "\n";

if ($updated_count > 0) {
    echo "\n🎉 Price columns have been successfully updated!\n";
    echo "Now all orders will show correct historical prices in BCP dashboard.\n";
} else {
    echo "\n⚠️ No orders were updated. Check if price columns already exist.\n";
}

mysqli_close($con);
?>
