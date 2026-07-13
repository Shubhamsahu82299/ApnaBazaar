<?php
session_start();
$response = ['locationExists' => false];

if (isset($_SESSION['user_location'])) {
    $response['locationExists'] = true;
}
echo json_encode($response);
