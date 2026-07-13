<?php
include_once('includes/config.php');

header('Content-Type: application/json');

$result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM orders WHERE orderStatus != 'Accepted'");
$row = mysqli_fetch_assoc($result);
echo json_encode(['count' => (int)$row['count']]);
?>