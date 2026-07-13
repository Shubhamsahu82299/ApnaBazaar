<!DOCTYPE html>
<html>
<head>
    <title>Location Check</title>
</head>
<body>
    <h2>Checking Your Location...</h2>
    <div id="result"></div>

    <script>
        navigator.geolocation.getCurrentPosition(success, error);

        function success(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            // Send to PHP
            fetch('check-location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `lat=${latitude}&lng=${longitude}`
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('result').innerHTML = data;
            });
        }

        function error() {
            document.getElementById('result').innerText = "Location access denied!";
        }
    </script>
</body>
</html>
