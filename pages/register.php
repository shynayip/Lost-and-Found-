<?php
/* ============================================
   pages/register.php — Register Page
   ============================================ */
session_start();
require_once '../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $studentId = trim($_POST['student_id']?? '');
    $password  = $_POST['password']       ?? '';
    $confirm   = $_POST['confirm']        ?? '';
    $db        = getDB();

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, student_id, password) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $name, $email, $studentId, $hash);
        if ($stmt->execute()) {
            $_SESSION['user_id']   = $db->insert_id;
            $_SESSION['user_name'] = $name;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email already registered. Try logging in.';
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
      <input type="text" name="name" placeholder="e.g. Zara Izzati"
             value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      <label>Email *</label>
      <input type="email" name="email" placeholder="your@student.edu.my"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      <label>Student ID</label>
      <input type="text" name="student_id" placeholder="e.g. 2024123456"
             value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
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
