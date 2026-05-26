<?php
/* ============================================
   pages/archive.php — Calendar Archive View
   ============================================ */
session_start();
require_once '../db.php';

$activePage = 'archive';
$darkPage   = true; // dark theme for this page

$db        = getDB();
$calFilter = $_GET['filter'] ?? 'all';

$where  = ['1=1'];
$params = [];
$types  = '';
if ($calFilter !== 'all') {
    $where[]  = 'status = ?';
    $params[] = $calFilter;
    $types   .= 's';
}
$whereStr = implode(' AND ', $where);
$sql  = "SELECT id, name, category, status, location, item_date, item_time
         FROM items WHERE $whereStr ORDER BY item_date ASC, item_time ASC";
$stmt = $db->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Group items by date
$byDate = [];
foreach ($rows as $row) {
    $byDate[$row['item_date']][] = $row;
}

$emojiMap = ['bag'=>'🎒','electronics'=>'📱','keys'=>'🔑','clothing'=>'👕','other'=>'📦'];
$MONTHS   = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
$DAYS     = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

// Find range of months to show
$allDates  = array_keys($byDate);
$firstDate = !empty($allDates) ? new DateTime($allDates[0])          : new DateTime();
$lastDate  = !empty($allDates) ? new DateTime($allDates[count($allDates)-1]) : new DateTime();
$nowDate   = new DateTime();
if ($lastDate < $nowDate) $lastDate = $nowDate;
$firstDate->modify('first day of this month');

require_once '../includes/header.php';
?>

<div class="cal-header">
  <div class="cal-header-title">📅 Archive by Date</div>
  <div class="cal-view-tabs">
    <?php
    $tabs = ['all'=>'All','unresolved'=>'Unresolved','found'=>'Found','returned'=>'Returned'];
    foreach ($tabs as $val => $label):
    ?>
      <a href="archive.php?filter=<?= $val ?>"
         class="cal-tab <?= $calFilter===$val ? 'active' : '' ?>">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<div id="calendarBody">
<?php
$cur = clone $firstDate;
while ($cur <= $lastDate):
    $year  = (int)$cur->format('Y');
    $month = (int)$cur->format('n') - 1; // 0-indexed
    $daysInMonth = (int)$cur->format('t');
    // Day of week of 1st (Mon=0)
    $startDow = ((int)(new DateTime("{$year}-".($month+1)."-01"))->format('N')) - 1;
?>
  <div class="cal-month-block">
    <div class="cal-month-label"><?= $MONTHS[$month] ?> <?= $year ?></div>
    <div class="cal-dow-row">
      <?php foreach ($DAYS as $d): ?><div class="cal-dow"><?= $d ?></div><?php endforeach; ?>
    </div>
    <div class="cal-grid">
      <?php
      // Empty leading cells
      for ($e = 0; $e < $startDow; $e++):
      ?><div class="cal-cell cal-empty"></div><?php endfor; ?>

      <?php for ($day = 1; $day <= $daysInMonth; $day++):
        $ds       = sprintf('%04d-%02d-%02d', $year, $month+1, $day);
        $dayItems = $byDate[$ds] ?? [];
        $hasItem  = !empty($dayItems);
        $first    = $hasItem ? $dayItems[0] : null;
        $multi    = count($dayItems) > 1;
        $emoji    = $first ? ($emojiMap[$first['category']] ?? '📦') : '';
      ?>
        <div class="cal-cell <?= $hasItem ? 'has-item' : '' ?>"
             <?= $hasItem ? "onclick=\"openDayDetail('$ds')\"" : '' ?>>
          <div class="cal-day-num"><?= $day ?></div>
          <div class="cal-circle <?= $hasItem ? 'status-'.$first['status'] : '' ?>">
            <?= $hasItem ? $emoji : '' ?>
            <?php if ($multi): ?>
              <div class="cal-multi-badge"><?= count($dayItems) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
<?php
    $cur->modify('first day of next month');
endwhile;
?>
</div>

<!-- Day Detail Panel -->
<div class="day-detail-overlay" id="dayDetailOverlay"
     onclick="if(event.target===this) this.classList.remove('open')">
  <div class="day-detail">
    <button class="day-detail-close"
            onclick="document.getElementById('dayDetailOverlay').classList.remove('open')">×</button>
    <div class="day-detail-handle"></div>
    <div class="day-detail-date" id="dayDetailDate"></div>
    <div id="dayDetailItems"></div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// All items grouped by date (passed from PHP)
const byDate = <?= json_encode($byDate) ?>;
const emojiMap = {bag:'🎒',electronics:'📱',keys:'🔑',clothing:'👕',other:'📦'};

function openDayDetail(ds) {
  const items = byDate[ds] || [];
  const dt    = new Date(ds + 'T00:00:00');
  document.getElementById('dayDetailDate').textContent =
    dt.toLocaleDateString('en-MY', {weekday:'long',day:'numeric',month:'long',year:'numeric'});

  document.getElementById('dayDetailItems').innerHTML = items.map(item => `
    <div class="day-item-row" onclick="window.location='index.php?search=${encodeURIComponent(item.name)}'">
      <div class="day-item-emoji">${emojiMap[item.category] || '📦'}</div>
      <div class="day-item-info">
        <div class="day-item-name">${item.name}</div>
        <div class="day-item-loc">📍 ${item.location}</div>
        <span class="day-item-badge status-${item.status}">
          ${item.status.charAt(0).toUpperCase()+item.status.slice(1)}
        </span>
      </div>
      <div class="day-item-time">${item.item_time.slice(0,5)}</div>
    </div>`).join('');

  document.getElementById('dayDetailOverlay').classList.add('open');
}
</script>
