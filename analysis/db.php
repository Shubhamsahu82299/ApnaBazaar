<?php
// Database credentials from env (with fallback)
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'u814646522_ApnaBazaars';
$DB_USER = getenv('DB_USER') ?: 'u814646522_ApnaBazaarspss';
$DB_PASS = getenv('DB_PASS') ?: 'ApnaBazaar967';

// Create connection
$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if (!$con) {
    die("❌ Failed to connect to MySQL: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');
mysqli_query($con, "SET time_zone = '+05:30'");
?>
