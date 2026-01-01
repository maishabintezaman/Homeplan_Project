<?php
// /homeplan/property_owner/dashboard.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if (($_SESSION['role'] ?? '') !== 'property_owner') {
  // don't loop back to login; send to home instead
  header("Location: /homeplan/index.php");
  exit;
}

$owner_id = (int)$_SESSION['user_id'];

$total_properties = 0;
$total_requests   = 0;
$pending = 0;
$accepted = 0;
$rejected = 0;

$error = '';

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM properties WHERE provider_id = ?");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $total_properties = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

    $sqlReq = "
        SELECT r.status, COUNT(*) AS c
        FROM requests r
        JOIN properties p ON p.property_id = r.property_id
        WHERE p.provider_id = ?
        GROUP BY r.status
    ";
    $stmt2 = $conn->prepare($sqlReq);
    $stmt2->bind_param("i", $owner_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    $total_requests = 0;
    while ($row = $res2->fetch_assoc()) {
        $count = (int)$row['c'];
        $total_requests += $count;

        $status = strtolower((string)$row['status']);
        if ($status === 'pending')  $pending = $count;
        if ($status === 'accepted') $accepted = $count;
        if ($status === 'rejected') $rejected = $count;
    }

} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Property Owner Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-3">

  <?php if ($error): ?>
    <div class="alert alert-danger"><b>Error:</b> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="d-flex justify-content-end gap-2 mb-3">
    <a class="btn btn-outline-primary" href="/homeplan/property_owner/my_properties.php">My Properties</a>
    <a class="btn btn-outline-primary" href="/homeplan/property_owner/requests.php">Requests</a>
    <a class="btn btn-primary" href="/homeplan/property_owner/add_property.php">Add Property</a>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Total Properties</div>
          <div class="display-6 fw-bold"><?= (int)$total_properties ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Total Requests</div>
          <div class="display-6 fw-bold"><?= (int)$total_requests ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Pending</div>
          <div class="display-6 fw-bold text-warning"><?= (int)$pending ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Accepted</div>
          <div class="display-6 fw-bold text-success"><?= (int)$accepted ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Rejected</div>
          <div class="display-6 fw-bold text-danger"><?= (int)$rejected ?></div>
        </div>
      </div>
    </div>
  </div>

</div>
</body>
</html>

