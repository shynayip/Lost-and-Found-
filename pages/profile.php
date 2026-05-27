<?php
session_start();
require_once '../db.php';

$activePage = 'profile';
$db         = getDB();
$userId     = $_SESSION['user_id'] ?? 1;

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's posted items
$stmt2 = $db->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY item_date DESC");
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$myItems = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// Stats
$posted   = count($myItems);
$claimed  = count(array_filter($myItems, fn($i) => $i['status'] === 'found'));
$returned = count(array_filter($myItems, fn($i) => $i['status'] === 'returned'));

$emojiMap = ['bag'=>'🎒','electronics'=>'📱','keys'=>'🔑','clothing'=>'👕','other'=>'📦'];
$initial  = strtoupper(substr($user['name'] ?? 'U', 0, 1));

require_once '../includes/header.php';
?>

<div class="profile-wrap">
  <div class="profile-header">
    <?php if (!empty($user['avatar'])): ?>
      <img src="../uploads/<?= htmlspecialchars($user['avatar']) ?>"
           style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:12px;">
    <?php else: ?>
      <div class="profile-avatar"><?= $initial ?></div>
    <?php endif; ?>

    <div class="profile-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
    <div class="profile-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
    <?php if (!empty($user['student_id'])): ?>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
        ID: <?= htmlspecialchars($user['student_id']) ?>
      </div>
    <?php endif; ?>

    <div class="profile-stats">
      <div class="stat">
        <div class="stat-num"><?= $posted ?></div>
        <div class="stat-label">Posted</div>
      </div>
      <div class="stat">
        <div class="stat-num"><?= $claimed ?></div>
        <div class="stat-label">Found</div>
      </div>
      <div class="stat">
        <div class="stat-num"><?= $returned ?></div>
        <div class="stat-label">Returned</div>
      </div>
    </div>
  </div>

  <div class="section-title">My Posts</div>

  <?php if (empty($myItems)): ?>
    <div class="empty-state">
      <div class="icon">📭</div>
      <h3>No posts yet</h3>
      <p>Tap the camera button to post an item</p>
    </div>
  <?php else: ?>
    <?php foreach ($myItems as $item):
      $emoji = $emojiMap[$item['category']] ?? '📦';
    ?>
    <div class="post-mini">
      <div class="mini-icon"><?= $emoji ?></div>
      <div style="flex:1;min-width:0;">
        <div class="mini-title"><?= htmlspecialchars($item['name']) ?></div>
        <div class="mini-sub">
          <?= htmlspecialchars($item['location']) ?> ·
          <span class="status-badge status-<?= $item['status'] ?>"
                style="padding:1px 7px;font-size:10px;">
            <?= ucfirst($item['status']) ?>
          </span>
        </div>
      </div>
      <!-- Change status button -->
      <form method="POST" action="../api/update_status.php" style="flex-shrink:0;">
        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
        <select name="status" onchange="this.form.submit()"
                style="font-size:11px;padding:3px 6px;border:1px solid var(--border);
                       border-radius:6px;background:var(--bg);cursor:pointer;">
          <option value="unresolved" <?= $item['status']==='unresolved'?'selected':'' ?>>Unresolved</option>
          <option value="found"      <?= $item['status']==='found'     ?'selected':'' ?>>Found</option>
          <option value="returned"   <?= $item['status']==='returned'  ?'selected':'' ?>>Returned</option>
        </select>
      </form>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
