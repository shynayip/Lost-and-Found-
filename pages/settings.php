<?php
/* ============================================
   pages/settings.php — Settings Page
   ============================================ */
session_start();
require_once '../db.php';

$activePage = 'settings';
$success = '';
$error   = '';

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

    if ($action === 'delete_account') {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $db = getDB();
            // Delete user's items first
            $stmt = $db->prepare("DELETE FROM items WHERE user_id=?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            // Delete the user
            $stmt2 = $db->prepare("DELETE FROM users WHERE id=?");
            $stmt2->bind_param('i', $userId);
            if ($stmt2->execute()) {
                session_destroy();
                header('Location: register.php');
                exit;
            } else {
                $error = 'Failed to delete account. Please try again.';
            }
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
  <div class="settings-page-title">⚙️ Settings</div>

  <?php if ($success): ?>
    <div class="alert-success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert-error">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- ACCOUNT -->
  <div class="settings-section">
    <div class="settings-section-title">Account</div>
    <div class="settings-card">
      <form method="POST" action="settings.php">
        <input type="hidden" name="action" value="update_profile">
        <label class="settings-field-label">Full Name</label>
        <input type="text" name="name" class="settings-input"
               value="<?= htmlspecialchars($user['name'] ?? '') ?>">
        <label class="settings-field-label">Email</label>
        <input type="email" name="email" class="settings-input"
               value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        <label class="settings-field-label">Student ID</label>
        <input type="text" name="student_id" class="settings-input"
               value="<?= htmlspecialchars($user['student_id'] ?? '') ?>">
        <button type="submit" class="settings-save-btn">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- CHANGE PASSWORD -->
  <div class="settings-section">
    <div class="settings-section-title">Change Password</div>
    <div class="settings-card">
      <form method="POST" action="settings.php">
        <input type="hidden" name="action" value="change_password">
        <label class="settings-field-label">Current Password</label>
        <div class="pw-wrap">
          <input type="password" name="current_password" class="settings-input" placeholder="••••••••">
          <button type="button" class="pw-eye" onclick="togglePw(this)" aria-label="Show password">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <label class="settings-field-label">New Password</label>
        <div class="pw-wrap">
          <input type="password" name="new_password" class="settings-input" placeholder="Min 6 characters">
          <button type="button" class="pw-eye" onclick="togglePw(this)" aria-label="Show password">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <label class="settings-field-label">Confirm New Password</label>
        <div class="pw-wrap">
          <input type="password" name="confirm_password" class="settings-input" placeholder="Repeat new password">
          <button type="button" class="pw-eye" onclick="togglePw(this)" aria-label="Show password">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <button type="submit" class="settings-save-btn">Change Password</button>
      </form>
    </div>
  </div>

  <!-- APPEARANCE -->
  <div class="settings-section">
    <div class="settings-section-title">Appearance</div>
    <div class="settings-row" id="darkModeRow">
      <span class="settings-row-label">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
        </svg>
        Dark Mode
      </span>
      <div class="toggle" id="darkModeToggle"></div>
    </div>
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

  <!-- ACCOUNT ACTIONS -->
  <div class="settings-section">
    <div class="settings-section-title">Account Actions</div>
    <a href="../api/logout.php" class="settings-row">
      <span class="settings-row-label">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Sign Out
      </span>
      <span class="settings-row-arrow">›</span>
    </a>
    <div class="settings-row danger" onclick="document.getElementById('deleteConfirm').classList.add('open')">
      <span class="settings-row-label">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
          <path d="M10 11v6M14 11v6"/>
          <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
        </svg>
        Delete Account
      </span>
      <span class="settings-row-arrow">›</span>
    </div>
  </div>
</div>

<!-- Delete Account Confirm Dialog -->
<div class="confirm-overlay" id="deleteConfirm">
  <div class="confirm-box">
    <h3>Delete Account?</h3>
    <p>This will permanently delete your account and all your posted items. This cannot be undone.</p>
    <div class="confirm-actions">
      <button class="confirm-cancel"
              onclick="document.getElementById('deleteConfirm').classList.remove('open')">
        Cancel
      </button>
      <form method="POST" action="settings.php" style="flex:1;">
        <input type="hidden" name="action" value="delete_account">
        <button type="submit" class="confirm-delete" style="width:100%;">
          Yes, Delete
        </button>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
const EYE_OPEN = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
const EYE_SHUT = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';

function togglePw(btn) {
  const input = btn.closest('.pw-wrap').querySelector('input');
  const isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.querySelector('svg').innerHTML = isHidden ? EYE_SHUT : EYE_OPEN;
}
</script>
