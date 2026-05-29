<?php

session_start();
require_once '../db.php';
require_once 'mail.php';

$activePage = 'post';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db       = getDB();
    $user_id  = $_SESSION['user_id'] ?? 1;
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
                // Notify all verified users about the new post
                $posterName = $_SESSION['user_name'] ?? 'Someone';
                $allUsers   = $db->query("SELECT name, email FROM users WHERE verified = 1 AND email != ''")->fetch_all(MYSQLI_ASSOC);
                sendNewItemNotification($allUsers, $name, $location, $category, $status, $posterName, $desc);

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

<style>
.post-form-wrap {
  padding: 22px 18px calc(var(--bottom-h) + 22px);
}
.post-form-wrap h2 {
  font-family: 'Playfair Display', serif;
  font-weight: 800;
  font-size: 22px;
  margin-bottom: 20px;
}
.form-field { margin-bottom: 14px; }
.form-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: .8px;
  margin-bottom: 6px;
}
.form-input {
  width: 100%;
  background: var(--bg);
  border: 2px solid var(--border);
  border-radius: 12px;
  padding: 11px 14px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  color: var(--text);
  outline: none;
  transition: border-color .18s;
  appearance: none;
}
.form-input:focus { border-color: var(--accent); }
.form-error {
  background: var(--pink);
  border: 1.5px solid var(--accent2);
  border-radius: 12px;
  padding: 11px 15px;
  font-size: 13px;
  color: var(--accent);
  margin-bottom: 16px;
  font-weight: 500;
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
.form-submit {
  width: 100%;
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 30px;
  padding: 14px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  letter-spacing: 0.3px;
  transition: background .18s, transform .12s;
  box-shadow: 0 4px 16px rgba(196,18,48,0.25);
}
.form-submit:hover { background: #a30e26; transform: translateY(-1px); }
</style>

<div class="post-form-wrap">
  <h2>📋 Post an Item</h2>

  <?php if ($error): ?>
    <div class="form-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="post.php" enctype="multipart/form-data">

    <div class="form-field">
      <label class="form-label">Item Name</label>
      <input type="text" name="name" class="form-input" placeholder="e.g. Blue Water Bottle"
             value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
    </div>

    <div class="form-field">
      <label class="form-label">Category</label>
      <select name="category" class="form-input">
        <option value="bag">🎒 Bag</option>
        <option value="electronics">📱 Electronics</option>
        <option value="keys">🔑 Keys</option>
        <option value="clothing">👕 Clothing</option>
        <option value="other">📦 Other</option>
      </select>
    </div>

    <div class="form-field">
      <label class="form-label">Status</label>
      <select name="status" class="form-input">
        <option value="unresolved">Unresolved</option>
        <option value="found">Found</option>
      </select>
    </div>

    <div class="form-field">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-input" placeholder="e.g. Block C Library"
             value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
    </div>

    <div class="form-grid">
      <div>
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-input"
               value="<?= $_POST['date'] ?? date('Y-m-d') ?>" required>
      </div>
      <div>
        <label class="form-label">Time</label>
        <input type="time" name="time" class="form-input"
               value="<?= $_POST['time'] ?? '' ?>">
      </div>
    </div>

    <div class="form-field">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-input" placeholder="Describe the item…" rows="4"
                style="resize:vertical;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="form-field">
      <label class="form-label">Photo</label>
      <div class="upload-area" onclick="document.getElementById('imageInput').click()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        Tap to upload a photo
        <div id="photoName" style="font-size:12px;color:var(--accent);margin-top:5px;font-weight:600;"></div>
      </div>
      <input type="file" id="imageInput" name="image" accept="image/*" style="display:none"
             onchange="document.getElementById('photoName').textContent = this.files[0]?.name || ''">
    </div>

    <button type="submit" class="form-submit">📤 Post Item</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
