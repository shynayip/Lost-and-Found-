<?php
session_start();
require_once '../db.php';

$error = '';

if (!isset($_SESSION['pending_user_id']) || !isset($_SESSION['verification_code'])) {
    header('Location: register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = strtoupper(trim($_POST['code'] ?? ''));

    if (empty($entered_code)) {
        $error = 'Please enter the verification code.';
    } elseif (!isset($_SESSION['code_expires']) || time() > $_SESSION['code_expires']) {
        $error = 'Verification code has expired. Please register again.';
        // Optional: clear session
        session_unset();
    } elseif ($entered_code !== $_SESSION['verification_code']) {
        $error = 'Invalid verification code. Please try again.';
    } else {
        $db = getDB();
        $user_id = $_SESSION['pending_user_id'];

        $stmt = $db->prepare("UPDATE users SET verified = 1 WHERE id = ?");
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            // Clear verification session data
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['pending_email']);
            unset($_SESSION['verification_code']);
            unset($_SESSION['code_expires']);

            // Log the user in
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $_SESSION['pending_email'] ?? '';

            header('Location: profile.php');
            exit;
        } else {
            $error = 'Failed to verify account. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account — Lost & Found</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-logo">Lost <span>&amp;</span> Found</div>
    <div class="auth-sub">Verify your email</div>
    
    <div class="auth-box">
        <h2>Email Verification</h2>
        
        <?php if ($error): ?>
            <div class="auth-error show"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="verify-text">
            We've sent a 6-digit verification code to<br>
            <strong><?= htmlspecialchars($_SESSION['pending_email'] ?? '') ?></strong>
        </p>

        <form method="POST" action="verify.php">
            <label>Verification Code</label>
            <input 
                type="text" 
                name="code" 
                maxlength="6" 
                placeholder="Enter 6-digit code" 
                style="text-transform: uppercase; font-size: 1.1em; letter-spacing: 4px;"
                required 
                autofocus
            >
            
            <button type="submit" class="auth-btn">Verify Account</button>
        </form>

        <div class="auth-link">
            Didn't receive the code? <a href="register.php">Register again</a>
        </div>
    </div>
</div>
</body>
</html>