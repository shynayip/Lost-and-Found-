<?php
/* ============================================
   pages/index.php — Home / Main Feed
   ============================================ */
session_start();
require_once '../db.php';

$activePage = 'home';
$db = getDB();

// Build query from filters
$status   = $_GET['status']   ?? 'all';
$category = $_GET['category'] ?? 'all';
$sort     = $_GET['sort']     ?? 'date_desc';
$search   = $_GET['search']   ?? '';

$where = ['1=1'];
$params = [];
$types  = '';

if ($status !== 'all') {
    $where[] = 'i.status = ?';
    $params[] = $status; $types .= 's';
}
if ($category !== 'all') {
    $where[] = 'i.category = ?';
    $params[] = $category; $types .= 's';
}
if ($search !== '') {
    $where[] = '(i.name LIKE ? OR i.location LIKE ?)';
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $types .= 'ss';
}

$orderBy = match($sort) {
    'date_asc'     => 'i.item_date ASC,  i.item_time ASC',
    'location_az'  => 'i.location ASC',
    'location_za'  => 'i.location DESC',
    default        => 'i.item_date DESC, i.item_time DESC',
};

$whereStr = implode(' AND ', $where);
$sql = "SELECT i.*, u.name AS poster_name
        FROM items i
        JOIN users u ON i.user_id = u.id
        WHERE $whereStr
        ORDER BY $orderBy";

$stmt = $db->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$emojiMap = ['bag'=>'🎒','electronics'=>'📱','keys'=>'🔑','clothing'=>'👕','other'=>'📦'];

require_once '../includes/header.php';
?>

<div class="search-bar">
  <form method="GET" action="index.php">
    <div class="search-input-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" name="search" placeholder="Search items, locations…"
             value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="filter-row">
      <?php
      $filters = ['all'=>'All','unresolved'=>'Unresolved','found'=>'Found','returned'=>'Returned',
                  'bag'=>'🎒 Bag','electronics'=>'📱 Electronics','keys'=>'🔑 Keys','clothing'=>'👕 Clothing'];
      foreach ($filters as $val => $label):
        $active = ($status === $val || $category === $val) ? 'active' : '';
        $paramName = in_array($val, ['bag','electronics','keys','clothing']) ? 'category' : 'status';
      ?>
        <button type="submit" name="<?= $paramName ?>" value="<?= $val ?>"
                class="filter-btn <?= $active ?>"><?= $label ?></button>
      <?php endforeach; ?>
    </div>

    <div class="sort-row">
      <span class="sort-label">Sort by:</span>
      <select name="sort" class="sort-select" onchange="this.form.submit()">
        <option value="date_desc"    <?= $sort==='date_desc'   ?'selected':'' ?>>Newest first</option>
        <option value="date_asc"     <?= $sort==='date_asc'    ?'selected':'' ?>>Oldest first</option>
        <option value="location_az"  <?= $sort==='location_az' ?'selected':'' ?>>Location A→Z</option>
        <option value="location_za"  <?= $sort==='location_za' ?'selected':'' ?>>Location Z→A</option>
      </select>
    </div>
  </form>
</div>

<main class="feed-wrap">
<?php if (empty($items)): ?>
  <div class="empty-state">
    <div class="icon">🔍</div>
    <h3>No items found</h3>
    <p>Try adjusting filters or search</p>
  </div>
<?php else: ?>
  <?php foreach ($items as $i => $item):
    $emoji = $emojiMap[$item['category']] ?? '📦';
    $imgSrc = $item['image'] ? "../uploads/{$item['image']}" : null;
  ?>
  <div class="item-card" style="animation-delay:<?= $i*50 ?>ms">
    <?php if ($imgSrc): ?>
      <img class="card-img" src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
    <?php else: ?>
      <div class="card-img-placeholder"><?= $emoji ?></div>
    <?php endif; ?>

    <div class="card-body">
      <div class="card-top">
        <div class="card-title"><?= htmlspecialchars($item['name']) ?></div>
        <span class="status-badge status-<?= $item['status'] ?>">
          <?= ucfirst($item['status']) ?>
        </span>
      </div>

      <div class="card-meta">
        <div class="meta-row">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <circle cx="12" cy="11" r="3"/>
          </svg>
          <strong><?= htmlspecialchars($item['location']) ?></strong>
        </div>
        <div class="meta-row">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <?= date('d M Y', strtotime($item['item_date'])) ?>
          &nbsp;·&nbsp;
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          <?= date('H:i', strtotime($item['item_time'])) ?>
        </div>
        <div class="meta-row">
          Posted by <strong><?= htmlspecialchars($item['poster_name']) ?></strong>
        </div>
      </div>

      <div style="font-size:13px;color:var(--text-muted);margin-top:8px;line-height:1.4;">
        <?= htmlspecialchars($item['description']) ?>
      </div>

      <div class="card-actions">
        <button class="btn-request"
                onclick="openClaimModal(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')">
          📋 Request Claim
        </button>
        <a href="messages.php?item_id=<?= $item['id'] ?>" class="btn-dm">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
          </svg>
          DM
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
</main>

<!-- Claim Modal -->
<div class="modal-overlay" id="claimModal" onclick="closeModalIf(event,'claimModal')">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('claimModal')">×</button>
    <div class="modal-handle"></div>
    <h2 id="claimTitle">Request Claim</h2>
    <p style="font-size:13.5px;color:var(--text-muted);margin-bottom:14px;">
      Describe why this item belongs to you.
    </p>
    <form method="POST" action="../api/claims.php">
      <input type="hidden" name="item_id" id="claimItemId">
      <label>Your Name</label>
      <input type="text" name="name" placeholder="Full name" required>
      <label>Student ID</label>
      <input type="text" name="student_id" placeholder="e.g. 2024123456">
      <label>Proof of Ownership</label>
      <textarea name="proof" placeholder="Describe something only the real owner would know…" required></textarea>
      <button type="submit" class="modal-submit">Send Claim Request</button>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
function openClaimModal(id, name) {
  document.getElementById('claimItemId').value = id;
  document.getElementById('claimTitle').textContent = 'Claim: ' + name;
  openModal('claimModal');
}
</script>
