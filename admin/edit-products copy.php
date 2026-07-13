<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('location:index.php'); // index.php = your login page
    exit;
}
include_once('includes/config.php');
include('includes/main-header.php');

$pid = intval($_GET['id']);
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

    $sql = mysqli_query($conn, "UPDATE products SET 
        category='$category',
        subCategory='$subcat',
        productName='$productname',
        productCompany='$productcompany',
        productPrice='$productprice',
        productDescription='$productdescription',
        shippingCharge='$productscharge',
        productAvailability='$productavailability',
        productPriceBeforeDiscount='$productpricebd'
        WHERE id='$pid'
    ");

    $msg = "Product Updated Successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { font-weight: 600; }
        img { margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; padding: 3px; }
    </style>
    <script>
    function getSubcat(val) {
        $.ajax({
            type: "POST",
            url: "get_subcat.php",
            data: 'cat_id=' + val,
            success: function(data){
                $("#subcategory").html(data);
            }
        });
    }
    </script>
</head>
<body>
<?include("includes/main-header.php") ?>
<div class="container form-container">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Edit Product</h3>
    <a href="manage-products.php" class="btn btn-success">Go back to manage product</a>
</div>
     
    <?php if (isset($msg)) { ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php } ?>

    <?php
    $query = mysqli_query($conn, "SELECT products.*, category.categoryName AS catname, category.id AS cid, subcategory.subcategory AS subcatname, subcategory.id AS subcatid 
        FROM products 
        JOIN category ON category.id=products.category 
        JOIN subcategory ON subcategory.id=products.subCategory 
        WHERE products.id='$pid'");
    $row = mysqli_fetch_array($query);
    ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Category</label>
            <select name="category" class="form-select" onchange="getSubcat(this.value);" required>
                <option value="<?php echo $row['cid']; ?>"><?php echo $row['catname']; ?></option>
                <?php
                $cats = mysqli_query($conn, "SELECT * FROM category");
                while ($c = mysqli_fetch_array($cats)) {
                    if ($c['id'] != $row['cid']) {
                        echo '<option value="'.$c['id'].'">'.$c['categoryName'].'</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Subcategory</label>
            <select name="subcategory" id="subcategory" class="form-select" required>
                <option value="<?php echo $row['subcatid']; ?>"><?php echo $row['subcatname']; ?></option>
            </select>
        </div>

        <div class="mb-3">
            <label>Product Name</label>
            <input type="text" name="productName" class="form-control" value="<?php echo $row['productName']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Company</label>
            <input type="text" name="productCompany" class="form-control" value="<?php echo $row['productCompany']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Price Before Discount</label>
            <input type="number" name="productpricebd" class="form-control" value="<?php echo $row['productPriceBeforeDiscount']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Selling Price</label>
            <input type="number" name="productprice" class="form-control" value="<?php echo $row['productPrice']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="productDescription" rows="5" class="form-control"><?php echo $row['productDescription']; ?></textarea>
        </div>

        <div class="mb-3">
            <label>Shipping Charge</label>
            <input type="number" name="productShippingcharge" class="form-control" value="<?php echo $row['shippingCharge']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Availability</label>
            <select name="productAvailability" class="form-select" required>
                <option value="In Stock" <?php if ($row['productAvailability'] == "In Stock") echo "selected"; ?>>In Stock</option>
                <option value="Out of Stock" <?php if ($row['productAvailability'] == "Out of Stock") echo "selected"; ?>>Out of Stock</option>
            </select>
        </div>

      <style>
    .product-image {
        width: 100%;
        height: 150px;
        object-fit: contain;
        border: 1px solid #ddd;
        padding: 5px;
        background-color: #fff;
    }
</style>

<div class="row">
    <div class="col-md-4 mb-3 text-center">
        <label class="fw-bold">Image 1</label><br>
        <img src="productimages/<?php echo $pid; ?>/<?php echo $row['productImage1']; ?>" class="product-image mb-2"><br>
        <a href="update-image1.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-primary">Change Image</a>
    </div>

    <div class="col-md-4 mb-3 text-center">
        <label class="fw-bold">Image 2</label><br>
        <img src="productimages/<?php echo $pid; ?>/<?php echo $row['productImage2']; ?>" class="product-image mb-2"><br>
        <a href="update-image2.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-primary">Change Image</a>
    </div>

    <div class="col-md-4 mb-3 text-center">
        <label class="fw-bold">Image 3</label><br>
        <img src="productimages/<?php echo $pid; ?>/<?php echo $row['productImage3']; ?>" class="product-image mb-2"><br>
        <a href="update-image3.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-primary">Change Image</a>
    </div>
</div>


        <button type="submit" name="submit" class="btn btn-primary">Update Product</button>
    </form>
    
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
