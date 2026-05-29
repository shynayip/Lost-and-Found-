<?php
require __DIR__ . '/PHPMailer-7.1.1/src/Exception.php';
require __DIR__ . '/PHPMailer-7.1.1/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-7.1.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($email, $name, $reset_token) {
    $mail = new PHPMailer(true);
    
    try {
        // Turn off debug for production
        $mail->SMTPDebug = 0;        // Changed from 2 to 0
        $mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shynayip913@gmail.com';
        $mail->Password   = 'ixkdtvebvrcfnclr';   // ← Keep your app password

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shynayip913@gmail.com', 'Lost and Found Campus Hub');
        $mail->addAddress($email, $name ?? 'User');

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - Lost & Found Campus Hub';

        $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") 
                    . $_SERVER['HTTP_HOST'] 
                    . "/pages/resetpassword.php?token=" 
                    . urlencode($reset_token);

        $mail->Body = "
        <h2>Password Reset Request</h2>
        <p>Hello {$name},</p>
        <p>You requested to reset your password. Click the button below:</p>
        <p style='margin: 25px 0;'>
            <a href='{$reset_link}' style='background:#e74c3c; color:white; padding:14px 28px; text-decoration:none; border-radius:5px; font-weight:bold;'>
                Reset My Password
            </a>
        </p>
        <p>This link will expire in 1 hour.</p>
        <p>If you did not request this, please ignore this email.</p>";

        $mail->AltBody = "Reset link: {$reset_link}\n\nExpires in 1 hour.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
function sendVerificationEmail($email, $name, $code) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shynayip913@gmail.com';
        $mail->Password   = 'ixkdtvebvrcfnclr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shynayip913@gmail.com', 'Lost and Found Campus Hub');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Lost & Found Campus Hub';

        $mail->Body = "
        <h2>Email Verification</h2>
        <p>Hello {$name},</p>
        <p>Your verification code is:</p>
        <h1 style='font-size:42px; letter-spacing:8px; color:#e74c3c;'>{$code}</h1>
        <p>This code will expire in 30 minutes.</p>
        <p>If you did not request this, please ignore this email.</p>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Verification Email Error: " . $e->getMessage());
        return false;
    }
}

function sendNewItemNotification($allUsers, $itemName, $itemLocation, $category, $status, $posterName, $description) {
    if (empty($allUsers)) return;

    $statusLabel = ucfirst($status);
    $statusColor = $status === 'found' ? '#27a05e' : '#e8a020';
    $statusBg    = $status === 'found' ? '#d6f5e6' : '#fff3cd';

    $emojiMap = ['bag'=>'🎒','electronics'=>'📱','keys'=>'🔑','clothing'=>'👕','other'=>'📦'];
    $emoji = $emojiMap[$category] ?? '📦';
    $desc  = htmlspecialchars(mb_substr($description, 0, 120)) . (strlen($description) > 120 ? '…' : '');

    foreach ($allUsers as $user) {
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'shynayip913@gmail.com';
            $mail->Password   = 'ixkdtvebvrcfnclr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('shynayip913@gmail.com', 'Lost and Found Campus Hub');
            $mail->addAddress($user['email'], $user['name']);

            $mail->isHTML(true);
            $mail->Subject = "{$emoji} New Item Posted: \"{$itemName}\"";

            $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:0 auto;'>
              <div style='background:#c41230;padding:24px;border-radius:12px 12px 0 0;text-align:center;'>
                <h1 style='color:#fff;margin:0;font-size:22px;'>Lost &amp; Found</h1>
                <p style='color:#f9d0d0;margin:6px 0 0;font-size:13px;'>Campus Hub — New Item Posted</p>
              </div>
              <div style='background:#fffdf8;border:1px solid #e8d5c0;border-top:none;border-radius:0 0 12px 12px;padding:28px 24px;'>
                <p style='color:#1a0a08;font-size:15px;'>Hello <strong>{$user['name']}</strong>,</p>
                <p style='color:#5a4a40;font-size:14px;'>A new item has been posted on the Lost &amp; Found board:</p>

                <div style='background:#faf4ec;border:1.5px solid #e8d5c0;border-radius:10px;padding:16px 20px;margin:18px 0;'>
                  <p style='margin:0 0 4px;font-size:22px;'>{$emoji}</p>
                  <p style='margin:0 0 6px;font-size:17px;font-weight:700;color:#1a0a08;'>{$itemName}</p>
                  <p style='margin:0 0 10px;font-size:13px;color:#8a6a58;'>📍 {$itemLocation}</p>
                  " . ($desc ? "<p style='margin:0 0 12px;font-size:13px;color:#5a4a40;'>{$desc}</p>" : "") . "
                  <span style='background:{$statusBg};color:{$statusColor};border-radius:20px;
                               padding:4px 14px;font-size:12px;font-weight:700;'>
                    {$statusLabel}
                  </span>
                </div>

                <p style='color:#8a6a58;font-size:13px;'>Posted by <strong>{$posterName}</strong></p>
                <p style='margin-top:22px;'>
                  <a href='http://localhost/Lost-and-Found-/pages/index.php'
                     style='background:#c41230;color:#fff;padding:12px 26px;text-decoration:none;
                            border-radius:30px;font-weight:700;font-size:14px;'>
                    View on Campus Hub
                  </a>
                </p>
                <hr style='border:none;border-top:1px solid #e8d5c0;margin:22px 0;'>
                <p style='color:#aaa;font-size:11px;'>
                  You received this because you have an account on Lost &amp; Found Campus Hub.
                </p>
              </div>
            </div>";

            $mail->AltBody = "New item \"{$itemName}\" posted at {$itemLocation} by {$posterName}. Status: {$statusLabel}.";
            $mail->send();

        } catch (Exception $e) {
            error_log("New item notification failed for {$user['email']}: " . $e->getMessage());
        }
    }
}

