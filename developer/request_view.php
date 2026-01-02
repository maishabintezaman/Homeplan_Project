<?php
// /homeplan/developer/request_view.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$dev_id = (int)$_SESSION['user_id'];
$request_id = (int)($_GET['request_id'] ?? 0);

if ($request_id <= 0) {
  die("Invalid request id.");
}

$sql = "SELECT r.*,
               u.full_name AS client_name,
               u.email     AS client_email,
               u.phone     AS client_phone
        FROM requests r
        JOIN users u ON u.user_id = r.client_id
        WHERE r.request_id = ?
          AND r.provider_id = ?
          AND r.request_type = 'developer_land'
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $request_id, $dev_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
  die("Request not found or you don't have permission.");
}

function badgeClass($s) {
  $s = strtolower((string)$s);
  if ($s === 'accepted') return 'bg-success';
  if ($s === 'rejected') return 'bg-danger';
  return 'bg-warning text-dark';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request View</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4" style="max-width: 980px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a class="btn btn-outline-dark" href="/homeplan/developer/requests.php">⬅ Back</a>
    <span class="badge <?= badgeClass($row['status'] ?? 'pending') ?>">
      <?= htmlspecialchars($row['status'] ?? 'pending') ?>
    </span>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h5 class="mb-3">Client Information</h5>
      <div><b>Name:</b> <?= htmlspecialchars($row['client_name'] ?? '') ?></div>
      <div><b>Email:</b> <?= htmlspecialchars($row['client_email'] ?? '') ?></div>
      <div><b>Phone:</b> <?= htmlspecialchars($row['client_phone'] ?? '') ?></div>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h5 class="mb-3">Land Details</h5>
      <div class="row g-2 small">
        <div class="col-md-4"><b>Area:</b> <?= htmlspecialchars($row['area_value'] ?? '') ?> <?= htmlspecialchars($row['area_unit'] ?? '') ?></div>
        <div class="col-md-4"><b>Asking Price:</b> <?= htmlspecialchars($row['asking_price'] ?? '') ?></div>
        <div class="col-md-4"><b>Road Width:</b> <?= htmlspecialchars($row['road_width'] ?? '') ?></div>
        <div class="col-md-12"><b>Location:</b> <?= htmlspecialchars($row['location_text'] ?? '') ?></div>
        <div class="col-md-12"><b>Ownership:</b> <?= htmlspecialchars($row['ownership_type'] ?? '') ?></div>
        <div class="col-md-12"><b>Notes:</b><br><?= nl2br(htmlspecialchars($row['notes'] ?? '')) ?></div>
      </div>

      <div class="text-muted small mt-3">
        Submitted: <?= htmlspecialchars($row['creation_date'] ?? '') ?>

      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <?php if (($row['status'] ?? 'pending') === 'pending'): ?>
      <form method="post" action="/homeplan/developer/request_action.php" class="m-0">
        <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
        <input type="hidden" name="action" value="accept">
        <button class="btn btn-success" onclick="return confirm('Accept this request?')">Accept</button>
      </form>

      <form method="post" action="/homeplan/developer/request_action.php" class="m-0">
        <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
        <input type="hidden" name="action" value="reject">
        <button class="btn btn-danger" onclick="return confirm('Reject this request?')">Reject</button>
      </form>
    <?php else: ?>
      <span class="text-muted align-self-center">This request is already <?= htmlspecialchars($row['status']) ?>.</span>
    <?php endif; ?>
  </div>

</div>

</body>
</html>

