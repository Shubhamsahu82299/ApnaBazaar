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
 * Update variant stock when order is placed (Dependent System)
 * @param mysqli $conn Database connection
 * @param int $variantId Variant ID
 * @param int $quantity Quantity ordered
 * @return bool Success status
 */
function updateDependentVariantStock($conn, $variantId, $quantity) {
    // Get variant details and product info
    $stmt = $conn->prepare("SELECT pv.stock, pv.product_id, pv.variant_label, p.stock as main_stock, p.stock_management_type, p.unit_type FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE pv.id = ?");
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $productId = intval($row['product_id']);
        $managementType = $row['stock_management_type'];
        
        // Debug logging
        error_log("DEBUG: updateDependentVariantStock - Product ID: $productId, Management Type: $managementType");
        error_log("DEBUG: Variant Label: " . $row['variant_label'] . ", Quantity: $quantity");
        error_log("DEBUG: Current Main Stock: " . $row['main_stock']);
        
        // If dependent system, update main stock and recalculate all variants
        if ($managementType === 'dependent') {
            // Calculate how much main stock this order consumes
            $variantLabel = $row['variant_label'];
            $mainStockConsumed = calculateMainStockFromVariant($variantLabel, $quantity);
            
            // Update main product stock (support float values)
            $currentMainStock = floatval($row['main_stock']);
            $newMainStock = max(0, $currentMainStock - $mainStockConsumed);
            
            error_log("DEBUG: Main Stock Consumed: $mainStockConsumed");
            error_log("DEBUG: New Main Stock: $newMainStock");
            
            // Check if order is valid (not overselling)
            if ($mainStockConsumed > $currentMainStock) {
                error_log("DEBUG: Order rejected - insufficient stock");
                $stmt->close();
                return false; // Order rejected
            }
            
            // Update main stock (store as float)
            $updateMainStmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $updateMainStmt->bind_param("di", $newMainStock, $productId);
            $updateMainStmt->execute();
            $updateMainStmt->close();
            
            // Auto-recalculate all variants based on new main stock
            recalculateDependentVariants($conn, $productId, $newMainStock);
            
        } else {
            // Independent system - just update this variant
            $currentStock = intval($row['stock']);
            $newStock = max(0, $currentStock - $quantity);

            $updateStmt = $conn->prepare("UPDATE product_variants SET stock = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newStock, $variantId);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        // Update product availability
        updateProductAvailabilityFromVariants($conn, $productId);
        
        $stmt->close();
        return true;
    }

    $stmt->close();
    return false;
}

/**
 * Calculate main stock consumed from variant order
 * @param string $variantLabel Variant label (e.g., "1 Kg", "250 gm", "5 pcs", "1 pc")
 * @param int $quantity Quantity ordered
 * @return float Main stock consumed
 */
function calculateMainStockFromVariant($variantLabel, $quantity) {
    // Extract size from variant label
    $size = 0;
    $unit = '';
    
    if (preg_match('/(\d+(?:\.\d+)?)\s*(kg|Kg|gm|ltr|ml|pc|pcs)/i', $variantLabel, $matches)) {
        $size = floatval($matches[1]);
        $unit = strtolower($matches[2]);
        
        // Normalize pc/pcs to pcs for calculation
        if ($unit === 'pc') {
            $unit = 'pcs';
        }
        
        // Convert gm to kg for main stock calculation
        if ($unit === 'gm') {
            $size = $size / 1000; // Convert gm to kg
            $unit = 'kg';
        }
        
        error_log("DEBUG: calculateMainStockFromVariant - Label: $variantLabel, Size: $size, Unit: $unit, Quantity: $quantity");
    } else {
        error_log("DEBUG: calculateMainStockFromVariant - No match for label: $variantLabel");
    }
    
    $consumed = $size * $quantity;
    error_log("DEBUG: calculateMainStockFromVariant - Consumed: $consumed $unit");
    
    return $consumed;
}

/**
 * Recalculate all variants based on main stock (Dependent System)
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @param float $mainStock Current main stock
 * @return bool Success status
 */
function recalculateDependentVariants($conn, $productId, $mainStock) {
    error_log("DEBUG: recalculateDependentVariants - Product ID: $productId, Main Stock: $mainStock");
    
    // Get product unit type
    $stmt = $conn->prepare("SELECT unit_type FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        error_log("DEBUG: No product found for ID: $productId");
        return false;
    }
    
    $unitType = $product['unit_type'];
    error_log("DEBUG: Unit Type: $unitType");
    
    // Get all variants for this product
    $variantStmt = $conn->prepare("SELECT id, variant_label FROM product_variants WHERE product_id = ? ORDER BY id");
    $variantStmt->bind_param("i", $productId);
    $variantStmt->execute();
    $variants = $variantStmt->get_result();
    $variantStmt->close();
    
    $variantIndex = 0;
    while ($variant = $variants->fetch_assoc()) {
        $variantLabel = $variant['variant_label'];
        $variantId = $variant['id'];
        
        // Calculate new stock for this variant
        $newStock = 0;
        if (preg_match('/(\d+(?:\.\d+)?)\s*(kg|Kg|gm|ltr|ml|pc|pcs)/i', $variantLabel, $matches)) {
            $size = floatval($matches[1]);
            $unit = strtolower($matches[2]);
            
            // Normalize pc/pcs to pcs for calculation
            if ($unit === 'pc') {
                $unit = 'pcs';
            }
            
            // Calculate based on unit type - exact calculation
            if ($unitType === 'pcs') {
                // For pieces, calculate how many pieces can be made from main stock
                $newStock = floor($mainStock / $size);
            } elseif ($unitType === 'kg') {
                if ($unit === 'gm') {
                    // Convert gm to kg for calculation
                    $sizeInKg = $size / 1000;
                    $newStock = floor($mainStock / $sizeInKg);
                } else {
                    // kg unit
                    $newStock = floor($mainStock / $size);
                }
            } elseif ($unitType === 'gm') {
                if ($unit === 'kg') {
                    // Convert kg to gm for calculation
                    $sizeInGm = $size * 1000;
                    $newStock = floor(($mainStock * 1000) / $sizeInGm);
                } else {
                    // gm unit
                    $newStock = floor($mainStock / $size);
                }
            } elseif ($unitType === 'ltr') {
                if ($unit === 'ml') {
                    // Convert ml to ltr for calculation
                    $sizeInLtr = $size / 1000;
                    $newStock = floor($mainStock / $sizeInLtr);
                } else {
                    // ltr unit
                    $newStock = floor($mainStock / $size);
                }
            } elseif ($unitType === 'ml') {
                if ($unit === 'ltr') {
                    // Convert ltr to ml for calculation
                    $sizeInMl = $size * 1000;
                    $newStock = floor(($mainStock * 1000) / $sizeInMl);
                } else {
                    // ml unit
                    $newStock = floor($mainStock / $size);
                }
            }
        }
        
        error_log("DEBUG: Variant $variantLabel - Size: $size, Unit: $unit, New Stock: $newStock");
        
        // Update variant stock (store as integer for display)
        $updateStmt = $conn->prepare("UPDATE product_variants SET stock = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newStock, $variantId);
        $updateStmt->execute();
        $updateStmt->close();
        
        $variantIndex++;
    }
    
    error_log("DEBUG: Recalculated $variantIndex variants");
    return true;
}

/**
 * Update variant stock when order is placed
 * @param mysqli $conn Database connection
 * @param int $variantId Variant ID
 * @param int $quantity Quantity ordered
 * @return bool Success status
 */
function updateVariantStock($conn, $variantId, $quantity) {
    // Check if product uses dependent system
    $stmt = $conn->prepare("SELECT p.stock_management_type FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE pv.id = ?");
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $managementType = $row['stock_management_type'];
        $stmt->close();
        
        if ($managementType === 'dependent') {
            return updateDependentVariantStock($conn, $variantId, $quantity);
        } else {
            // Original independent logic
            return updateIndependentVariantStock($conn, $variantId, $quantity);
        }
    }
    
    $stmt->close();
    return false;
}

/**
 * Update variant stock when order is placed (Independent System)
 * @param mysqli $conn Database connection
 * @param int $variantId Variant ID
 * @param int $quantity Quantity ordered
 * @return bool Success status
 */
function updateIndependentVariantStock($conn, $variantId, $quantity) {
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
    // First check if product uses dependent system
    $productStmt = $conn->prepare("SELECT stock, stock_management_type FROM products WHERE id = ?");
    $productStmt->bind_param("i", $productId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    $productRow = $productResult->fetch_assoc();
    $productStmt->close();
    
    if (!$productRow) {
        return false;
    }
    
    $managementType = $productRow['stock_management_type'];
    $mainStock = floatval($productRow['stock']); // Support float values
    
    // For dependent system, check main stock
    if ($managementType === 'dependent') {
        $newAvailability = ($mainStock > 0) ? 'In Stock' : 'Out of Stock';
        
        $updateStmt = $conn->prepare("UPDATE products SET productAvailability = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newAvailability, $productId);
        $success = $updateStmt->execute();
        $updateStmt->close();
        
        error_log("DEBUG: Dependent system - Main stock: $mainStock, Availability: $newAvailability");
        return $success;
    }
    
    // For independent system, check total variant stock
    $stmt = $conn->prepare("SELECT SUM(stock) as total_variant_stock FROM product_variants WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $totalVariantStock = intval($row['total_variant_stock']);
        
        // Total available stock = product stock + variant stock
        $totalStock = $mainStock + $totalVariantStock;
        
        // Update product availability based on total stock
        $newAvailability = ($totalStock > 0) ? 'In Stock' : 'Out of Stock';
        
        $updateStmt = $conn->prepare("UPDATE products SET productAvailability = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newAvailability, $productId);
        $success = $updateStmt->execute();
        
        $updateStmt->close();
        $stmt->close();
        
        return $success;
    }
    
    // If no variants found, just update based on product stock
    $newAvailability = ($mainStock > 0) ? 'In Stock' : 'Out of Stock';
    
    $updateStmt = $conn->prepare("UPDATE products SET productAvailability = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newAvailability, $productId);
    $success = $updateStmt->execute();
    
    $updateStmt->close();
    $stmt->close();
    
    return $success;
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
/**
 * Process gm/kg variant order and update main stock and dependent variants
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @param string $variantLabel Variant label (e.g., "100 gm", "250 gm", "1 Kg")
 * @param int $quantity Quantity ordered
 * @return bool Success status
 */
function processGmVariantOrder($conn, $productId, $variantLabel, $quantity) {
    // Get current main stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $currentStock = floatval($row['stock']);
        // Convert variantLabel to kg
        $size = 0;
        $unit = '';
        if (preg_match('/(\d+(?:\.\d+)?)\s*(kg|Kg|gm)/i', $variantLabel, $matches)) {
            $size = floatval($matches[1]);
            $unit = strtolower($matches[2]);
            if ($unit === 'gm') {
                $size = $size / 1000; // Convert gm to kg
            }
        } else {
            $stmt->close();
            return false; // Invalid label
        }
        $totalConsumed = $size * $quantity;
        if ($totalConsumed > $currentStock) {
            $stmt->close();
            return false; // Not enough stock
        }
        $newStock = max(0, $currentStock - $totalConsumed);
        // Update main stock
        $updateStmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $updateStmt->bind_param("di", $newStock, $productId);
        $updateStmt->execute();
        $updateStmt->close();
        // Recalculate all gm/kg variants (ignore unit_type)
        recalculateGmVariants($conn, $productId, $newStock);
        // Update product availability
        updateProductAvailabilityFromVariants($conn, $productId);
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

/**
 * Recalculate all gm/kg variants for a product, ignoring unit_type
 * @param mysqli $conn Database connection
 * @param int $productId Product ID
 * @param float $mainStock Current main stock in kg
 * @return bool Success status
 */
function recalculateGmVariants($conn, $productId, $mainStock) {
    // Get all variants for this product
    $variantStmt = $conn->prepare("SELECT id, variant_label FROM product_variants WHERE product_id = ? ORDER BY id");
    $variantStmt->bind_param("i", $productId);
    $variantStmt->execute();
    $variants = $variantStmt->get_result();
    $variantStmt->close();
    $variantIndex = 0;
    while ($variant = $variants->fetch_assoc()) {
        $variantLabel = $variant['variant_label'];
        $variantId = $variant['id'];
        $size = 0;
        $unit = '';
        if (preg_match('/(\d+(?:\.\d+)?)\s*(kg|Kg|gm)/i', $variantLabel, $matches)) {
            $size = floatval($matches[1]);
            $unit = strtolower($matches[2]);
            if ($unit === 'gm') {
                $size = $size / 1000; // Convert gm to kg
            }
        } else {
            error_log("recalculateGmVariants: Invalid label for variant $variantId: $variantLabel");
            continue; // Skip invalid label
        }
        error_log("recalculateGmVariants: mainStock=$mainStock, size=$size, variantId=$variantId, variantLabel=$variantLabel");
        $newStock = ($size > 0) ? floor($mainStock / $size) : 0;
        error_log("recalculateGmVariants: Calculated newStock=$newStock for variantId=$variantId");
        // Update variant stock
        $updateStmt = $conn->prepare("UPDATE product_variants SET stock = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newStock, $variantId);
        $updateStmt->execute();
        $updateStmt->close();
        $variantIndex++;
    }
    return true;
}

?> 