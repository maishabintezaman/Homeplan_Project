<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
require_once __DIR__ . '/../partials/navbar.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Provider Options</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-3">Provider Options</h3>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Architect</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/client/architect_list.php">View</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3">
        <h5>Developer</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/client/developer_list.php">View</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3">
        <h5>Material Provider</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/client/material_provider_list.php">View</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3">
        <h5>Worker Provider</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/client/worker_provider_list.php">View</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3">
        <h5>Interior Designer</h5>
        <a class="btn btn-primary btn-sm" href="/homeplan/client/interior_list.php">View</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>

