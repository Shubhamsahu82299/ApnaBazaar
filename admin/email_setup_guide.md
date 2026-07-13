# Gmail Notification Setup Guide

## Overview
This feature allows you to send email notifications to customers when their order status is updated. The emails are sent using Gmail SMTP.

## Setup Instructions

### 1. Gmail Account Setup
1. **Enable 2-Step Verification** (if not already enabled):
   - Go to your Google Account settings
   - Navigate to Security
   - Enable 2-Step Verification

2. **Generate App Password**:
   - Go to Security > App passwords
   - Select "Mail" as the app
   - Click "Generate"
   - Copy the 16-character password

### 2. Configure Email Settings
Edit the file: `admin/includes/email-config.php`

Replace these values:
```php
$GMAIL_EMAIL = "";  // Your Gmail address
$GMAIL_PASSWORD = "your-app-password";  // The 16-character app password
```

### 3. Test the Feature
1. Go to Admin Panel > Manage Orders
2. Find an order with a customer email
3. Click the "📧 Send Mail" button
4. Check if the email is sent successfully

## Features
- ✅ Professional HTML email template
- ✅ Order details included (ID, product, status, amount)
- ✅ Status-based color coding
- ✅ Real-time button feedback
- ✅ Error handling and user notifications

## Troubleshooting
- **"Authentication failed"**: Check your Gmail app password
- **"Connection timeout"**: Check your internet connection
- **"Email not sent"**: Verify the customer has a valid email address

## Email Template
The email includes:
- ApnaBazaar branding
- Order details (ID, product, date, amount)
- Current order status with color coding
- Professional styling
- Contact information

## Security Notes
- Never commit your actual Gmail password to version control
- Use app passwords instead of your main Gmail password
- The email-config.php file should be kept secure 