<?php
session_start();

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$architectId = (int)($_GET['architect_id'] ?? $_POST['architect_id'] ?? 0);

if ($architectId <= 0) {
  header("Location: /homeplan/client/architect_list.php");
  exit;
}

// Go to profile page first (qualifications + portfolio)
header("Location: /homeplan/client/architect_view.php?architect_id=" . $architectId);
exit;

