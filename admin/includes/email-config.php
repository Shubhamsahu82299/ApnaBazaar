<?php
// Email Configuration for Hostinger SMTP

// Hostinger Email Credentials
$SMTP_EMAIL = "";   // Your Hostinger email address
$SMTP_PASSWORD = "ApnaBazaar967@";                    // Your Hostinger email password

// SMTP Server Settings for Hostinger
$SMTP_HOST = "smtp.hostinger.com";
$SMTP_PORT = 465;                    // Use 465 for SSL, or 587 for TLS
$SMTP_SECURE = "ssl";               // Change to "tls" if using port 587

// Company Info
$COMPANY_NAME = "ApnaBazaar";
$COMPANY_EMAIL = $SMTP_EMAIL;       // Use same as sending email

// Email Subject Prefix
$EMAIL_SUBJECT_PREFIX = "Order Status Update - ";

// Optional: Reply-To settings
$REPLY_TO = "";
$REPLY_TO_NAME = "Support Team";
?>
