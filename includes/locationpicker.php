<style>
#location-display {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-family: Arial, sans-serif;
  font-size: 12px;
  color: #333;
  cursor: pointer;
  padding: 0;
  margin: 0;
  line-height: 1.2;
}

#location-display svg {
  width: 14px;
  height: 14px;
  fill: #4caf50;
  margin: 0;
  padding: 0;
}
</style>

<div id="location-display">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z"/>
  </svg>
  <span id="address-text">Detecting your location...</span>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const addressSpan = document.getElementById("address-text");

  fetch("/session-check-address.php")
    .then(res => res.json())
    .then(data => {
      if (data.status === "ok") {
        addressSpan.innerHTML = data.message;
      } else {
        detectLocation();
      }
    })
    .catch(() => {
      detectLocation();
    });

  function detectLocation() {
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;

        fetch("/get-address.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `lat=${lat}&lon=${lon}`
        })
        .then(res => res.json())
        .then(data => {
          addressSpan.innerHTML = data.message;
          if (data.status === "fail") {
            alert("🚫 Sorry! We're currently not delivering to your location.\nWe’re expanding soon — stay tuned!");
          }
        })
        .catch(() => {
          addressSpan.innerText = "Unable to fetch address.";
        });
      },
      function () {
        addressSpan.innerText = "Location access denied.";
      }
    );
  }
});
</script>