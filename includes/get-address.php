<?php
session_start();
include("config.php"); // your DB config

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lat'], $_POST['lon'])) {
    $userLat = $_POST['lat'];
    $userLon = $_POST['lon'];

    // Risali Bhilai center
    $centerLat = 21.1956;
    $centerLon = 81.3811;

    function getDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    $distance = getDistance($userLat, $userLon, $centerLat, $centerLon);

    $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$userLat&lon=$userLon";
    $opts = ["http" => ["header" => "User-Agent: blinkit-radius-check/1.0\r\n"]];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response) {
        $data = json_decode($response, true);
        $addr = $data['address'] ?? [];

        $house     = $addr['house_number'] ?? '';
        $road      = $addr['road'] ?? '';
        $landmark  = $addr['landmark'] ?? '';
        $village   = $addr['village'] ?? '';
        $suburb    = $addr['suburb'] ?? '';
        $neighbour = $addr['neighbourhood'] ?? '';
        $hamlet    = $addr['hamlet'] ?? '';
        $town      = $addr['town'] ?? '';
        $city      = $addr['city'] ?? '';
        $county    = $addr['county'] ?? '';
        $state     = $addr['state'] ?? '';
        $postcode  = $addr['postcode'] ?? '';

        $area = $landmark ?: $neighbour ?: $suburb ?: $hamlet ?: $village ?: '';
        $locality = $city ?: $town ?: $village ?: $county ?: '';

        $full = array_filter([$house, $road, $area, $locality, $state, $postcode]);
        $addressLine = implode(', ', $full);

        // Store in session
        $_SESSION['shippingAddress'] = $addressLine;
        $_SESSION['shippingCity'] = $locality;
        $_SESSION['shippingState'] = $state;
        $_SESSION['shippingPincode'] = $postcode;
        $_SESSION['shippingLandmark'] = $area;
        $_SESSION['user_lat'] = $userLat;
        $_SESSION['user_lon'] = $userLon;

        // DB update if user logged in
        if ($distance <= 10 && isset($_SESSION['id'])) {
            $userId = $_SESSION['id'];
            $stmt = $con->prepare("UPDATE users SET shippingAddress=?, shippingCity=?, shippingState=?, shippingPincode=? WHERE id=?");
            $stmt->bind_param("ssssi", $addressLine, $locality, $state, $postcode, $userId);
            $stmt->execute();
            $stmt->close();
        }

        $result = ($distance <= 10)
            ? ['status' => 'ok', 'message' => "Delivering to: <strong>$addressLine</strong>"]
            : ['status' => 'fail', 'message' => "<strong style='color:#d9534f;'>Sorry! We're not delivering to your location right now.</strong>"];
    } else {
        $result = ['status' => 'fail', 'message' => 'Unable to fetch address. Please try again.'];
    }

    header('Content-Type: application/json');
    echo json_encode($result);
}
