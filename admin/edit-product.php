<?php 
// DB Connection (no session, no logout redirect)
include_once('includes/config.php');
// For Updating Product
if(isset($_POST['submit'])) {
    $pid=intval($_GET['id']);
    $category=$_POST['category'];
    $subcat=$_POST['subcategory'];
    $productname=$_POST['productName'];
    $productcompany=$_POST['productCompany'];
    $productprice=$_POST['productprice'];
    $productpricebd=$_POST['productpricebd'];
    $productdescription=$_POST['productDescription'];
    $productscharge=$_POST['productShippingcharge'];
    $productavailability=$_POST['productAvailability'];
    $updatedby = 1; // hardcoded admin id for now

    $sql=mysqli_query($conn,"UPDATE products SET 
        category='$category',
        subCategory='$subcat',
        productName='$productname',
        productCompany='$productcompany',
        productPrice='$productprice',
        productDescription='$productdescription',
        shippingCharge='$productscharge',
        productAvailability='$productavailability',
        productPriceBeforeDiscount='$productpricebd',
        lastUpdatedBy='$updatedby'
        WHERE id='$pid'");
    
    echo "<script>alert('Product details updated successfully');</script>";
    echo "<script>window.location.href='manage-products.php'</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>ApnaBazaar Shopping Portal | Edit Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="js/all.min.js" crossorigin="anonymous"></script>
    <script src="js/jquery-3.5.1.min.js"></script>
    <script>
    function getSubcat(val) {
        $.ajax({
            type: "POST",
            url: "get_subcat.php",
            data: 'cat_id=' + val,
            success: function(data) {
                $("#subcategory").html(data);
            }
        });
    }
    </script>
</head>

    <?php include_once('includes/main-header.php'); ?><body>
    <div id="layoutSidenav">
      
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Edit Product</h1>
                    <div class="card mb-4">
                        <div class="card-body">
                            <?php 
                            $pid=intval($_GET['id']);
                            $query=mysqli_query($conn,"SELECT products.id as pid, products.productImage1, products.productImage2, products.productImage3, products.productName, category.categoryName, subcategory.subcategoryName as subcatname, products.postingDate, products.updationDate, subcategory.id as subid, category.id as catid, products.productCompany, products.productPrice, products.productPriceBeforeDiscount, products.productAvailability, products.productDescription, products.shippingCharge FROM products JOIN subcategory ON products.subCategory=subcategory.id JOIN category ON products.category=category.id WHERE products.id='$pid'");
                            while($row=mysqli_fetch_array($query)) {
                            ?>                                 
                            <form method="post" enctype="multipart/form-data">                                
                                <div class="row mb-3">
                                    <div class="col-2">Category</div>
                                    <div class="col-6">
                                        <select name="category" id="category" class="form-control" onChange="getSubcat(this.value);" required>
                                            <option value="<?php echo $row['catid']; ?>"><?php echo $row['categoryName']; ?></option> 
                                            <?php 
                                            $ret=mysqli_query($conn,"SELECT * FROM category");
                                            while($result=mysqli_fetch_array($ret)) {
                                                if($result['id'] != $row['catid']) {
                                            ?>
                                            <option value="<?php echo $result['id'];?>"><?php echo $result['categoryName'];?></option>
                                            <?php } } ?>
                                        </select>    
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Subcategory</div>
                                    <div class="col-6">
                                        <select name="subcategory" id="subcategory" class="form-control" required>
                                            <option value="<?php echo $row['subid']; ?>"><?php echo $row['subcatname']; ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Product Name</div>
                                    <div class="col-6">
                                        <input type="text" name="productName" value="<?php echo $row['productName']; ?>" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Product Company</div>
                                    <div class="col-6">
                                        <input type="text" name="productCompany" value="<?php echo $row['productCompany']; ?>" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Price Before Discount</div>
                                    <div class="col-6">
                                        <input type="number" step="0.01" name="productpricebd" value="<?php echo $row['productPriceBeforeDiscount']; ?>" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Selling Price</div>
                                    <div class="col-6">
                                        <input type="number" step="0.01" name="productprice" value="<?php echo $row['productPrice']; ?>" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Product Description</div>
                                    <div class="col-6">
                                        <textarea name="productDescription" rows="6" class="form-control"><?php echo $row['productDescription']; ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Shipping Charge</div>
                                    <div class="col-6">
                                        <input type="number" step="0.01" name="productShippingcharge" value="<?php echo $row['shippingCharge']; ?>" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Availability</div>
                                    <div class="col-6">
                                        <select name="productAvailability" class="form-control" required>
                                            <option value="In Stock" <?php if($row['productAvailability']=='In Stock') echo "selected"; ?>>In Stock</option>
                                            <option value="Out of Stock" <?php if($row['productAvailability']=='Out of Stock') echo "selected"; ?>>Out of Stock</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Image 1</div>
                                    <div class="col-6">
                                        <img src="productimages/<?php echo $row['productImage1'];?>" width="200"><br>
                                        <a href="change-image1.php?id=<?php echo $row['pid']; ?>">Change Image</a>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Image 2</div>
                                    <div class="col-6">
                                        <img src="productimages/<?php echo $row['productImage2'];?>" width="200"><br>
                                        <a href="change-image2.php?id=<?php echo $row['pid']; ?>">Change Image</a>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-2">Image 3</div>
                                    <div class="col-6">
                                        <img src="productimages/<?php echo $row['productImage3'];?>" width="200"><br>
                                        <a href="change-image3.php?id=<?php echo $row['pid']; ?>">Change Image</a>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-6 offset-2">
                                        <button type="submit" name="submit" class="btn btn-success">Update Product</button>
                                    </div>
                                </div>
                            </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include_once('includes/footer.php'); ?>
        </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
