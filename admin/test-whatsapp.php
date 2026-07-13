<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>WhatsApp Integration Test</h2>
    
    <button type="button" class="send-whatsapp-btn" 
        data-contact="9876543210"
        data-customer-name="Test Customer"
        data-order-id="123"
        data-order-status="Accepted"
        style="background: #25d366; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
        📱 Test WhatsApp
    </button>

    <script>
        $('.send-whatsapp-btn').on('click', function() {
            const $btn = $(this);
            const contact = $btn.data('contact');
            const customerName = $btn.data('customer-name');
            const orderId = $btn.data('order-id');
            const orderStatus = $btn.data('order-status');

            console.log('Contact:', contact);
            console.log('Customer Name:', customerName);
            console.log('Order ID:', orderId);
            console.log('Order Status:', orderStatus);

            // Format phone number
            let phoneNumber = contact.replace(/^\+91/, '').replace(/\s+/g, '');
            if (!phoneNumber.startsWith('91') && phoneNumber.length === 10) {
                phoneNumber = '91' + phoneNumber;
            }

            console.log('Formatted Phone:', phoneNumber);

            // Create WhatsApp message
            const message = `Hi ${customerName}! 👋

Your order #${orderId} status has been updated to: ${orderStatus}

Thank you for choosing ApnaBazaar! 🛍️

For any queries, please contact us.`;

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
                $btn.text('📱 Test WhatsApp').css('background', '#25d366');
            }, 2000);
        });
    </script>
</body>
</html> 