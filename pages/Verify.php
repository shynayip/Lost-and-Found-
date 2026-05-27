<?php
session_start();
require_once '../db.php';

// Redirect if there is no pending session
if (!isset($_SESSION['pending_user_id'])) {
    header('Location: register.php');
    exit;
}

$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_code = trim($_POST['verification_code'] ?? '');
    $user_id = $_SESSION['pending_user_id'];

    // Retrieve the code and expiration from DB to validate
    $stmt = $db->prepare("SELECT verification_code, expires_at FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Validate the code and ensure it hasn't expired
    if ($user && $input_code === $user['verification_code']) {
        if (strtotime($user['expires_at']) > time()) {
            
            // Verification successful: Establish session
            $_SESSION['user_id'] = $user_id;
            
            // Clean up temporary registration session data
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['pending_email']);
            
            // Send user to their profile
            header('Location: profile.php');
            exit;
        } else {
            $error = 'The verification code has expired. Please register again.';
        }
    } else {
        $error = 'Invalid verification code.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email — Lost & Found</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <h2>Verify Your Email</h2>
        <p>Please enter the 6-digit code sent to your email.</p>
        
        <?php if ($error): ?>
            <div class="auth-error show"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="verification_code" maxlength="6" placeholder="000000" required>
            <button type="submit" class="auth-btn">Verify Account</button>
        </form>
    </div>
</div>
</body>
</html>