<?php
require_once __DIR__ . '/../config/session.php';

if (($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/auth/login.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Service Providers</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4">

<h3 class="mb-4">Service Providers</h3>

<div class="row g-3">

  <!-- Architects -->
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Architects</h5>
        <p class="text-muted">Licensed architects with portfolio & expertise</p>
        <a href="architect_list.php" class="btn btn-primary btn-sm">View Architects</a>
      </div>
    </div>
  </div>

  <!-- Developers -->
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Developers</h5>
        <p class="text-muted">Property & real-estate developers</p>
        <a href="developer_list.php" class="btn btn-primary btn-sm">View Developers</a>
      </div>
    </div>
  </div>

  <!-- Worker Providers -->
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Worker Providers</h5>
        <p class="text-muted">Masons, plumbers, electricians & labor teams</p>
        <a href="worker_list.php" class="btn btn-primary btn-sm">View Workers</a>
      </div>
    </div>
  </div>

  <!-- Material Providers -->
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Material Providers</h5>
        <p class="text-muted">Cement, rod, bricks & construction materials</p>
        <a href="material_list.php" class="btn btn-primary btn-sm">View Materials</a>
      </div>
    </div>
  </div>

  <!-- Interior Companies -->
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Interior Companies</h5>
        <p class="text-muted">Interior design & finishing services</p>
        <a href="interior_list.php" class="btn btn-primary btn-sm">View Interior Companies</a>
      </div>
    </div>
  </div>

</div>

</div>
</body>
</html>

