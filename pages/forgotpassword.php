<?php
session_start();
require_once '../db.php';
require_once 'mail.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires) 
                                 VALUES (?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)");
            $stmt->bind_param('sss', $email, $token, $expires);
            $stmt->execute();

            $reset_link = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/resetpassword.php?token=" . $token;

            $subject = "Reset Your Password - Lost & Found Campus Hub";
            
            $message = "
            <html>
            <head>
                <title>Password Reset</title>
            </head>
            <body>
                <h2>Password Reset Request</h2>
                <p>Hello,</p>
                <p>You requested to reset your password. Click the button below:</p>
                <p style='margin: 25px 0;'>
                    <a href='$reset_link' style='background:#e74c3c; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold;'>
                        Reset My Password
                    </a>
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, please ignore this email.</p>
                <p>Best regards,<br><strong>Lost & Found Campus Hub</strong></p>
            </body>
            </html>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $success = "Password reset link has been sent to your email!";
            } else {
                $error = "Failed to send email. Please try again later.";
            }
        } else {
            // For security, show same message even if email not found
            $success = "If an account with that email exists, a reset link has been sent.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — Lost &amp; Found</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">Lost <span>&amp;</span> Found</div>
  <div class="auth-sub">Campus Hub</div>

  <div class="auth-box">
    <h2>Forgot Password?</h2>
    
    <?php if ($success): ?>
      <div class="auth-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="auth-error show"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
      <p style="text-align:center; color:#666; margin-bottom:25px;">
        Enter your registered email and we'll send you a link to reset your password.
      </p>

      <form method="POST">
        <label>Your Registered Email</label>
        <input type="email" name="email" placeholder="your@student.edu.my" 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        
        <button type="submit" class="auth-btn">Send Reset Link</button>
      </form>
    <?php endif; ?>

    <div class="auth-link">
      <a href="login.php">← Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>