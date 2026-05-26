<?php
require __DIR__ . '/PHPMailer-7.1.1/src/Exception.php';
require __DIR__ . '/PHPMailer-7.1.1/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-7.1.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($email, $name, $reset_token) {
    $mail = new PHPMailer(true);
    
    try {
        // Force maximum debug
        $mail->SMTPDebug = 3;
        $mail->Debugoutput = function($str, $level) {
            echo "<div style='background:#ffffcc; color:#000; padding:10px; margin:5px 0; border:1px solid #cc0; font-family:monospace;'>";
            echo nl2br(htmlspecialchars($str));
            echo "</div>";
        };

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shynayip913@gmail.com';
        
        // ←←← CHANGE THIS TO YOUR REAL APP PASSWORD
        $mail->Password   = 'ixkdtvebvrcfnclr';
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shynayip913@gmail.com', 'Lost and Found Campus Hub');
        $mail->addAddress($email, $name ?? 'User');

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - Lost & Found Campus Hub';

        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/pages/resetpassword.php?token=" . urlencode($reset_token);

        $mail->Body = "
        <h2>Password Reset Request</h2>
        <p>Hello {$name},</p>
        <p>Click here to reset your password:</p>
        <p><a href='{$reset_link}'>Reset Password</a></p>";

        $mail->AltBody = "Reset link: {$reset_link}";

        echo "<h3>Attempting to send email to: " . htmlspecialchars($email) . "</h3>";

        $mail->send();
        
        echo "<p style='color:green; font-size:18px;'>✅ PHPMailer says SUCCESS.</p>";
        return true;

    } catch (Exception $e) {
        echo "<div style='background:#ff4444; color:white; padding:15px; margin:10px 0; border-radius:5px;'>";
        echo "<strong>ERROR:</strong><br>";
        echo htmlspecialchars($mail->ErrorInfo);
        echo "</div>";
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>