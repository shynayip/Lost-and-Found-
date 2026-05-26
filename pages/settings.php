<?php
/* ============================================
   pages/settings.php — Settings Page
   ============================================ */
session_start();
require_once '../db.php';

$activePage = 'settings';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db     = getDB();
    $userId = $_SESSION['user_id'] ?? 1;
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name      = trim($_POST['name']       ?? '');
        $email     = trim($_POST['email']      ?? '');
        $studentId = trim($_POST['student_id'] ?? '');
        $stmt = $db->prepare("UPDATE users SET name=?, email=?, student_id=? WHERE id=?");
        $stmt->bind_param('sssi', $name, $email, $studentId, $userId);
        $stmt->execute();
        $success = 'Profile updated!';
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt2 = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt2->bind_param('si', $hash, $userId);
            $stmt2->execute();
            $success = 'Password changed!';
        }
    }
}

// Load current user
$db     = getDB();
$userId = $_SESSION['user_id'] ?? 1;
$stmt   = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once '../includes/header.php';
?>

<div class="settings-wrap">
  <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:20px;margin-bottom:20px;">
    ⚙️ Settings
  </div>

  <?php if (!empty($success)): ?>
    <div style="background:#d6f5e6;border:1px solid #7edcab;border-radius:8px;
                padding:10px 14px;font-size:13px;color:#1a7a46;margin-bottom:14px;">
      ✅ <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div style="background:#fde8e4;border:1px solid #f0b0a0;border-radius:8px;
                padding:10px 14px;font-size:13px;color:#a02010;margin-bottom:14px;">
      ❌ <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- ACCOUNT -->
  <div class="settings-section">
    <div class="settings-section-title">Account</div>
    <form method="POST" action="settings.php">
      <input type="hidden" name="action" value="update_profile">
      <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:10px;padding:14px;margin-bottom:7px;">
        <label style="display:block;font-size:12px;color:var(--text-muted);text-transform:uppercase;
                      letter-spacing:.5px;margin-bottom:5px;">Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;
                      padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:10px;">
        <label style="display:block;font-size:12px;color:var(--text-muted);text-transform:uppercase;
                      letter-spacing:.5px;margin-bottom:5px;">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;
                      padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:10px;">
        <label style="display:block;font-size:12px;color:var(--text-muted);text-transform:uppercase;
                      letter-spacing:.5px;margin-bottom:5px;">Student ID</label>
        <input type="text" name="student_id" value="<?= htmlspecialchars($user['student_id'] ?? '') ?>"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;
                      padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:12px;">
        <button type="submit"
                style="background:var(--accent);color:#fff;border:none;border-radius:8px;
                       padding:9px 18px;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;cursor:pointer;">
          Save Changes
        </button>
      </div>
    </form>
  </div>

  <!-- CHANGE PASSWORD -->
  <div class="settings-section">
    <div class="settings-section-title">Change Password</div>
    <form method="POST" action="settings.php">
      <input type="hidden" name="action" value="change_password">
      <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:10px;padding:14px;margin-bottom:7px;">
        <label style="display:block;font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Current Password</label>
        <input type="password" name="current_password"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:10px;">
        <label style="display:block;font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">New Password</label>
        <input type="password" name="new_password"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:10px;">
        <label style="display:block;font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Confirm New Password</label>
        <input type="password" name="confirm_password"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:12px;">
        <button type="submit"
                style="background:var(--accent);color:#fff;border:none;border-radius:8px;
                       padding:9px 18px;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;cursor:pointer;">
          Change Password
        </button>
      </div>
    </form>
  </div>

  <!-- NOTIFICATIONS -->
  <div class="settings-section">
    <div class="settings-section-title">Notifications</div>
    <div class="settings-row" onclick="toggleSetting(this)">
      <span class="settings-row-label">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        Push Notifications
      </span>
      <div class="toggle on"></div>
    </div>
    <div class="settings-row" onclick="toggleSetting(this)">
      <span class="settings-row-label">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Email Alerts
      </span>
      <div class="toggle"></div>
    </div>
  </div>

  <!-- SIGN OUT -->
  <div class="settings-section">
    <div class="settings-section-title">Account Actions</div>
    <a href="../api/logout.php" class="settings-row" style="text-decoration:none;color:var(--accent);">
      <span class="settings-row-label" style="color:var(--accent);">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Sign Out
      </span>
    </a>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
