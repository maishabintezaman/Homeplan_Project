<?php
// /homeplan/architect/dashboard.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if (($_SESSION['role'] ?? '') !== 'architect') {
  // IMPORTANT: don't redirect back to login (loop risk)
  header("Location: /homeplan/index.php");
  exit;
}

$architect_id = (int)$_SESSION['user_id'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architect Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-3">

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h3 class="mb-1">Architect Dashboard</h3>
      <div class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Architect') ?>.</div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="/homeplan/architect/requests.php">My Requests</a>
    <a class="btn btn-outline-secondary" href="/homeplan/architect/architect_list.php">Architect List</a>
  </div>

</div>

</body>
</html>

