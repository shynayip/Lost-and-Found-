<?php
session_start();
require_once '../db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if ($token) {
    $db = getDB();
    $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();

    if (!$reset) {
        $error = 'Invalid or expired reset link. Please request a new one.';
    } else {
        $email = $reset['email'];
    }
} else {
    $error = 'No reset token provided.';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            // Delete used token
            $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $success = 'Password has been reset successfully!<br>
                        You can now <a href="login.php" style="color:#e74c3c;">Sign In</a> 
                        with your new password.';
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — Lost &amp; Found</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">Lost <span>&amp;</span> Found</div>
  <div class="auth-sub">Campus Hub</div>

  <div class="auth-box">
    <?php if ($error): ?>
      <div class="auth-error show"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="auth-success"><?= $success ?></div>
    <?php else: ?>
      <h2>Set New Password</h2>
      <p style="text-align:center; color:#666; margin-bottom:25px;">
        Please enter and confirm your new password.
      </p>

      <form method="POST">
        <label>New Password</label>
        <input type="password" name="password" placeholder="Enter new password" 
               minlength="6" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm new password" 
               required>

        <button type="submit" class="auth-btn">Reset Password</button>
      </form>
    <?php endif; ?>

    <div class="auth-link" style="margin-top:20px;">
      <a href="login.php">← Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>