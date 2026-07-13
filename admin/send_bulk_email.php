<?php
include_once('includes/config.php');
include_once('includes/email-config.php');
require_once('../PHPMailer/src/Exception.php');
require_once('../PHPMailer/src/PHPMailer.php');
require_once('../PHPMailer/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($to) || empty($subject) || empty($message)) {
        echo "Invalid data";
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        // SMTP settings from email-config.php
        $mail->isSMTP();
        $mail->Host = $SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $SMTP_EMAIL;
        $mail->Password = $SMTP_PASSWORD;
        $mail->SMTPSecure = $SMTP_SECURE === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $SMTP_PORT;

        $mail->setFrom($SMTP_EMAIL, $COMPANY_NAME);
        $mail->addReplyTo($REPLY_TO, $REPLY_TO_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $EMAIL_SUBJECT_PREFIX . " " . $subject;
        $mail->Body = nl2br($message); // jo bhi ad likhoge vo HTML me jayega
        $mail->AltBody = strip_tags($message);

        $mail->send();
        echo "Sent to $to";
    } catch (Exception $e) {
        echo "Failed: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request";
}
