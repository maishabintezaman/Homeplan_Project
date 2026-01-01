<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'property_owner') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

// Owner name
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id=? LIMIT 1");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$full_name = $u['full_name'] ?? 'Property Owner';

// Fetch properties
$stmt = $conn->prepare("
    SELECT property_id, project_name, price, availability_status, created_at
    FROM properties
    WHERE provider_id=?
    ORDER BY property_id DESC
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$props = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Properties</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .brandbar{background:#111827;color:#fff}
    .table thead th{background:#111827;color:#fff}
  </style>
</head>
<body class="bg-light">

<div class="brandbar py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="fs-4 fw-bold">HomePlan</div>
    <div class="d-flex align-items-center gap-3">
      <div class="fw-semibold"><?= htmlspecialchars($full_name) ?></div>
      <span class="badge bg-secondary">PROPERTY OWNER</span>
      <a class="btn btn-sm btn-outline-light" href="/homeplan/auth/logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Properties</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/homeplan/property_owner/dashboard.php">Back</a>
      <a class="btn btn-primary" href="/homeplan/property_owner/requests.php">Requests</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <?php if ($props->num_rows === 0): ?>
        <div class="p-4 text-muted">No properties found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped mb-0 align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Project</th>
                <th class="text-end">Price</th>
                <th>Status</th>
                <th>Posted</th>
              </tr>
            </thead>
            <tbody>
            <?php while($p = $props->fetch_assoc()): ?>
              <?php
                $avail = $p['availability_status'] ?? 'available';
                $badge = ($avail === 'available') ? 'bg-success' : 'bg-secondary';
              ?>
              <tr>
                <td><?= (int)$p['property_id'] ?></td>
                <td><?= htmlspecialchars($p['project_name'] ?? '-') ?></td>
                <td class="text-end"><?= number_format((float)($p['price'] ?? 0), 0) ?></td>
                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($avail) ?></span></td>
                <td><?= htmlspecialchars($p['created_at'] ?? '') ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
