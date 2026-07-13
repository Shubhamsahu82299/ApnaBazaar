<?php
  $waPhone = "918668464275";  // admin WhatsApp number (country code+number, no + sign)
  
  $userId = $_SESSION['id'];
  $userQuery = mysqli_query($con, "SELECT name, contactno, shippingAddress, shippingCity, shippingState, shippingPincode FROM users WHERE id='$userId'");
  $userData = mysqli_fetch_assoc($userQuery);

  $userName = $userData['name'];
  $userMobile = $userData['contactno'];
  $userAddr = $userData['shippingAddress'];
  $userCity = $userData['shippingCity'];
  $userState = $userData['shippingState'];
  $userPin = $userData['shippingPincode'];
?>
<div style="margin-top: 14px; text-align:center;">
  <button onclick="sendFullLocationToWhatsApp()" 
          style="background-color: #25D366; color: white; border: none; padding: 10px 16px; border-radius: 6px; font-weight: bold; font-size: 14px;">
    📍 Send My Location + Details via WhatsApp
  </button>
</div>

<script>
function sendFullLocationToWhatsApp() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const mapsUrl = `https://maps.google.com/?q=${lat},${lng}`;

      const name = "<?php echo addslashes($userName); ?>";
      const mobile = "<?php echo addslashes($userMobile); ?>";
      const address = "<?php echo addslashes($userAddr); ?>";
      const city = "<?php echo addslashes($userCity); ?>";
      const state = "<?php echo addslashes($userState); ?>";
      const pincode = "<?php echo addslashes($userPin); ?>";

      const message = `🧍 Name: ${name}%0A📞 Mobile: ${mobile}%0A🏠 Address: ${address}, ${city}, ${state} - ${pincode}%0A📍 Location: ${mapsUrl}`;

      const waUrl = `https://wa.me/<?php echo $waPhone; ?>?text=${message}`;
      window.open(waUrl, '_blank');
    }, function(error) {
      alert("❌ Location permission denied.");
    });
  } else {
    alert("❌ Geolocation not supported.");
  }
}
</script>
