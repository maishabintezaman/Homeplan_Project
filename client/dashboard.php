<?php
// /homeplan/client/dashboard.php
require_once __DIR__ . '/../config/session.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if (($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/index.php");
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Client Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-3">

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h3 class="mb-1">Client Dashboard</h3>
      <div class="text-muted">
        Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Client') ?>.
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <a class="text-decoration-none" href="/homeplan/client/properties.php">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="fw-semibold">Browse Properties</div>
            <div class="text-muted small">See available properties & details</div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a class="text-decoration-none" href="/homeplan/client/my_requests.php">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="fw-semibold">My Requests</div>
            <div class="text-muted small">Track your booking / interest requests</div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a class="text-decoration-none" href="/homeplan/client/notifications.php">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="fw-semibold">Notifications</div>
            <div class="text-muted small">See updates and messages</div>
          </div>
        </div>
      </a>
    </div>
  </div>

</div>

</body>
</html>

