<?php
session_start();
require_once '../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $db       = getDB();
    $stmt     = $db->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Lost &amp; Found</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">Lost <span>&amp;</span> Found</div>
  <div class="auth-sub">Campus Hub — Sign in to continue</div>

  <div class="auth-box">
    <h2>Welcome back 👋</h2>
    <?php if ($error): ?>
      <div class="auth-error show"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
      <label>Email</label>
      <input type="email" name="email" placeholder="your@student.edu.my"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>
      
      <div class="forgot-password">
        <a href="forgotpassword.php">Forgot Password?</a>
      </div>
      
      <button type="submit" class="auth-btn">Sign In</button>
    </form>
    
    <div class="auth-link">
      No account? <a href="register.php">Register here</a>
    </div>
  </div>
</div>
</body>
</html>