<?php
session_start();
require_once '../db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save to database
            $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)");
            $stmt->bind_param('sss', $email, $token, $expires);
            $stmt->execute();

            // Send Email
            $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
            
            $subject = "Password Reset - Lost & Found Campus Hub";
            $message = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <p><a href='$reset_link' style='background:#e74c3c;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
            ";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@yourdomain.com" . "\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $success = "Password reset link has been sent to your email!";
            } else {
                $error = "Failed to send email. Please try again later.";
            }
        } else {
            $success = "If your email exists, a reset link has been sent."; // Security: Don't reveal if email exists
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
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
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Enter your email address</label>
      <input type="email" name="email" placeholder="your@student.edu.my" required>
      <button type="submit" class="auth-btn">Send Reset Link</button>
    </form>
    
    <div class="auth-link">
      <a href="login.php">Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>