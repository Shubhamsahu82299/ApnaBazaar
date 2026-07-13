<?php
session_start();
include('includes/config.php');

header('Content-Type: application/json');

// Validate input
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;

// Validate product ID
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    switch ($action) {
        case 'add':
            if (isset($_SESSION['cart'][$id])) {
                // Check stock before incrementing
                $current_qty = $_SESSION['cart'][$id]['quantity'];
                $new_qty = $current_qty + 1;
                $variant_id = isset($_SESSION['cart'][$id]['variant_id']) ? $_SESSION['cart'][$id]['variant_id'] : null;
                
                if ($variant_id) {
                    // Check variant stock
                    if (!checkVariantStock($con, $variant_id, $new_qty)) {
                        echo json_encode(['error' => 'Cannot add more items - insufficient stock']);
                        exit;
                    }
                } else {
                    // Check product stock
                    if (!checkProductStock($con, $id, $new_qty)) {
                        echo json_encode(['error' => 'Cannot add more items - insufficient stock']);
                        exit;
                    }
                }
                
                $_SESSION['cart'][$id]['quantity'] = $new_qty;
            } else {
                // If variant_id is provided, use variant price/label
                if ($variant_id) {
                    $stmt = $con->prepare("SELECT id, price, variant_label FROM product_variants WHERE id = ? AND product_id = ? LIMIT 1");
                    $stmt->bind_param("ii", $variant_id, $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($v = $result->fetch_assoc()) {
                        $_SESSION['cart'][$id] = [
                            'quantity' => 1,
                            'variant_id' => $variant_id,
                            'variant_label' => $v['variant_label'],
                            'price' => $v['price'],
                            'name' => '', // can fetch product name if needed
                        ];
                    } else {
                        echo json_encode(['error' => 'Variant not found or invalid']);
                        exit;
                    }
                    $stmt->close();
                } else {
                    // Include stock management functions
                    include('includes/stock-management.php');
                    
                    // Check if product has variants with stock
                    $variant_stmt = $con->prepare("SELECT id, price, variant_label, stock FROM product_variants WHERE product_id = ? AND stock > 0 ORDER BY stock DESC LIMIT 1");
                    $variant_stmt->bind_param("i", $id);
                    $variant_stmt->execute();
                    $variant_result = $variant_stmt->get_result();
                    
                    if ($variant_result->num_rows > 0) {
                        // Product has variants with stock, use variant with highest stock
                        $variant_row = $variant_result->fetch_assoc();
                        
                        // Double-check variant stock before adding to cart
                        if (checkVariantStock($con, $variant_row['id'], 1)) {
                            $_SESSION['cart'][$id] = [
                                'quantity' => 1, 
                                'price' => $variant_row['price'],
                                'name' => $variant_row['variant_label'],
                                'variant_id' => $variant_row['id']
                            ];
                        } else {
                            echo json_encode(['error' => 'Selected variant is out of stock']);
                            exit;
                        }
                    } else {
                        // Check if product has sufficient stock
                        if (!checkProductStock($con, $id, 1)) {
                            echo json_encode(['error' => 'Product is out of stock']);
                            exit;
                        }
                        
                        // Use prepared statement to prevent SQL injection
                        $stmt = $con->prepare("SELECT id, productPrice, productName FROM products WHERE id = ? AND productAvailability = 'In Stock' LIMIT 1");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $_SESSION['cart'][$id] = [
                                'quantity' => 1, 
                                'price' => $row['productPrice'],
                                'name' => $row['productName']
                            ];
                        } else {
                            echo json_encode(['error' => 'Product not found or out of stock']);
                            exit;
                        }
                        $stmt->close();
                    }
                    $variant_stmt->close();
                }
            }
            break;

        case 'update':
            if (isset($_SESSION['cart'][$id])) {
                if ($qty > 0) {
                    // Check stock before updating quantity
                    $variant_id = isset($_SESSION['cart'][$id]['variant_id']) ? $_SESSION['cart'][$id]['variant_id'] : null;
                    
                    if ($variant_id) {
                        // Check variant stock
                        if (!checkVariantStock($con, $variant_id, $qty)) {
                            echo json_encode(['error' => 'Insufficient stock for requested quantity']);
                            exit;
                        }
                    } else {
                        // Check product stock
                        if (!checkProductStock($con, $id, $qty)) {
                            echo json_encode(['error' => 'Insufficient stock for requested quantity']);
                            exit;
                        }
                    }
                    
                    $_SESSION['cart'][$id]['quantity'] = $qty;
                } else {
                    unset($_SESSION['cart'][$id]);
                }
            }
            break;

        case 'remove':
            unset($_SESSION['cart'][$id]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }

    // Count total items and total price in cart
    $totalItems = 0;
    $totalPrice = 0;
    foreach ($_SESSION['cart'] as $id => $item) {
        $totalItems += $item['quantity'];
        $totalPrice += $item['quantity'] * $item['price'];
    }

    $itemQty = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id]['quantity'] : 0;

    echo json_encode([
        'success' => true,
        'itemQty' => $itemQty,
        'cartCount' => $totalItems,
        'cartTotal' => $totalPrice,
        'message' => 'Item Added To Cart'
    ]);

} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    echo json_encode([
        'error' => 'An error occurred while updating cart',
        'cartCount' => 0
    ]);
}
?>
