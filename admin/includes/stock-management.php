<?php
/**
 * Stock Management Functions
 * This file contains functions to handle stock updates and availability management
 */

/**
 * Update product stock when order is placed
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @param int $quantity Quantity ordered
 * @return bool Success status
 */
function updateProductStock($conn, $productId, $quantity) {
    // Get current stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $currentStock = intval($row['stock']);
        $newStock = max(0, $currentStock - $quantity); // Ensure stock doesn't go below 0
        
        // Update stock
        $updateStmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newStock, $productId);
        $success = $updateStmt->execute();
        $updateStmt->close();
        
        // Auto-update availability considering both product and variant stock
        updateProductAvailabilityFromVariants($conn, $productId);
        
        $stmt->close();
        return $success;
    }
    
    $stmt->close();
    return false;
}

/**
 * Update product availability based on stock
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @param int $stock Current stock level
 * @return bool Success status
 */
function updateProductAvailability($conn, $productId, $stock = null) {
    if ($stock === null) {
        // Get current stock if not provided
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stock = intval($row['stock']);
        } else {
            $stmt->close();
            return false;
        }
        $stmt->close();
    }
    
    // Determine availability based on stock
    $availability = ($stock == 0) ? 'Out of Stock' : 'In Stock';
    
    // Update availability
    $updateStmt = $conn->prepare("UPDATE products SET productAvailability = ? WHERE id = ?");
    $updateStmt->bind_param("si", $availability, $productId);
    $success = $updateStmt->execute();
    $updateStmt->close();
    
    return $success;
}

/**
 * Check if product has sufficient stock
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @param int $quantity Required quantity
 * @return bool True if sufficient stock available
 */
function checkProductStock($conn, $productId, $quantity) {
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND productAvailability = 'In Stock'");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $availableStock = intval($row['stock']);
        $stmt->close();
        return $availableStock >= $quantity;
    }
    
    $stmt->close();
    return false;
}

/**
 * Bulk update all product availability based on stock
 * @param mysqli $conn Database connection
 * @return array Array with counts of updated products
 */
function bulkUpdateProductAvailability($conn) {
    $results = [];
    
    // Update products with stock = 0 to "Out of Stock"
    $stmt = $conn->prepare("UPDATE products SET productAvailability = 'Out of Stock' WHERE stock = 0 AND productAvailability != 'Out of Stock'");
    $stmt->execute();
    $results['zero_stock_updated'] = $stmt->affected_rows;
    $stmt->close();
    
    // Update products with stock > 0 to "In Stock" if they were "Out of Stock"
    $stmt = $conn->prepare("UPDATE products SET productAvailability = 'In Stock' WHERE stock > 0 AND productAvailability = 'Out of Stock'");
    $stmt->execute();
    $results['in_stock_updated'] = $stmt->affected_rows;
    $stmt->close();
    
    return $results;
}

/**
 * Get stock statistics
 * @param mysqli $conn Database connection
 * @return array Array with stock statistics
 */
