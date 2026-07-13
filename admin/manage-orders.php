<?php
// manage-orders.php
include('includes/main-header.php');


$allowedStatuses = ['Accepted', 'Processing', 'Shipped', 'Payment_Done', 'Delivered'];
$allowedSortFields = ['orders.id', 'orders.orderDate', 'users.name', 'products.productPrice'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <style>
        table { width: 95%; margin: 20px auto; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
        .pending, .processing { background-color: #f8d7da; }
        .shipped, .delivered, .payment_done { background-color: #d4edda; }
        .filter-sort { text-align: center; margin-bottom: 20px; }
        form.inline { display: inline-block; }
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: none;
            z-index: 9999;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .status-buttons button {
            margin: 2px;
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }

        .status-btn {
            background: #f1f1f1;
            color: #333;
        }

        .status-btn:hover {
            background: #007bff;
            color: white;
        }

        .current-status {
            background: #28a745;
            color: white;
            font-weight: bold;
            cursor: not-allowed;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function sendWhatsApp(contact, customerName, orderId, orderStatus, deliveryAddress) {
        console.log('sendWhatsApp called with:', contact, customerName, orderId, orderStatus, deliveryAddress);

        // ✅ Format phone number
        let phoneNumber = contact.replace(/^\+91/, '').replace(/\s+/g, '');
        if (!phoneNumber.startsWith('91') && phoneNumber.length === 10) {
            phoneNumber = '91' + phoneNumber;
        }

        // 🔥 Fetch order details
        fetch('get-order-details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}`
        })
        .then(response => response.json())
        .then(data => {
            let o = data.order || {};
            let message = `*Namaste ${customerName}! 🙏*\n\n` +
                          `Your order status has been updated!\n\n` +
                          `📦 *Order Summary:*\n` +
                          `• Order ID: #${orderId}\n` +
                          `• Product: ${o.productName || 'N/A'}\n` +
                          `• Order Date: ${o.orderDate || 'N/A'}\n` +
                          `• Total Amount: ₹${o.totalAmount || 'N/A'}\n` +
                          `• Current Status: *${orderStatus}*\n\n` +
                          `🛍️ Thank you for choosing *ApnaBazaar*!\n\n` +
                          `📍 Share your location:\nhttps://apnabazaar.store/share-location.html\n\n` +
                          `📞 *Contact Us:*\n` +
                          `🟢 WhatsApp: https://wa.me/YOUR_NUM\n\n` +
                          `☎️ Office Landline: YOUR_LANDLINE\n` +
                          `📱 Mobile No: YOUR_MOBILE\n` +
                          `🌐 *Follow Us On Social Media:*\n` +
                          `▶️ YouTube: https://youtube.com/@apnabazaar\n` +
                          `📘 Facebook: https://www.facebook.com/apnabazaar\n` +
                          `📸 Instagram: https://www.instagram.com/apnabazaar\n\n` +
                          `🌐 Website: https://apnabazaar.store`;

            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        })
        .catch(error => {
            console.error('Error fetching order details:', error);

            const message = `*Namaste ${customerName}! 🙏*\n\n` +
                            `Your order #${orderId} status is now: *${orderStatus}*\n\n` +
                            `🛍️ Thank you for choosing *ApnaBazaar*!\n\n` +
                            `📍 Share your location:\nhttps://apnabazaar.store/share-location.html\n\n` +
                            `📞 *Contact Us:*\n` +
                            `🟢 WhatsApp: https://wa.me/YOUR_NUM\n\n` +
                            `☎️ Office Landline: YOUR_LANDLINE\n` +
                            `📱 Mobile No: YOUR_MOBILE\n` +
                            `🌐 *Follow Us On Social Media:*\n` +
                            `▶️ YouTube: https://youtube.com/@apnabazaar\n` +
                            `📘 Facebook: https://www.facebook.com/apnabazaar\n` +
                            `📸 Instagram: https://www.instagram.com/apnabazaar\n\n` +
                            `🌐 Website: https://apnabazaar.store`;

            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        });
    }
</script>

</head>
<body>

<div class="filter-sort">
    <form id="filterForm" class="inline">
        <label>Status:</label>
        <select name="filter_status" id="filter_status">
            <option value="">All</option>
            <?php foreach ($allowedStatuses as $status): ?>
                <option value="<?= $status ?>"><?= $status ?></option>
            <?php endforeach; ?>
        </select>

        <label>Sort by:</label>
        <select name="sort_by" id="sort_by">
            <option value="orders.id">Order ID</option>
            <option value="orders.orderDate">Order Date</option>
            <option value="users.name">Customer</option>
            <option value="products.productPrice">Price</option>
        </select>

        <select name="sort_order" id="sort_order">
            <option value="desc" selected>Descending</option>
            <option value="asc">Ascending</option>
        </select>

        <input type="submit" value="Apply">
    </form>
</div>

<div id="orderTableContainer"></div>

<!-- Toast and Audio -->
<div id="toast">🔔 New Order Received!</div>
<audio id="alertSound"><source src="assets/audio/notification.mp3" type="audio/mpeg"></audio>

<script>
let lastOrderId = 0;

function fetchOrders() {
    const status = $('#filter_status').val();
    const sortBy = $('#sort_by').val();
    const sortOrder = $('#sort_order').val();

    $.get('fetch-orders.php', {
        filter_status: status,
        sort_by: sortBy,
        sort_order: sortOrder
    }, function (data) {
        const $data = $(data);
        const $ordersTable = $data.filter('table');
        const newOrderFlag = $data.filter('[data-neworder="yes"]');

        $('#orderTableContainer').html($ordersTable);

        // Play sound + toast if new order
        if (newOrderFlag.length) {
            $('#toast').fadeIn(200).delay(2000).fadeOut(400);
            const audio = document.getElementById('alertSound');
            audio.play();
        }

        // Re-bind form submit for status update
        $('.updateForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            $.post('update-order-status.php', $(this).serialize(), function () {
                fetchOrders(); // Refresh again
            });
        });

        // Re-bind send mail button click
        $('.send-mail-btn').off('click').on('click', function() {
            const $btn = $(this);
            const orderSession = $btn.data('order-session');
            const customerEmail = $btn.data('customer-email');
            const customerName = $btn.data('customer-name');
            const orderDate = $btn.data('order-date');

            // Disable button and show loading
            $btn.prop('disabled', true).text('Sending...');

            $.post('send-order-session-email.php', {
                order_session: orderSession,
                customer_email: customerEmail,
                customer_name: customerName,
                order_date: orderDate
            }, function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $btn.text('✅ Sent').css('background', '#28a745');
                        setTimeout(() => {
                            $btn.text('📧 Send Mail').css('background', '#007bff').prop('disabled', false);
                        }, 3000);
                    } else {
                        $btn.text('❌ Failed').css('background', '#dc3545');
                        setTimeout(() => {
                            $btn.text('📧 Send Mail').css('background', '#007bff').prop('disabled', false);
                        }, 3000);
                        alert('Error: ' + result.message);
                    }
                } catch (e) {
                    $btn.text('❌ Error').css('background', '#dc3545');
                    setTimeout(() => {
                        $btn.text('📧 Send Mail').css('background', '#007bff').prop('disabled', false);
                    }, 3000);
                    alert('Error sending email. Please try again.');
                }
            }).fail(function() {
                $btn.text('❌ Failed').css('background', '#dc3545');
                setTimeout(() => {
                    $btn.text('📧 Send Mail').css('background', '#007bff').prop('disabled', false);
                }, 3000);
                alert('Network error. Please try again.');
            });
        });

        // Re-bind WhatsApp button click
        $('.send-whatsapp-btn').off('click').on('click', function() {
            console.log('WhatsApp button clicked!');
            
            const $btn = $(this);
            const contact = $btn.data('contact');
            const customerName = $btn.data('customer-name');
            const orderId = $btn.data('order-id');
            const orderStatus = $btn.data('order-status');

            console.log('Data from button:', {
                contact: contact,
                customerName: customerName,
                orderId: orderId,
                orderStatus: orderStatus
            });

            // Format phone number (remove +91 if present, add if not)
            let phoneNumber = contact.replace(/^\+91/, '').replace(/\s+/g, '');
            if (!phoneNumber.startsWith('91') && phoneNumber.length === 10) {
                phoneNumber = '91' + phoneNumber;
            }

            console.log('Formatted phone number:', phoneNumber);

            // Create WhatsApp message
            const message = `*Namaste ${customerName}! 🙏*\n\n` +
                            `Your order #${orderId} status has been updated to: *${orderStatus}*\n\n` +
                            `🛍️ Thank you for choosing *ApnaBazaar*! \n\n` +
                            `📞 *Contact Us:* \n` +
                            `🟢 WhatsApp: https://wa.me/YOUR_NUM\n` +
                            `☎️ Office Landline: YOUR_LANDLINE \n` +
                            `📱 Mobile: YOUR_MOBILE \n\n` +
                            `🌐 *Follow Us:* \n` +
                            `▶️ YouTube: https://youtube.com/@apnabazaar \n` +
                            `📘 Facebook: https://www.facebook.com/apnabazaar \n` +
                            `📸 Instagram: https://www.instagram.com/apnabazaar \n\n` +
                            `🌍 Website: https://apnabazaar.store`;

            console.log('Message:', message);

            // Encode message for URL
            const encodedMessage = encodeURIComponent(message);

            // Create WhatsApp URL
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
            
            console.log('WhatsApp URL:', whatsappUrl);
            
            // Open WhatsApp in new tab
            window.open(whatsappUrl, '_blank');
            
            // Show success feedback
            $btn.text('✅ Opened').css('background', '#28a745');
            setTimeout(() => {
                $btn.text('📱 WhatsApp').css('background', '#25d366').prop('disabled', false);
            }, 2000);
        });
    });
}

fetchOrders();
setInterval(fetchOrders, 5000);

$('#filterForm').on('submit', function (e) {
    e.preventDefault();
    fetchOrders();
});
</script>
<script>
  // Background polling every 5 seconds
  setInterval(function () {
    fetch('order-status-mail-trigger.php'); // 🚀 This runs silently
  }, 5000);
  // Handle status button click
$(document).on('click', '.status-btn', function () {
    const button = $(this);
    const orderId = button.closest('.status-buttons').data('orderid');
    const newStatus = button.data('status');

    $.post('update-order-status.php', {
        order_id: orderId,
        status: newStatus,
        remark: '' // agar remark nahi lena hai to khali bhej do
    }, function (response) {
        if (response.trim() === "success") {
            
            fetchOrders(); // table refresh
        } else {
            alert("❌ Error: " + response);
        }
    });
});
</script>
</body>
</html>