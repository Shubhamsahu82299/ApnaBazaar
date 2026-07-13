<?php
/**
 * Variant History Management
 * This file helps maintain variant history and order relationships
 */

/**
 * Check if a variant has existing orders before allowing deletion
 */
function checkVariantOrders($conn, $variant_id) {
    $query = "SELECT COUNT(*) as order_count FROM orders WHERE variant_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    return $data['order_count'] > 0;
}

/**
 * Get variant history for a product
 */
function getVariantHistory($conn, $product_id) {
    $query = "SELECT pv.*, 
              (SELECT COUNT(*) FROM orders WHERE variant_id = pv.id) as order_count,
              (SELECT SUM(quantity) FROM orders WHERE variant_id = pv.id) as total_quantity
              FROM product_variants pv 
              WHERE pv.product_id = ? 
              ORDER BY pv.id";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * Safe variant deletion - only if no orders exist
 */
function safeDeleteVariant($conn, $variant_id) {
    if (checkVariantOrders($conn, $variant_id)) {
        return false; // Cannot delete - has orders
    }
    
    $query = "DELETE FROM product_variants WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Archive variant instead of deleting (for history preservation)
 */
function archiveVariant($conn, $variant_id) {
    // First, copy to archive table if it exists
    $archive_query = "INSERT INTO product_variants_archive 
                      SELECT *, NOW() as archived_date 
                      FROM product_variants 
                      WHERE id = ?";
    $stmt = mysqli_prepare($conn, $archive_query);
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    mysqli_stmt_execute($stmt);
    
    // Then mark as inactive instead of deleting
    $update_query = "UPDATE product_variants SET is_active = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get active variants for a product
 */
function getActiveVariants($conn, $product_id) {
    $query = "SELECT * FROM product_variants 
              WHERE product_id = ? AND (is_active = 1 OR is_active IS NULL)
              ORDER BY id";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * Validate variant update - ensure no breaking changes
 */
function validateVariantUpdate($conn, $variant_id, $new_label, $new_price) {
    // Check if this variant has orders
    if (checkVariantOrders($conn, $variant_id)) {
        // Get current variant data
        $query = "SELECT variant_label, price FROM product_variants WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $variant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $current = mysqli_fetch_assoc($result);
        
        // If label changed and has orders, this might cause issues
        if ($current['variant_label'] !== $new_label) {
            return [
                'valid' => false,
                'message' => "Cannot change variant label '$current[variant_label]' to '$new_label' because it has existing orders. Consider creating a new variant instead."
            ];
        }
    }
    
    return ['valid' => true, 'message' => 'Update allowed'];
}
?>