function getStockStatistics($conn) {
    $stats = [];
    
    // Total products
    $result = $conn->query("SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = $result->fetch_assoc()['total'];
    
    // Products with stock = 0
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0");
    $stats['zero_stock_products'] = $result->fetch_assoc()['count'];
    
    // Products marked as "Out of Stock"
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE productAvailability = 'Out of Stock'");
    $stats['out_of_stock_products'] = $result->fetch_assoc()['count'];
    
    // Products marked as "In Stock"
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE productAvailability = 'In Stock'");
    $stats['in_stock_products'] = $result->fetch_assoc()['count'];
    
    // Products with low stock (5 or less)
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 0 AND stock <= 5");
    $stats['low_stock_products'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

/**
 * Get products that need attention (mismatched stock and availability)
 * @param mysqli $conn Database connection
 * @param int $limit Maximum number of products to return
 * @return array Array of products that need attention
 */
function getProductsNeedingAttention($conn, $limit = 10) {
    $products = [];
    
    $sql = "SELECT id, productName, stock, productAvailability 
            FROM products 
            WHERE (stock = 0 AND productAvailability != 'Out of Stock') 
            OR (stock > 0 AND productAvailability = 'Out of Stock')
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['should_be'] = ($row['stock'] == 0) ? 'Out of Stock' : 'In Stock';
        $products[] = $row;
    }
    
    $stmt->close();
    return $products;
}

/**
 * Update variant stock when order is placed
 * @param mysqli $conn Database connection
 * @param int $variantId Variant ID
 * @param int $quantity Quantity ordered
 * @return bool Success status
 */
function updateVariantStock($conn, $variantId, $quantity) {
    $stmt = $conn->prepare("SELECT stock, product_id FROM product_variants WHERE id = ?");
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $currentStock = intval($row['stock']);
        $productId = intval($row['product_id']);
        $newStock = max(0, $currentStock - $quantity);

        $updateStmt = $conn->prepare("UPDATE product_variants SET stock = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newStock, $variantId);
        $success = $updateStmt->execute();
        $updateStmt->close();
        
        // Update product availability based on total variant stock
        updateProductAvailabilityFromVariants($conn, $productId);
        
        $stmt->close();
        return $success;
    }

    $stmt->close();
    return false;
}

/**
 * Update product availability based on variant stock
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @return bool Success status
 */
function updateProductAvailabilityFromVariants($conn, $productId) {
    // Check total stock across all variants for this product
    $stmt = $conn->prepare("SELECT SUM(stock) as total_variant_stock FROM product_variants WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $totalVariantStock = intval($row['total_variant_stock']);
        
        // Also check product's own stock
        $productStmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $productStmt->bind_param("i", $productId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $productRow = $productResult->fetch_assoc();
        $productStock = intval($productRow['stock']);
        
        // Total available stock = product stock + variant stock
        $totalStock = $productStock + $totalVariantStock;
        
        // Update product availability based on total stock
        $newAvailability = ($totalStock > 0) ? 'In Stock' : 'Out of Stock';
        
        $updateStmt = $conn->prepare("UPDATE products SET productAvailability = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newAvailability, $productId);
        $success = $updateStmt->execute();
        
        $updateStmt->close();
        $productStmt->close();
        $stmt->close();
        
        return $success;
    }
    
    // If no variants found, just update based on product stock
    $productStmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $productStmt->bind_param("i", $productId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    
    if ($productRow = $productResult->fetch_assoc()) {
        $productStock = intval($productRow['stock']);
        $newAvailability = ($productStock > 0) ? 'In Stock' : 'Out of Stock';
        
        $updateStmt = $conn->prepare("UPDATE products SET productAvailability = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newAvailability, $productId);
        $success = $updateStmt->execute();
        
        $updateStmt->close();
        $productStmt->close();
        $stmt->close();
        
        return $success;
    }
    
    $stmt->close();
    return false;
}

/**
 * Bulk update all products availability based on variant stock
 * @param mysqli $conn Database connection
 * @return array Array with update statistics
 */
function bulkUpdateProductAvailabilityFromVariants($conn) {
    $stats = ['updated' => 0, 'errors' => 0];
    
    // Get all products (both with and without variants)
    $sql = "SELECT id, productName FROM products";
    
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $productId = $row['id'];
        if (updateProductAvailabilityFromVariants($conn, $productId)) {
            $stats['updated']++;
        } else {
            $stats['errors']++;
        }
    }
    
    return $stats;
}

/**
 * Check if variant has sufficient stock
 * @param mysqli $conn Database connection
 * @param int $variantId Variant ID
 * @param int $quantity Required quantity
 * @return bool True if sufficient stock available
 */
function checkVariantStock($conn, $variantId, $quantity) {
    $stmt = $conn->prepare("SELECT stock FROM product_variants WHERE id = ?");
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $availableStock = intval($row['stock']);
        $stmt->close();
        return $availableStock >= $quantity;
    }

    $stmt->close();
    return false;
}
?> 