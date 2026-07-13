<?php
session_start();

$store_lat = 21.1657; // Maitri Kunj, Risali
$store_lng = 81.3207;
$radius_km = 15;

$user_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$user_lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;

function getDistanceKm($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) ** 2 +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}

$distance = getDistanceKm($store_lat, $store_lng, $user_lat, $user_lng);
$response = [
    'available' => ($distance <= $radius_km),
    'distance' => round($distance, 2)
];

$_SESSION['delivery_status'] = $response;

header('Content-Type: application/json');
echo json_encode($response);
