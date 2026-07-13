<?php
session_start();

// Get POSTed JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['latitude']) && isset($data['longitude'])) {
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];

    // Optionally use reverse geocoding API
    $address = file_get_contents("https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}");
    $addressData = json_decode($address, true);
    
    $_SESSION['user_location'] = [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'address' => $addressData['display_name'] ?? 'Unknown location'
    ];

    // Optional: Save to DB if user is logged in
    // if (isset($_SESSION['user_id'])) {
    //     // Save to DB logic here
    // }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
