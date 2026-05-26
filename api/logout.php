<?php
/* ============================================
   api/logout.php — Logout
   ============================================ */
session_start();
session_destroy();
header('Location: ../pages/login.php');
exit;
