<?php
/* ============================================
   api/update_status.php — Update Item Status
   ============================================ */
session_start();
require_once '../db.php';
require_once '../pages/mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db      = getDB();
    $userId  = $_SESSION['user_id'] ?? 1;
    $itemId  = (int)($_POST['item_id'] ?? 0);
    $status  = $_POST['status'] ?? '';
    $allowed = ['unresolved', 'found', 'returned'];

    if ($itemId && in_array($status, $allowed)) {
        $stmt = $db->prepare("UPDATE items SET status=? WHERE id=? AND user_id=?");
        $stmt->bind_param('sii', $status, $itemId, $userId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Get item details
            $stmt2 = $db->prepare("SELECT i.name, i.location, u.name AS updater_name
                                   FROM items i JOIN users u ON i.user_id = u.id
                                   WHERE i.id = ?");
            $stmt2->bind_param('i', $itemId);
            $stmt2->execute();
            $item = $stmt2->get_result()->fetch_assoc();

            // Get all verified users with email alerts enabled (all verified users for now)
            $allUsers = $db->query("SELECT name, email FROM users WHERE verified = 1 AND email != ''")->fetch_all(MYSQLI_ASSOC);

            if ($item && !empty($allUsers)) {
                sendItemUpdateNotification(
                    $allUsers,
                    $item['name'],
                    $item['location'],
                    $status,
                    $item['updater_name']
                );
            }
        }
    }

    header('Location: ../pages/profile.php');
    exit;
}
