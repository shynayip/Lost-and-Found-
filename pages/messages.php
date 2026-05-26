<?php
/* ============================================
   pages/messages.php — DM / Messages Page
   ============================================ */
session_start();
require_once '../db.php';

$activePage = 'messages';
$db         = getDB();
$userId     = $_SESSION['user_id'] ?? 1;

// Get all conversations for this user
$stmt = $db->prepare("
  SELECT c.id AS conv_id,
         IF(c.user1_id = ?, c.user2_id, c.user1_id) AS other_id,
         u.name AS other_name,
         i.name AS item_name,
         (SELECT message FROM messages WHERE conversation_id=c.id ORDER BY created_at DESC LIMIT 1) AS last_msg,
         (SELECT created_at FROM messages WHERE conversation_id=c.id ORDER BY created_at DESC LIMIT 1) AS last_time,
         (SELECT COUNT(*) FROM messages WHERE conversation_id=c.id AND sender_id != ? AND is_read=0) AS unread
  FROM conversations c
  JOIN users u ON u.id = IF(c.user1_id=?, c.user2_id, c.user1_id)
  LEFT JOIN items i ON i.id = c.item_id
  WHERE c.user1_id=? OR c.user2_id=?
  ORDER BY last_time DESC
");
$stmt->bind_param('iiiii', $userId, $userId, $userId, $userId, $userId);
$stmt->execute();
$convos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$colors = ['#d44b2a','#2a7dd4','#27a05e','#a020d4','#d4a020','#208090'];

require_once '../includes/header.php';
?>

<div style="padding:18px 16px 10px;border-bottom:1px solid var(--border);background:var(--surface);">
  <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:19px;">Messages</h2>
</div>

<div class="dm-list" style="padding-bottom:calc(var(--bottom-h)+10px);">
<?php if (empty($convos)): ?>
  <div class="empty-state">
    <div class="icon">💬</div>
    <h3>No messages yet</h3>
    <p>Start a conversation from an item listing</p>
  </div>
<?php else: ?>
  <?php foreach ($convos as $idx => $c):
    $initial = strtoupper(substr($c['other_name'], 0, 1));
    $color   = $colors[$idx % count($colors)];
    $timeAgo = $c['last_time'] ? date('d M', strtotime($c['last_time'])) : '';
  ?>
  <a href="chat.php?conv_id=<?= $c['conv_id'] ?>" class="dm-thread" style="text-decoration:none;color:inherit;">
    <div class="dm-avatar" style="background:<?= $color ?>;"><?= $initial ?></div>
    <div class="dm-info">
      <div class="dm-name"><?= htmlspecialchars($c['other_name']) ?></div>
      <div class="dm-preview"><?= htmlspecialchars($c['last_msg'] ?? 'No messages yet') ?></div>
      <?php if ($c['item_name']): ?>
        <div style="font-size:11px;color:var(--accent);margin-top:2px;">
          Re: <?= htmlspecialchars($c['item_name']) ?>
        </div>
      <?php endif; ?>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;">
      <div class="dm-time"><?= $timeAgo ?></div>
      <?php if ($c['unread'] > 0): ?>
        <div class="dm-unread"><?= $c['unread'] ?></div>
      <?php endif; ?>
    </div>
  </a>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
