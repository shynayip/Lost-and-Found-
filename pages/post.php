<?php
/* ============================================
   pages/post.php — Post a New Item
   ============================================ */
session_start();
require_once '../db.php';

$activePage = 'post';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db       = getDB();
    $user_id  = $_SESSION['user_id'] ?? 1; // replace with real session
    $name     = trim($_POST['name']     ?? '');
    $category = $_POST['category']      ?? 'other';
    $status   = $_POST['status']        ?? 'unresolved';
    $location = trim($_POST['location'] ?? '');
    $date     = $_POST['date']          ?? '';
    $time     = $_POST['time']          ?? '00:00';
    $desc     = trim($_POST['description'] ?? '');
    $image    = null;

    if (!$name || !$location || !$date) {
        $error = 'Please fill in name, location and date.';
    } else {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg','jpeg','png','webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, WEBP images allowed.';
            } else {
                $filename = uniqid('item_') . '.' . $ext;
                $dest     = "../uploads/$filename";
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image = $filename;
                } else {
                    $error = 'Image upload failed. Check uploads/ folder permissions.';
                }
            }
        }

        if (!$error) {
            $stmt = $db->prepare(
                "INSERT INTO items (user_id, name, category, status, location, item_date, item_time, description, image)
                 VALUES (?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param('issssssss', $user_id, $name, $category, $status, $location, $date, $time, $desc, $image);
            if ($stmt->execute()) {
                header('Location: index.php?success=1');
                exit;
            } else {
                $error = 'Failed to save item. Please try again.';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div style="padding:20px 18px calc(var(--bottom-h)+20px);">
  <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:20px;margin-bottom:18px;">
    📋 Post an Item
  </h2>

  <?php if ($error): ?>
    <div style="background:#fde8e4;border:1px solid #f0b0a0;border-radius:8px;padding:10px 14px;
                font-size:13px;color:#a02010;margin-bottom:14px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="post.php" enctype="multipart/form-data">

    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Item Name</label>
    <input type="text" name="name" placeholder="e.g. Blue Water Bottle"
           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
           style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                  padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:12px;" required>

    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Category</label>
    <select name="category"
            style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                   padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:12px;">
      <option value="bag">🎒 Bag</option>
      <option value="electronics">📱 Electronics</option>
      <option value="keys">🔑 Keys</option>
      <option value="clothing">👕 Clothing</option>
      <option value="other">📦 Other</option>
    </select>

    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Status</label>
    <select name="status"
            style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                   padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:12px;">
      <option value="unresolved">Unresolved</option>
      <option value="found">Found</option>
    </select>

    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Location</label>
    <input type="text" name="location" placeholder="e.g. Block C Library"
           value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
           style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                  padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;margin-bottom:12px;" required>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                      text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Date</label>
        <input type="date" name="date" value="<?= $_POST['date'] ?? date('Y-m-d') ?>"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                      padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;" required>
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                      text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Time</label>
        <input type="time" name="time" value="<?= $_POST['time'] ?? '' ?>"
               style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                      padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;">
      </div>
    </div>

    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Description</label>
    <textarea name="description" placeholder="Describe the item…" rows="4"
              style="width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:9px;
                     padding:10px 13px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;
                     resize:vertical;margin-bottom:12px;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Photo</label>
    <div class="upload-area" onclick="document.getElementById('imageInput').click()" style="margin-bottom:20px;">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/>
        <line x1="12" y1="3" x2="12" y2="15"/>
      </svg>
      Tap to upload a photo
      <div id="photoName" style="font-size:12px;color:var(--accent);margin-top:4px;"></div>
    </div>
    <input type="file" id="imageInput" name="image" accept="image/*" style="display:none"
           onchange="document.getElementById('photoName').textContent = this.files[0]?.name || ''">

    <button type="submit"
            style="width:100%;background:var(--accent);color:#fff;border:none;border-radius:10px;
                   padding:14px;font-family:'Syne',sans-serif;font-weight:700;font-size:15px;cursor:pointer;">
      📤 Post Item
    </button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
