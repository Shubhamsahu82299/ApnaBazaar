<?php
session_start();
?>

<style>
.location-popup {
    background: #f0f0f0;
    padding: 10px 15px;
    border-radius: 10px;
    font-size: 14px;
    display: inline-block;
    margin: 10px 0;
}
</style>

<div class="location-popup">
    <?php if (!empty($_SESSION['shippingAddress'])): ?>
        📍 <strong><?= htmlspecialchars($_SESSION['shippingAddress']) ?></strong>
    <?php else: ?>
        📍 Detecting your location...
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const isLocationSet = <?= isset($_SESSION['shippingAddress']) ? 'true' : 'false' ?>;

    if (!isLocationSet && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            fetch("get-address.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `lat=${lat}&lon=${lon}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    location.reload(); // Page reload after address stored in session
                } else {
                    showLocationError(data.message);
                }
            })
            .catch(() => {
                showLocationError("⚠️ Unable to get your location.");
            });
        }, function () {
            showLocationError("⚠️ Location permission denied.");
        });
    }

    function showLocationError(msg) {
        const popup = document.querySelector('.location-popup');
        popup.innerHTML = msg;
        popup.style.color = '#d9534f';
    }
});
</script>

