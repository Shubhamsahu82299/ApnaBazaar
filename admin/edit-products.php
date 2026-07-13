<?php
include_once('includes/config.php');
include('includes/main-header.php');
include('includes/variant-history.php');

// SQL Query to add stock_management_type column (run this once):
// ALTER TABLE products ADD COLUMN stock_management_type ENUM('independent', 'dependent') DEFAULT 'independent';

$pid = intval($_GET['id']);
$msg = '';

// On form submit
if (isset($_POST['submit'])) {
    $category = $_POST['category'];
    $subcat = $_POST['subcategory'];
    $productname = $_POST['productName'];
    $productcompany = $_POST['productCompany'];
    $productprice = $_POST['productprice'];
    $productpricebd = $_POST['productpricebd'];
    $productdescription = $_POST['productDescription'];
    $productscharge = $_POST['productShippingcharge'];
  $productavailability = $_POST['productAvailability'];
$unit_type = $_POST['unit_type'];
$stock = intval($_POST['stock']); // ✅ ADDED // ✅ ADDED
$stock_management_type = $_POST['stock_management_type']; // ✅ ADDED
 $base_buy_price = isset($_POST['base_buy_price']) ? floatval($_POST['base_buy_price']) : 0.00; // ✅ NEW

    mysqli_query($conn, "UPDATE products SET 
        category='$category',
        subCategory='$subcat',
        productName='$productname',
        productCompany='$productcompany',
        productPrice='$productprice',
        productDescription='$productdescription',
        shippingCharge='$productscharge',
        productPriceBeforeDiscount='$productpricebd',
        unit_type='$unit_type',
        stock='$stock',
        stock_management_type='$stock_management_type',
        base_buy_price='$base_buy_price' -- ✅ NEW
        WHERE id='$pid'
    ");
    // Smart variant update - preserve existing variants and update them
    if (isset($_POST['variant']) && is_array($_POST['variant'])) {
        // Get existing variants to preserve their IDs
        $existing_variants = [];
     $existing_query = mysqli_query($conn, "SELECT id, variant_label 
    FROM product_variants 
    WHERE product_id='$pid' AND slot='1'");

        while ($existing = mysqli_fetch_assoc($existing_query)) {
            $existing_variants[$existing['variant_label']] = $existing['id'];
        }
        
        // Track which variants we've processed
        $processed_variants = [];
        
        foreach ($_POST['variant'] as $v) {
            $label = mysqli_real_escape_string($conn, $v['name']);
            $price = floatval($v['price']);
            $buy_price = isset($v['buy_price']) ? floatval($v['buy_price']) : 0.00;
            $vstock = isset($v['stock']) ? intval($v['stock']) : 0;
            
            if (!empty($label) && $price > 0) {
                // Check if this variant already exists
                if (isset($existing_variants[$label])) {
                    // Update existing variant (preserve ID)
                    $variant_id = $existing_variants[$label];
                    
                    // Validate the update
                    $validation = validateVariantUpdate($conn, $variant_id, $label, $price);
                    if (!$validation['valid']) {
                        $msg = "❌ " . $validation['message'];
                        continue;
                    }
                    
                    mysqli_query($conn, "UPDATE product_variants SET 
                         price='$price', 
    variant_buy_price='$buy_price', 
    stock='$vstock' 
    WHERE id='$variant_id' AND slot='1'");
                    $processed_variants[] = $variant_id;
                } else {
                    // Insert new variant
                    mysqli_query($conn, "INSERT INTO product_variants (product_id, variant_label, price, variant_buy_price, stock, slot) 
    VALUES ('$pid', '$label', '$price', '$buy_price', '$vstock', '1')");
                }
            }
        }
        
        // Delete variants that were not in the form (user removed them)
      if (!empty($processed_variants)) {
    $processed_ids = implode(',', $processed_variants);
    mysqli_query($conn, "DELETE FROM product_variants 
        WHERE product_id='$pid' AND slot='1' 
        AND id NOT IN ($processed_ids)");
} else {
    mysqli_query($conn, "DELETE FROM product_variants 
        WHERE product_id='$pid' AND slot='1'");
}

    } else {
        // No variants in form, delete all variants for this product
       mysqli_query($conn, "DELETE FROM product_variants WHERE product_id='$pid' AND slot='1'");

    }
    
    // Update product availability based on total stock (product + variants)
    include('./includes/stock-management.php');
    updateProductAvailabilityFromVariants($conn, $pid);

    $msg = "✅ Product updated successfully!";
}

// Fetch data for form
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = '$pid'"));
$variants = mysqli_query($conn, "SELECT * FROM product_variants WHERE product_id='$pid' AND slot='1'");

$variantData = [];
while ($v = mysqli_fetch_assoc($variants)) {
    $variantData[] = $v;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <style>
    body { background: #f8f9fa; }
    .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    label { font-weight: 600; }
    .product-image { max-width: 100px; margin-bottom: 10px; border: 1px solid #ddd; padding: 3px; border-radius: 4px; }
  </style>
  <script>
  function getSubcat(val) {
    $.ajax({
      type: "POST",
      url: "get_subcat.php",
      data: { cat_id: val },
      success: function(data) {
        $("#subcategory").html(data);
      }
    });
  }
  </script>
</head>
<body>
  
    <div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">Edit Product</h3>
  <a href="manage-products.php" class="btn btn-success">Go back to manage product</a>
</div>
<div class="container form-container">
  <h3 class="mb-4">Edit Product</h3>
  <?php if ($msg): ?>
    <div class="alert alert-info"><?php echo $msg; ?></div>
  <?php endif; ?>
  <form method="post">
    <!-- Debug Info -->
    <div class="alert alert-info">
      <strong>Debug Info:</strong><br>
      Product ID: <?= $pid ?><br>
      Stock Management Type: <?= $product['stock_management_type'] ?? 'NULL' ?><br>
      POST Data: <?= isset($_POST['stock_management_type']) ? $_POST['stock_management_type'] : 'None' ?>
    </div>
    
    <div class="mb-3">
      <label>Category</label>
      <select name="category" class="form-select" onchange="getSubcat(this.value)" required>
        <option value="">-- Select Category --</option>
        <?php
        $cat_res = mysqli_query($conn, "SELECT * FROM category");
        while ($cat = mysqli_fetch_assoc($cat_res)) {
            $selected = ($cat['id'] == $product['category']) ? 'selected' : '';
            echo "<option value='{$cat['id']}' $selected>{$cat['categoryName']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Subcategory</label>
      <select name="subcategory" id="subcategory" class="form-select" required>
        <?php
        $sub_res = mysqli_query($conn, "SELECT * FROM subcategory WHERE categoryid = '{$product['category']}'");
        while ($sub = mysqli_fetch_assoc($sub_res)) {
            $selected = ($sub['id'] == $product['subCategory']) ? 'selected' : '';
            echo "<option value='{$sub['id']}' $selected>{$sub['subcategory']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3"><label>Product Name</label>
      <input type="text" name="productName" class="form-control" value="<?= $product['productName'] ?>" ></div>

    <div class="mb-3"><label>Product Company</label>
      <input type="text" name="productCompany" class="form-control" value="<?= $product['productCompany'] ?>" ></div>
    <div class="mb-3"><label>Base Buy Price</label>
      <input type="number" step="0.01" name="base_buy_price" class="form-control" value="<?= isset($product['base_buy_price']) ? $product['base_buy_price'] : '0.00' ?>" required>
    </div>
    <div class="mb-3"><label>Price Before Discount</label>
      <input type="number" name="productpricebd" class="form-control" value="<?= $product['productPriceBeforeDiscount'] ?>" ></div>

    <div class="mb-3"><label>Selling Price</label>
      <input type="number" name="productprice" class="form-control" value="<?= $product['productPrice'] ?>" ></div>

    <div class="mb-3"><label>Description</label>
      <textarea name="productDescription" class="form-control" rows="3"><?= $product['productDescription'] ?></textarea></div>

    <div class="mb-3"><label>Shipping Charge</label>
      <input type="number" name="productShippingcharge" class="form-control" value="<?= $product['shippingCharge'] ?>"></div>

    <div class="mb-3"><label>Availability</label>
      <select name="productAvailability" class="form-select" required>
        <option value="In Stock" <?= $product['productAvailability'] == "In Stock" ? 'selected' : '' ?>>In Stock</option>
        <option value="Out of Stock" <?= $product['productAvailability'] == "Out of Stock" ? 'selected' : '' ?>>Out of Stock</option>
      </select>
    </div>

    <div class="mb-3">
      <label>Unit Type</label>
      <select name="unit_type" id="unit_type" class="form-select" >
        <option value="">-- Select --</option>
        <?php
        $types = ['pcs','kg','gm','ltr','ml','size','color'];
        foreach ($types as $type) {
          $sel = ($product['unit_type'] == $type) ? 'selected' : '';
          echo "<option value='$type' $sel>" . ucfirst($type) . "</option>";
        }
        ?>
      </select>
    </div>
    <div class="mb-3">
       <label>Stock</label>
       <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required min="0">
    </div>
    
    <div class="mb-3">
      <label>Stock Management Type</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="stock_management_type" id="independent" value="independent" <?= ($product['stock_management_type'] ?? 'independent') == 'independent' ? 'checked' : '' ?>>
        <label class="form-check-label" for="independent">
          <strong>Independent Stock</strong> - Each variant has separate stock. Ordering one variant doesn't affect others.
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="stock_management_type" id="dependent" value="dependent" <?= ($product['stock_management_type'] ?? 'independent') == 'dependent' ? 'checked' : '' ?>>
        <label class="form-check-label" for="dependent">
          <strong>Dependent Stock</strong> - All variants share the main product stock. Ordering any variant reduces stock for all variants.
        </label>
      </div>
    </div>
    
    <div class="mb-3" id="unit-options"></div>

    <div class="row mb-4">
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="col-md-4 text-center">
          <label class="form-label">Image <?= $i ?></label><br>
          <?php if ($product["productImage$i"]): ?>
            <img src="productimages/<?= $pid ?>/<?= $product["productImage$i"] ?>" class="product-image"><br>
          <?php else: ?>
            <span class="text-muted">No image</span><br>
          <?php endif; ?>
          <a href="update-image<?= $i ?>.php?id=<?= $pid ?>" class="btn btn-sm btn-outline-primary mt-2">Change Image</a>
        </div>
      <?php endfor; ?>
    </div>

    <button type="submit" name="submit" class="btn btn-primary">Update Product</button>
  </form>
  
  <!-- Variant History Section -->
  <div class="container form-container mt-4">
      <h4>📋 Variant History & Order Information</h4>
      <?php
      $variant_history = getVariantHistory($conn, $pid);
      if ($variant_history && mysqli_num_rows($variant_history) > 0):
      ?>
      <div class="table-responsive">
          <table class="table table-bordered table-striped">
              <thead class="table-dark">
                  <tr>
                      <th>Variant ID</th>
                      <th>Label</th>
                      <th>Current Price</th>
                      <th>Buy Price</th>
                      <th>Stock</th>
                      <th>Orders</th>
                      <th>Total Qty Sold</th>
                      <th>Status</th>
                  </tr>
              </thead>
              <tbody>
                  <?php while ($variant = mysqli_fetch_assoc($variant_history)): ?>
                  <tr class="<?php echo $variant['order_count'] > 0 ? 'table-warning' : ''; ?>">
                      <td><strong><?php echo $variant['id']; ?></strong></td>
                      <td><?php echo htmlspecialchars($variant['variant_label']); ?></td>
                      <td>₹<?php echo $variant['price']; ?></td>
                      <td>₹<?php echo $variant['variant_buy_price']; ?></td>
                      <td><?php echo $variant['stock']; ?></td>
                      <td>
                          <?php if ($variant['order_count'] > 0): ?>
                              <span class="badge bg-warning text-dark">
                                  <?php echo $variant['order_count']; ?> orders
                              </span>
                          <?php else: ?>
                              <span class="badge bg-secondary">No orders</span>
                          <?php endif; ?>
                      </td>
                      <td><?php echo $variant['total_quantity'] ?? 0; ?></td>
                      <td>
                          <?php if ($variant['order_count'] > 0): ?>
                              <span class="text-warning">⚠️ Has Orders</span>
                          <?php else: ?>
                              <span class="text-success">✅ Safe to modify</span>
                          <?php endif; ?>
                      </td>
                  </tr>
                  <?php endwhile; ?>
              </tbody>
          </table>
      </div>
      <div class="alert alert-info">
          <strong>💡 Note:</strong> Variants with existing orders cannot be deleted or have their labels changed. 
          You can only modify prices and stock for these variants.
      </div>
      <?php else: ?>
      <div class="alert alert-secondary">
          No variants found for this product.
      </div>
      <?php endif; ?>
  </div>
</div>

<script>
const unitOptions = document.getElementById('unit-options');
const unitTypeSelect = document.getElementById('unit_type');
const stockManagementType = document.querySelector('input[name="stock_management_type"]:checked');
const variantsFromPHP = <?= json_encode($variantData); ?>;

// Auto stock calculation function
function calculateVariantStock(mainStock, unitType, managementType) {
    const predefined = {
        pcs: [1, 2, 3, 4, 5],
        kg: [1, 2, 5, 10],
        gm: [100, 250, 500, 1000],
        ltr: [1, 2, 5],
        ml: [100, 250, 500, 1000]
    };
    
    if (!predefined[unitType]) return [];
    
    if (managementType === 'dependent') {
        // Dependent system: Calculate based on main stock
        return predefined[unitType].map(unit => {
            if (unitType === 'gm' || unitType === 'ml') {
                return Math.floor((mainStock * 1000) / unit); // Convert kg to gm
            }
            return Math.floor(mainStock / unit);
        });
    } else {
        // Independent system: Return empty for manual entry
        return predefined[unitType].map(() => '');
    }
}

// Auto fill variant stock inputs
function autoFillVariantStock() {
    const mainStock = parseInt(document.querySelector('input[name="stock"]').value) || 0;
    const unitType = unitTypeSelect.value;
    const managementType = document.querySelector('input[name="stock_management_type"]:checked')?.value || 'independent';
    
    if (mainStock > 0 && unitType && unitType !== 'color') {
        const stockValues = calculateVariantStock(mainStock, unitType, managementType);
        const stockInputs = document.querySelectorAll('input[name*="[stock]"]');
        
        stockInputs.forEach((input, index) => {
            if (stockValues[index] !== undefined) {
                input.value = stockValues[index];
                if (managementType === 'dependent') {
                    input.style.backgroundColor = '#e8f5e8'; // Light green background
                    input.readOnly = true; // Make readonly for dependent system
                    setTimeout(() => {
                        input.style.backgroundColor = '';
                    }, 2000);
                } else {
                    input.readOnly = false; // Allow manual entry for independent system
                }
            }
        });
        
        // Show success message
        const message = managementType === 'dependent' 
            ? '✅ Dependent stock calculated! (All variants share main stock)'
            : '✅ Independent stock fields ready! (Enter stock manually for each variant)';
        showMessage(message, 'success');
    }
}

// Show message function
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.form-container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

function loadVariants(type) {
    let html = '';
    const predefined = {
        pcs: ['1 pc','2 pcs','3 pcs','4 pcs','5 pcs'],
        kg: ['1 Kg','2 Kg','5 Kg','10 Kg'],
        gm: ['100 gm','250 gm','500 gm','1 Kg'],
        ltr: ['1 Ltr','2 Ltr','5 Ltr'],
        ml: ['100 ml','250 ml','500 ml','1 Ltr'],
        size: ['XS','S','M','L','XL']
    };

    if (type === 'color') {
        for (let i = 0; i < 5; i++) {
            const name = variantsFromPHP[i]?.variant_label || '';
            const price = variantsFromPHP[i]?.price || '';
            const buy_price = variantsFromPHP[i]?.variant_buy_price || ''; // ✅ NEW
            const stock = variantsFromPHP[i]?.stock || '';
            html += `<div class="d-flex mb-2">
                        <input type="text" name="variant[${i}][name]" value="${name}" placeholder="Color" class="form-control me-2" />
                        <input type="number" name="variant[${i}][price]" value="${price}" placeholder="Price" class="form-control me-2" />
                        <input type="number" name="variant[${i}][buy_price]" value="${buy_price}" placeholder="Buy Price" class="form-control me-2" step="0.01" />
                        <input type="number" name="variant[${i}][stock]" value="${stock}" placeholder="Stock" class="form-control" min="0" />
                    </div>`;
        }
    } else if (predefined[type]) {
        predefined[type].forEach((label, i) => {
            const found = variantsFromPHP.find(v => v.variant_label === label);
            const price = found ? found.price : '';
            const buy_price = found ? found.variant_buy_price : ''; // ✅ NEW
            const stock = found ? found.stock : '';
            const isReadOnly = document.querySelector('input[name="stock_management_type"]:checked')?.value === 'dependent';
            html += `<div class="d-flex mb-2">
                        <input type="text" name="variant[${i}][name]" value="${label}" readonly class="form-control me-2" />
                        <input type="number" name="variant[${i}][price]" value="${price}" placeholder="Price" class="form-control me-2" />
                        <input type="number" name="variant[${i}][buy_price]" value="${buy_price}" placeholder="Buy Price" class="form-control me-2" step="0.01" />
                        <input type="number" name="variant[${i}][stock]" value="${stock}" placeholder="Stock" class="form-control" min="0" ${isReadOnly ? 'readonly' : ''} />
                    </div>`;
        });
    }

    unitOptions.innerHTML = html;
}

unitTypeSelect.addEventListener('change', function() {
    loadVariants(this.value);
});

// Add event listener for stock management type change
document.querySelectorAll('input[name="stock_management_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (unitTypeSelect.value && unitTypeSelect.value !== 'color') {
            loadVariants(unitTypeSelect.value);
            if (this.value === 'dependent') {
                setTimeout(() => autoFillVariantStock(), 100);
            }
        }
    });
});

// Add event listener for main stock input
document.addEventListener('DOMContentLoaded', function() {
    const mainStockInput = document.querySelector('input[name="stock"]');
    if (mainStockInput) {
        // Remove auto calculation on input - only manual button click
        // mainStockInput.addEventListener('input', function() {
        //     if (unitTypeSelect.value && unitTypeSelect.value !== 'color') {
        //         setTimeout(() => autoFillVariantStock(), 500);
        //     }
        // });
    }
    
    if (unitTypeSelect.value) {
        loadVariants(unitTypeSelect.value);
    }
});

// Add auto-calculate button
document.addEventListener('DOMContentLoaded', function() {
    const unitOptionsDiv = document.getElementById('unit-options');
    if (unitOptionsDiv) {
        const autoCalcBtn = document.createElement('button');
        autoCalcBtn.type = 'button';
        autoCalcBtn.className = 'btn btn-success btn-sm mb-3';
        autoCalcBtn.innerHTML = '🔄 Auto Calculate Stock';
        autoCalcBtn.onclick = autoFillVariantStock;
        
        unitOptionsDiv.parentNode.insertBefore(autoCalcBtn, unitOptionsDiv);
    }
});
</script>
</body>
</html>
