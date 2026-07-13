<?php
session_start();
include('includes/config.php');

// Get order details from URL parameters
$orderId = $_GET['order_id'] ?? '';
$customerName = $_GET['customer_name'] ?? '';
$orderStatus = $_GET['order_status'] ?? '';
$deliveryAddress = $_GET['delivery_address'] ?? '';

// If no parameters, show error
if (empty($orderId)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Location - ApnaBazaar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .location-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            margin: 50px auto;
        }
        .header-section {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: #4CAF50;
        }
        .content-section {
            padding: 30px;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .location-btn {
            background: linear-gradient(45deg, #25D366, #128C7E);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
        }
        .location-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            color: white;
        }
        .search-btn {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
            color: white;
        }
        .manual-btn {
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        .manual-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
            color: white;
        }
        .status-badge {
            background: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .map-container {
            height: 200px;
            background: #e9ecef;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        .loading {
            display: none;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="location-card">
            <!-- Header Section -->
            <div class="header-section">
                <div class="logo">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h2>Share Your Location</h2>
                <p>Help us deliver your order faster!</p>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <!-- Order Information -->
                <div class="order-info">
                    <h5><i class="fas fa-shopping-bag"></i> Order Details</h5>
                    <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($orderId); ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($customerName); ?></p>
                    <p><strong>Status:</strong> <span class="status-badge"><?php echo htmlspecialchars($orderStatus); ?></span></p>
                    <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($deliveryAddress); ?></p>
                </div>

                <!-- Map Container -->
                <div class="map-container" id="mapContainer">
                    <div class="text-center">
                        <i class="fas fa-map fa-3x mb-3"></i>
                        <p>Your location will appear here</p>
                    </div>
                </div>

                <!-- Location Buttons -->
                <div class="text-center">
                    <button class="location-btn" onclick="shareCurrentLocation()">
                        <i class="fab fa-whatsapp"></i> Share Current Location
                    </button>
                    
                    <button class="search-btn" onclick="searchDeliveryLocation()">
                        <i class="fas fa-search"></i> Search Delivery Location
                    </button>
                    
                    <button class="manual-btn" onclick="manualLocation()">
                        <i class="fas fa-edit"></i> Enter Location Manually
                    </button>
                </div>

                <!-- Loading Section -->
                <div class="loading text-center" id="loadingSection">
                    <div class="spinner"></div>
                    <p>Getting your location...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentLocation = null;

        // Share Current Location
        function shareCurrentLocation() {
            showLoading();
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        currentLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        
                        hideLoading();
                        showLocationOnMap();
                        sendLocationToWhatsApp('current');
                    },
                    function(error) {
                        hideLoading();
                        alert('Unable to get your location. Please try again or use manual entry.');
                        console.error('Geolocation error:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                hideLoading();
                alert('Geolocation is not supported by this browser. Please use manual entry.');
            }
        }

        // Search Delivery Location
        function searchDeliveryLocation() {
            const deliveryAddress = '<?php echo addslashes($deliveryAddress); ?>';
            const searchUrl = `https://www.google.com/maps/search/${encodeURIComponent(deliveryAddress)}`;
            window.open(searchUrl, '_blank');
            
            // Also send WhatsApp message
            sendLocationToWhatsApp('search');
        }

        // Manual Location Entry
        function manualLocation() {
            const address = prompt('Please enter your delivery address:');
            if (address) {
                sendLocationToWhatsApp('manual', address);
            }
        }

        // Show Location on Map
        function showLocationOnMap() {
            if (currentLocation) {
                const mapContainer = document.getElementById('mapContainer');
                mapContainer.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-map-marker-alt fa-3x text-success mb-3"></i>
                        <p><strong>Location Found!</strong></p>
                        <p class="small">Lat: ${currentLocation.lat.toFixed(6)}</p>
                        <p class="small">Lng: ${currentLocation.lng.toFixed(6)}</p>
                    </div>
                `;
            }
        }

        // Send Location to WhatsApp
        function sendLocationToWhatsApp(type, manualAddress = '') {
            const orderId = '<?php echo addslashes($orderId); ?>';
            const customerName = '<?php echo addslashes($customerName); ?>';
            const orderStatus = '<?php echo addslashes($orderStatus); ?>';
            const deliveryAddress = '<?php echo addslashes($deliveryAddress); ?>';
            
            let message = `Hi ${customerName}! 👋\n\n`;
            message += `Your order #${orderId} status: ${orderStatus}\n\n`;
            message += `📍 *Delivery Address:*\n${deliveryAddress}\n\n`;
            
            if (type === 'current' && currentLocation) {
                message += `📍 *Your Current Location:*\n`;
                message += `Latitude: ${currentLocation.lat.toFixed(6)}\n`;
                message += `Longitude: ${currentLocation.lng.toFixed(6)}\n\n`;
                message += `🔗 *Google Maps Link:*\n`;
                message += `https://maps.google.com/?q=${currentLocation.lat},${currentLocation.lng}\n\n`;
            } else if (type === 'search') {
                message += `📍 *Location Search:*\n`;
                message += `I'm searching for delivery location\n\n`;
            } else if (type === 'manual') {
                message += `📍 *Manual Address:*\n${manualAddress}\n\n`;
            }
            
            message += `🚚 *Delivery Options:*\n`;
            message += `• Confirm current address ✅\n`;
            message += `• Share updated location 📍\n`;
            message += `• Request delivery time ⏰\n\n`;
            message += `For any queries:\n📞 Customer Support\n🌐 www.ApnaBazaarservicepoint.store`;
            
            // Create WhatsApp URL
            const whatsappUrl = `https://wa.me/917884074846?text=${encodeURIComponent(message)}`;
            
            // Open WhatsApp
            window.open(whatsappUrl, '_blank');
        }

        // Show/Hide Loading
        function showLoading() {
            document.getElementById('loadingSection').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loadingSection').style.display = 'none';
        }

        // Auto-detect location on page load (optional)
        window.addEventListener('load', function() {
            // You can auto-detect location here if needed
            console.log('Location sharing page loaded');
        });
    </script>
</body>
</html> 