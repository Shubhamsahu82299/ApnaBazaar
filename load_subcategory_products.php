<?php
include('includes/config.php');

$scid = intval($_POST['scid']);
$limit = 10;
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

$query = "SELECT * FROM products WHERE subCategory='$scid' AND productAvailability='In Stock' LIMIT $limit OFFSET $offset";
$ret = mysqli_query($con, $query);

if (mysqli_num_rows($ret) > 0) {
    while ($row = mysqli_fetch_array($ret)) {
        include('includes/productgridsubcat.php');
    }
} else {
    echo "no_more";
}
?>
