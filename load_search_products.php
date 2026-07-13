<?php
include('includes/config.php');

$offset = intval($_POST['offset']);
$limit = 10;
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';
$sortOption = isset($_POST['sort']) ? $_POST['sort'] : '';

$order_by = "productName ASC";
switch ($sortOption) {
    case 'price_asc':
        $order_by = "productPrice ASC";
        break;
    case 'price_desc':
        $order_by = "productPrice DESC";
        break;
    case 'newest':
        $order_by = "id DESC";
        break;
    case 'alpha':
        $order_by = "productName ASC";
        break;
}

$products = [];

if (!empty($searchTerm)) {
    $likeTerm = '%' . $searchTerm . '%';
    $sql = "
    SELECT DISTINCT p.*
    FROM products p
    LEFT JOIN product_keywords k ON k.product_id = p.id
    WHERE (
        p.productName LIKE ?
        OR k.keyword LIKE ?
    )
    AND p.productAvailability = 'In Stock'
    ORDER BY $order_by
    LIMIT ? OFFSET ?
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssii", $likeTerm, $likeTerm, $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $stmt = $con->prepare("SELECT * FROM products WHERE productAvailability='In Stock' ORDER BY $order_by LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
}

while ($row = $res->fetch_assoc()) {
    include('includes/productgrid.php');
}

if ($res->num_rows == 0) {
    echo "no_more";
}
?>
