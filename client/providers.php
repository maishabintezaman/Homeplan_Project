<?php
session_start();
require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/auth/login.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Providers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0">Providers</h3>
    <a href="/homeplan/client/dashboard.php" class="btn btn-outline-dark">Back</a>
  </div>

  <div class="row g-3">

    <!-- ✅ Architects -->
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="fw-bold mb-1">Architects</div>
          <div class="text-muted small mb-3">View architect profiles & request</div>
          <a class="btn btn-primary w-100" href="/homeplan/client/architect_list.php">Open</a>
        </div>
      </div>
    </div>

    <!-- Workers Providers (placeholder link) -->
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="fw-bold mb-1">Workers Providers</div>
          <div class="text-muted small mb-3">Masons, electricians, plumbers etc.</div>
          <a class="btn btn-outline-primary w-100" href="#" onclick="alert('Coming soon'); return false;">Open</a>
        </div>
      </div>
    </div>

    <!-- Material Providers (placeholder link) -->
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="fw-bold mb-1">Material Providers</div>
          <div class="text-muted small mb-3">Cement, rods, tiles, paints etc.</div>
          <a class="btn btn-outline-primary w-100" href="#" onclick="alert('Coming soon'); return false;">Open</a>
        </div>
      </div>
    </div>

    <!-- Interior Company (placeholder link) -->
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="fw-bold mb-1">Interior Company</div>
          <div class="text-muted small mb-3">Interior design & decoration services</div>
          <a class="btn btn-outline-primary w-100" href="#" onclick="alert('Coming soon'); return false;">Open</a>
        </div>
      </div>
    </div>

  </div>

</div>
</body>
</html>
