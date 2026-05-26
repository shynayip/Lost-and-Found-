<?php
/* ============================================
   api/claims.php — Handle Claim Requests
   ============================================ */
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db      = getDB();
    $userId  = $_SESSION['user_id'] ?? 1;
    $itemId  = (int)($_POST['item_id'] ?? 0);
    $proof   = trim($_POST['proof']   ?? '');

    if (!$itemId || !$proof) {
        header('Location: ../pages/index.php?error=missing_fields');
        exit;
    }

    $stmt = $db->prepare("INSERT INTO claims (item_id, user_id, proof) VALUES (?,?,?)");
    $stmt->bind_param('iis', $itemId, $userId, $proof);
    $stmt->execute();

    header('Location: ../pages/index.php?success=claim_sent');
    exit;
}
