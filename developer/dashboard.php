<?php

require_once __DIR__ . '/../config/session.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'property_owner') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
require_once __DIR__ . '/../partials/navbar.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Developer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-3">Developer Dashboard</h3>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Projects</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/developer/projects.php">Manage Projects</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Land Requests</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/developer/requests.php">View Requests</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>


