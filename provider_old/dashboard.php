<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';


if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'provider') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

// Pending request count (incoming)
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM requests r
    JOIN properties p ON p.property_id = r.property_id
    WHERE r.request_type = 'property'
      AND r.status = 'pending'
      AND p.provider_id = ?
");
$stmt->execute([$provider_id]);
$pendingCount = (int)$stmt->fetchColumn();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Provider Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
  <h3 class="mb-3">Provider Dashboard</h3>

  <div class="row g-3">

    <div class="col-md-4">
      <a class="card p-3 text-decoration-none" href="/homeplan/provider/requests.php">
        <div class="d-flex justify-content-between align-items-center">
          <span>Incoming Requests</span>
          <?php if ($pendingCount > 0): ?>
            <span class="badge bg-danger"><?= $pendingCount ?></span>
          <?php else: ?>
            <span class="badge bg-secondary">0</span>
          <?php endif; ?>
        </div>
        <div class="text-muted small mt-2">View, accept, or reject requests</div>
      </a>
    </div>

    <div class="col-md-4">
      <a class="card p-3 text-decoration-none" href="/homeplan/provider/properties.php">
        <div>My Properties</div>
        <div class="text-muted small mt-2">Manage your listed properties</div>
      </a>
    </div>

    <div class="col-md-4">
      <a class="card p-3 text-decoration-none" href="/homeplan/provider/profile.php">
        <div>My Profile</div>
        <div class="text-muted small mt-2">Update your details</div>
      </a>
    </div>

  </div>
</div>

</body>
</html>


