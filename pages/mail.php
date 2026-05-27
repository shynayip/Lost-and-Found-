<?php
require __DIR__ . '/PHPMailer-7.1.1/src/Exception.php';
require __DIR__ . '/PHPMailer-7.1.1/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-7.1.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($email, $name, $reset_token) {
    $mail = new PHPMailer(true);
    
    try {
        // Turn off debug for production
        $mail->SMTPDebug = 0;        // Changed from 2 to 0
        $mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shynayip913@gmail.com';
        $mail->Password   = 'ixkdtvebvrcfnclr';   // ← Keep your app password

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shynayip913@gmail.com', 'Lost and Found Campus Hub');
        $mail->addAddress($email, $name ?? 'User');

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - Lost & Found Campus Hub';

        $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") 
                    . $_SERVER['HTTP_HOST'] 
                    . "/pages/resetpassword.php?token=" 
                    . urlencode($reset_token);

        $mail->Body = "
        <h2>Password Reset Request</h2>
        <p>Hello {$name},</p>
        <p>You requested to reset your password. Click the button below:</p>
        <p style='margin: 25px 0;'>
            <a href='{$reset_link}' style='background:#e74c3c; color:white; padding:14px 28px; text-decoration:none; border-radius:5px; font-weight:bold;'>
                Reset My Password
            </a>
        </p>
        <p>This link will expire in 1 hour.</p>
        <p>If you did not request this, please ignore this email.</p>";

        $mail->AltBody = "Reset link: {$reset_link}\n\nExpires in 1 hour.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>