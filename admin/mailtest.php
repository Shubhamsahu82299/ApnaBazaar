<?php
include_once('includes/config.php')
require 'sendOrderEmail.php';
sendOrderEmail('', '', 250 , 'accepted');
echo "done";
?>