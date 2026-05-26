<?php
/* ============================================
   Lost & Found Campus Hub — db.php
   MySQL Database Connection
   ============================================ */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // change to your MySQL username
define('DB_PASS', '');            // change to your MySQL password
define('DB_NAME', 'lost_found');  // your database name

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
