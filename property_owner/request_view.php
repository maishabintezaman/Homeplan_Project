<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'property_owner') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];
$request_id  = (int)($_GET['id'] ?? 0);

if ($request_id <= 0) {
    header("Location: /homeplan/property_owner/requests.php");
    exit;
}

// Owner name
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id=? LIMIT 1");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$full_name = $u['full_name'] ?? 'Property Owner';

// Load request (only if belongs to this owner)
$stmt = $conn->prepare("
    SELECT
      r.request_id, r.client_id, r.status, r.creation_date,
      u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone,
      p.property_id, p.project_name, p.price, p.availability_status
    FROM requests r
    INNER JOIN properties p ON p.property_id = r.property_id
    LEFT JOIN users u ON u.user_id = r.client_id
    WHERE r.request_id = ? AND p.provider_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $request_id, $provider_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header("Location: /homeplan/property_owner/requests.php?err=" . urlencode("Request not found"));
    exit;
}

$status = $row['status'] ?? 'pending';
$badge = 'bg-warning';
if ($status === 'accepted') $badge = 'bg-success';
if ($status === 'rejected') $badge = 'bg-danger';

$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request View</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .brandbar{background:#111827;color:#fff}
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
    <h3 class="mb-0">Request #<?= (int)$row['request_id'] ?></h3>
    <a class="btn btn-outline-secondary" href="/homeplan/property_owner/requests.php">Back</a>
  </div>

  <?php if ($msg === 'accepted'): ?>
    <div class="alert alert-success">Request accepted successfully.</div>
  <?php elseif ($msg === 'rejected'): ?>
    <div class="alert alert-danger">Request rejected successfully.</div>
  <?php elseif ($msg === 'already_done'): ?>
    <div class="alert alert-warning">This request was already processed.</div>
  <?php endif; ?>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="text-muted">Client</div>
          <div class="fw-semibold"><?= htmlspecialchars($row['client_name'] ?? ('Client#'.(int)$row['client_id'])) ?></div>
          <div class="small text-muted">
            <?= htmlspecialchars($row['client_email'] ?? '') ?>
            <?= $row['client_phone'] ? ' | ' . htmlspecialchars($row['client_phone']) : '' ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="text-muted">Property</div>
          <div class="fw-semibold"><?= htmlspecialchars($row['project_name'] ?? '-') ?></div>
          <div class="small text-muted">
            Property ID: <?= (int)$row['property_id'] ?> |
            Availability: <?= htmlspecialchars($row['availability_status'] ?? '-') ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="text-muted">Request Info</div>
          <div class="fw-semibold">Price: <?= number_format((float)($row['price'] ?? 0), 0) ?></div>
          <div class="small text-muted">Requested at: <?= htmlspecialchars($row['creation_date'] ?? '') ?></div>
          <div class="mt-2">
            <span class="badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
          </div>
        </div>
      </div>

      <?php if ($status === 'pending'): ?>
        <hr class="my-4">
        <div class="d-flex gap-2">
          <form method="post" action="/homeplan/property_owner/request_action.php">
            <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
            <input type="hidden" name="action" value="accept">
            <button class="btn btn-success" onclick="return confirm('Accept this request?')">Accept</button>
          </form>

          <form method="post" action="/homeplan/property_owner/request_action.php">
            <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
            <input type="hidden" name="action" value="reject">
            <button class="btn btn-danger" onclick="return confirm('Reject this request?')">Reject</button>
          </form>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>
</body>
</html>
