<?php
require_once 'mail.php';

echo "<h2>Testing Email...</h2>";

$test_email = 'shynayip913@gmail.com';  // your own email
$test_name = 'Test User';
$test_token = 'test123456';

if (sendPasswordResetEmail($test_email, $test_name, $test_token)) {
    echo "<p style='color:green; font-size:18px;'>✅ PHPMailer says it was sent successfully.</p>";
} else {
    echo "<p style='color:red; font-size:18px;'>❌ Failed to send.</p>";
}

echo "<hr><h3>Check your PHP error log too!</h3>";
?>