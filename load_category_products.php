<?php
include('includes/config.php');

$cid = intval($_POST['cid']);
$limit = 10;
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

$query = "SELECT * FROM products WHERE category='$cid' AND productAvailability='In Stock' LIMIT $limit OFFSET $offset";
$ret = mysqli_query($con, $query);

if (mysqli_num_rows($ret) > 0) {
    while ($row = mysqli_fetch_array($ret)) {
        include('includes/productgrid.php');
    }
} else {
    echo "no_more";
}
?>