function sendItemUpdateNotification($allUsers, $itemName, $itemLocation, $newStatus, $updaterName) {
    if (empty($allUsers)) return;

    $statusLabel = ucfirst($newStatus);
    $statusColor = match($newStatus) {
        'found'     => '#27a05e',
        'returned'  => '#2a7dd4',
        default     => '#e8a020',
    };
    $statusBg = match($newStatus) {
        'found'     => '#d6f5e6',
        'returned'  => '#dce8ff',
        default     => '#fff3cd',
    };

    foreach ($allUsers as $user) {
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'shynayip913@gmail.com';
            $mail->Password   = 'ixkdtvebvrcfnclr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('shynayip913@gmail.com', 'Lost and Found Campus Hub');
            $mail->addAddress($user['email'], $user['name']);

            $mail->isHTML(true);
            $mail->Subject = "Item Update: \"{$itemName}\" is now {$statusLabel}";

            $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:0 auto;'>
              <div style='background:#c41230;padding:24px;border-radius:12px 12px 0 0;text-align:center;'>
                <h1 style='color:#fff;margin:0;font-size:22px;'>Lost &amp; Found</h1>
                <p style='color:#f9d0d0;margin:6px 0 0;font-size:13px;'>Campus Hub — Item Update</p>
              </div>
              <div style='background:#fffdf8;border:1px solid #e8d5c0;border-top:none;border-radius:0 0 12px 12px;padding:28px 24px;'>
                <p style='color:#1a0a08;font-size:15px;'>Hello <strong>{$user['name']}</strong>,</p>
                <p style='color:#5a4a40;font-size:14px;'>An item on the Lost &amp; Found board has been updated:</p>

                <div style='background:#faf4ec;border:1.5px solid #e8d5c0;border-radius:10px;padding:16px 20px;margin:18px 0;'>
                  <p style='margin:0 0 6px;font-size:16px;font-weight:700;color:#1a0a08;'>{$itemName}</p>
                  <p style='margin:0 0 10px;font-size:13px;color:#8a6a58;'>📍 {$itemLocation}</p>
                  <span style='background:{$statusBg};color:{$statusColor};border-radius:20px;
                               padding:4px 14px;font-size:12px;font-weight:700;'>
                    {$statusLabel}
                  </span>
                </div>

                <p style='color:#8a6a58;font-size:13px;'>Updated by <strong>{$updaterName}</strong></p>
                <p style='margin-top:22px;'>
                  <a href='http://localhost/Lost-and-Found-/pages/index.php'
                     style='background:#c41230;color:#fff;padding:12px 26px;text-decoration:none;
                            border-radius:30px;font-weight:700;font-size:14px;'>
                    View on Campus Hub
                  </a>
                </p>
                <hr style='border:none;border-top:1px solid #e8d5c0;margin:22px 0;'>
                <p style='color:#aaa;font-size:11px;'>
                  You received this because you have an account on Lost &amp; Found Campus Hub.
                </p>
              </div>
            </div>";

            $mail->AltBody = "Item \"{$itemName}\" at {$itemLocation} is now {$statusLabel}. Updated by {$updaterName}.";
            $mail->send();

        } catch (Exception $e) {
            error_log("Item update notification failed for {$user['email']}: " . $e->getMessage());
        }
    }
}
?>