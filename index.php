<?php
require_once __DIR__ . '/config/session.php';

// Not logged in => go login
if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

// Logged in => show a simple landing (or redirect dashboard)
$role = $_SESSION['role'] ?? 'client';

switch ($role) {
  case 'admin':
    header("Location: /homeplan/admin/dashboard.php");
    exit;
  case 'client':
    header("Location: /homeplan/client/dashboard.php");
    exit;
  default:
    header("Location: /homeplan/provider/dashboard.php");
    exit;
}


