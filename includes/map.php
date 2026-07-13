<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Delivery Radius Check</title>
<style>
#popup {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.popup-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  font-family: Arial, sans-serif;
  max-width: 350px;
}
#location-display {
  font-size: 14px;
  margin-top: 10px;
}
.error-message {
  color: #ff4d4d;
  margin-top: 10px;
  font-size: 13px;
  white-space: pre-line;
}
</style>
</head>
<body>

<div id="popup">
  <div class="popup-content">
    <h3>📍 Detecting your location...</h3>
    <div id="location-display">Please wait...</div>
    <div id="delivery-message"></div>
  </div>
</div>

<script>
const GOOGLE_API_KEY = "YOUR_GOOGLE_API_KEY"; // <-- Apna API key daalo
const DELIVERY_RADIUS = 15; // km

function checkDelivery(lat, lng) {
    fetch("check_delivery.php?lat=" + lat + "&lng=" + lng)
    .then(res => res.json())
    .then(data => {
        if (data.available) {
            document.getElementById("location-display").innerHTML =
                `✅ Delivery Available — ${data.distance} km away`;
            document.getElementById("popup").style.display = "none";
        } else {
            document.getElementById("location-display").innerHTML =
                `🚫 Sorry! We're currently not delivering to your location.`;
            document.getElementById("delivery-message").innerHTML =
                `<div class="error-message">
                🚫 Sorry! We're currently not delivering to your location.<br>
                We’re expanding soon — stay tuned!
                </div>`;
        }
        localStorage.setItem("user_lat", lat);
        localStorage.setItem("user_lng", lng);
    });
}

function getAddress(lat, lng) {
    let url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${GOOGLE_API_KEY}`;
    fetch(url)
    .then(res => res.json())
    .then(data => {
        if (data.status === "OK") {
            let comp = data.results[0].address_components;
            let locality = "", city = "", pincode = "";
            comp.forEach(c => {
                if (c.types.includes("sublocality") || c.types.includes("locality")) locality = c.long_name;
                if (c.types.includes("administrative_area_level_2")) city = c.long_name;
                if (c.types.includes("postal_code")) pincode = c.long_name;
            });
            document.getElementById("location-display").innerHTML =
                `📍 ${locality}, ${city} - ${pincode}`;
        }
    });
}

function fetchLocation() {
    let savedLat = localStorage.getItem("user_lat");
    let savedLng = localStorage.getItem("user_lng");

    if (savedLat && savedLng) {
        getAddress(savedLat, savedLng);
        checkDelivery(savedLat, savedLng);
        return;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                let lat = pos.coords.latitude;
                let lng = pos.coords.longitude;
                getAddress(lat, lng);
                checkDelivery(lat, lng);
            },
            () => {
                document.getElementById("delivery-message").innerHTML = "❌ Location denied. Using default Bhilai location.";
                checkDelivery(21.1938, 81.3509); 
            },
            { enableHighAccuracy: false, timeout: 5000 }
        );
    } else {
        document.getElementById("delivery-message").innerHTML = "❌ Geolocation not supported.";
    }
}

fetchLocation();
</script>

</body>
</html>
