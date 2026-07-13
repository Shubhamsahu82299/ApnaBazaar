<?php
include('includes/config.php');

$limit = 10;
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

$sortQuery = "";
if (isset($_POST['sort'])) {
  switch ($_POST['sort']) {
    case 'price_asc': $sortQuery = "ORDER BY productPrice ASC"; break;
    case 'price_desc': $sortQuery = "ORDER BY productPrice DESC"; break;
    case 'newest': $sortQuery = "ORDER BY id DESC"; break;
    case 'name_asc': $sortQuery = "ORDER BY productName ASC"; break;
    case 'name_desc': $sortQuery = "ORDER BY productName DESC"; break;
  }
}

$ret = mysqli_query($con, "SELECT * FROM products WHERE productAvailability='In Stock' $sortQuery LIMIT $limit OFFSET $offset");

if (mysqli_num_rows($ret) > 0) {
  while ($row = mysqli_fetch_array($ret)) {
    include('includes/productgrid.php');
  }
} else {
  echo "no_more";
}
?>
