<?php



// Show errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB Connection
include_once('includes/config.php');

// Handle form
if (isset($_POST['submit'])) {
    $categoryid = $_POST['category'];
    $subcategory = $_POST['subcategory'];

    $sql = mysqli_query($conn, "INSERT INTO subcategory (categoryid, subcategory) VALUES ('$categoryid', '$subcategory')");
    
    if ($sql) {
        echo "<script>alert('Subcategory added successfully');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Subcategory</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body>
    <?php include('includes/main-header.php') ?>
    <div class="container">
        <h2>Add Subcategory</h2>
        <form method="post">
            <div class="form-group">
                <label>Category</label>
                <select name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php 
                    $result = mysqli_query($conn, "SELECT * FROM category");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['id'] . '">' . $row['categoryName'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Subcategory Name</label>
                <input type="text" name="subcategory" class="form-control" placeholder="Enter Subcategory Name" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Add Subcategory</button>
        </form>
    </div>
</body>
</html>
