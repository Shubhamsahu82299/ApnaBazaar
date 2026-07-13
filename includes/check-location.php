<?php
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
{
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $earthRadius * $angle;
}


$targetLat = 21.2187;
$targetLng = 81.3797;

if (isset($_POST['lat']) && isset($_POST['lng'])) {
    $userLat = floatval($_POST['lat']);
    $userLng = floatval($_POST['lng']);

    $distance = haversineGreatCircleDistance($userLat, $userLng, $targetLat, $targetLng);

    if ($distance <= 10) {
        echo "✅ We can deliver to you! You're within " . round($distance, 2) . " km.";
    } else {
        echo "❌ Sorry, we cannot reach you currently.<br>But stay tuned, we are expanding very soon!";
    }
} else {
    echo "No location data received.";
}
?>
