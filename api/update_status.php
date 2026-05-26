<?php
/* ============================================
   api/update_status.php — Update Item Status
   ============================================ */
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db      = getDB();
    $userId  = $_SESSION['user_id'] ?? 1;
    $itemId  = (int)($_POST['item_id'] ?? 0);
    $status  = $_POST['status'] ?? '';
    $allowed = ['unresolved','found','returned'];

    if ($itemId && in_array($status, $allowed)) {
        $stmt = $db->prepare("UPDATE items SET status=? WHERE id=? AND user_id=?");
        $stmt->bind_param('sii', $status, $itemId, $userId);
        $stmt->execute();
    }
    header('Location: ../pages/profile.php');
    exit;
}
