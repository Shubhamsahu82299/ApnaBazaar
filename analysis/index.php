<?php
include 'db.php';

// search handle
$search = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : "";
$where = $search ? "WHERE products.productName LIKE '%$search%' OR category.categoryName LIKE '%$search%' OR subcategory.subcategory LIKE '%$search%'" : "";

// query
$query = mysqli_query($con, "
    SELECT products.*, category.categoryName, subcategory.subcategory AS subcat 
    FROM products 
    JOIN category ON category.id = products.category 
    JOIN subcategory ON subcategory.id = products.subCategory
    $where
    ORDER BY products.id DESC
");
$cnt = 1;
?>
<!DOCTYPE html>
<html>
<head>
<title>Product List</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

<h2 class="mb-4">📋 Product List</h2>

<!-- search bar -->
<form method="get" class="row mb-3">
  <div class="col-md-4">
    <input type="text" name="q" class="form-control" placeholder="Search product, category, subcategory" value="<?= htmlspecialchars($search) ?>">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary">Search</button>
    <a href="product_list.php" class="btn btn-secondary">Reset</a>
  </div>
</form>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Category</th>
    <th>Subcategory</th>
    <th>Company</th>
    <th>Product Stock</th>
    <th>Variants</th>
    <th>Total Stock</th>
    <th>Availability</th>
    <th>Posted</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($query)) { 
    $variant_query = mysqli_query($con, "SELECT SUM(stock) as total_variant_stock, COUNT(*) as variant_count FROM product_variants WHERE product_id=".(int)$row['id']);
    $variant_result = mysqli_fetch_assoc($variant_query);
    $variant_stock = intval($variant_result['total_variant_stock']);
    $variant_count = intval($variant_result['variant_count']);

    $product_stock = intval($row['stock']);
    $total_stock = $product_stock + $variant_stock;
?>
<tr>
    <td><?= $cnt++ ?></td>
    <td><?= htmlentities($row['productName']) ?></td>
    <td><?= htmlentities($row['categoryName']) ?></td>
    <td><?= htmlentities($row['subcat']) ?></td>
    <td><?= htmlentities($row['productCompany']) ?></td>

    <td><span class="badge <?= $product_stock==0?'bg-danger':($product_stock<=5?'bg-warning text-dark':'bg-success') ?>">
        <?= $product_stock ?></span>
    </td>

    <td>
        <?= $variant_count > 0 ? "<span class='badge bg-primary'>$variant_count</span>" : "<span class='badge bg-secondary'>None</span>" ?>
        /
        <?= $variant_stock > 0 ? "<span class='badge ".($variant_stock<=5?'bg-warning text-dark':'bg-info')."'>$variant_stock</span>" : "<span class='badge bg-secondary'>0</span>" ?>
    </td>

    <td><span class="badge <?= $total_stock==0?'bg-danger':($total_stock<=5?'bg-warning text-dark':'bg-success') ?>">
        <?= $total_stock ?>
    </span></td>

    <td><span class="badge <?= $row['productAvailability']=='In Stock'?'bg-success':'bg-danger' ?>">
        <?= $row['productAvailability'] ?>
    </span></td>

    <td><?= htmlentities($row['postingDate']) ?></td>

    <td>
        <a href="product_editor.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏ Edit</a>
        <a href="?id=<?= $row['id'] ?>&del=delete" onclick="return confirm('Delete this product?')" class="btn btn-sm btn-danger">🗑 Delete</a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</body>
</html>
