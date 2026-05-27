<?php
session_start();
require_once '../db.php';
require_once 'mail.php';  

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();

        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered. Try logging in.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)); // 6-digit code
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            
            $stmt->bind_param('ssssss', $name, $email, $studentId, $hash, $code, $expires);

            if ($stmt->execute()) {
                $user_id = $db->insert_id;

                // Send verification email
                if (sendVerificationEmail($email, $name, $code)) {
                    $_SESSION['pending_user_id'] = $user_id;
                    $_SESSION['pending_email'] = $email;
                    header('Location: verify.php');
                    exit;
                } else {
                    $error = "Failed to send verification email.";
                }
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — Lost &amp; Found</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">Lost <span>&amp;</span> Found</div>
  <div class="auth-sub">Create your campus account</div>
  <div class="auth-box">
    <h2>Create Account</h2>
    <?php if ($error): ?>
      <div class="auth-error show"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php">
      <label>Full Name *</label>
      <input type="text" name="name" placeholder="e.g. Zara Izzati" required>
      
      <label>Email *</label>
      <input type="email" name="email" placeholder="your@student.edu.my" required>
      
      <label>Student ID</label>
      <input type="text" name="student_id" placeholder="e.g. 2024123456">
      
      <label>Password *</label>
      <input type="password" name="password" placeholder="Min 6 characters" required>
      
      <label>Confirm Password *</label>
      <input type="password" name="confirm" placeholder="Repeat password" required>
      
      <button type="submit" class="auth-btn">Create Account</button>
    </form>
    <div class="auth-link">Already have an account? <a href="login.php">Sign in</a></div>
  </div>
</div>
</body>
</html>