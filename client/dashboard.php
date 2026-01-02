<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$full_name = $_SESSION['full_name'] ?? 'Client';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Client Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<div class="container py-4" style="max-width: 920px;">
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="mb-1">Client Dashboard</h2>
      <div class="text-muted">Welcome, <?= htmlspecialchars($full_name) ?>.</div>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-1">Browse Properties</h4>
        <div class="text-muted">See available properties & details</div>
      </div>
      <a class="btn btn-primary" href="/homeplan/client/properties.php">View</a>
    </div>
  </div>

  <!-- ✅ NEW: Providers -->
  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-1">Providers</h4>
        <div class="text-muted">Find architects, developers, vendors & more</div>
      </div>
      <a class="btn btn-primary" href="/homeplan/client/providers.php">View</a>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-1">My Requests</h4>
        <div class="text-muted">Track your booking / interest requests</div>
      </div>
      <a class="btn btn-outline-primary" href="/homeplan/client/my_requests.php">View</a>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-1">Notifications</h4>
        <div class="text-muted">See updates and messages</div>
      </div>
      <a class="btn btn-outline-secondary" href="/homeplan/client/notifications.php">View</a>
    </div>
  </div>

</div>
</body>
</html>


