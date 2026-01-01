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

// Optional filter: status
$allowed = ['all','pending','accepted','rejected'];
$statusFilter = $_GET['status'] ?? 'all';
if (!in_array($statusFilter, $allowed, true)) $statusFilter = 'all';

$sql = "
    SELECT
      r.request_id, r.client_id, r.status, r.creation_date,
      u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone,
      p.property_id, p.project_name, p.price
    FROM requests r
    INNER JOIN properties p ON p.property_id = r.property_id
    LEFT JOIN users u ON u.user_id = r.client_id
    WHERE p.provider_id = ?
";

if ($statusFilter !== 'all') {
    $sql .= " AND r.status = ? ";
}

$sql .= " ORDER BY r.request_id DESC";

$stmt = $conn->prepare($sql);

if ($statusFilter === 'all') {
    $stmt->bind_param("i", $provider_id);
} else {
    $stmt->bind_param("is", $provider_id, $statusFilter);
}

$stmt->execute();
$list = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Requests</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .brandbar{background:#111827;color:#fff}
    .table thead th{background:#111827;color:#fff}
    .status-badge{font-weight:700}
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
    <h3 class="mb-0">Requests</h3>
    <a class="btn btn-outline-secondary" href="/homeplan/property_owner/dashboard.php">Back</a>
  </div>

  <div class="d-flex gap-2 mb-3">
    <a class="btn btn-sm <?= $statusFilter==='all'?'btn-primary':'btn-outline-primary' ?>"
       href="?status=all">All</a>
    <a class="btn btn-sm <?= $statusFilter==='pending'?'btn-warning':'btn-outline-warning' ?>"
       href="?status=pending">Pending</a>
    <a class="btn btn-sm <?= $statusFilter==='accepted'?'btn-success':'btn-outline-success' ?>"
       href="?status=accepted">Accepted</a>
    <a class="btn btn-sm <?= $statusFilter==='rejected'?'btn-danger':'btn-outline-danger' ?>"
       href="?status=rejected">Rejected</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <?php if ($list->num_rows === 0): ?>
        <div class="p-4 text-muted">No requests found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped mb-0 align-middle">
            <thead>
              <tr>
                <th>Req ID</th>
                <th>Client</th>
                <th>Project</th>
                <th class="text-end">Price</th>
                <th>Status</th>
                <th>Requested At</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while($r = $list->fetch_assoc()): ?>
                <?php
                  $status = $r['status'] ?? 'pending';
                  $badge = 'bg-warning';
                  if ($status === 'accepted') $badge = 'bg-success';
                  if ($status === 'rejected') $badge = 'bg-danger';
                ?>
                <tr>
                  <td><?= (int)$r['request_id'] ?></td>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($r['client_name'] ?? ('Client#'.(int)$r['client_id'])) ?></div>
                    <div class="small text-muted">
                      <?= htmlspecialchars($r['client_email'] ?? '') ?>
                      <?= $r['client_phone'] ? ' | ' . htmlspecialchars($r['client_phone']) : '' ?>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($r['project_name'] ?? '-') ?> <span class="text-muted small">(ID: <?= (int)$r['property_id'] ?>)</span></td>
                  <td class="text-end"><?= number_format((float)($r['price'] ?? 0), 0) ?></td>
                  <td><span class="badge <?= $badge ?> status-badge"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
                  <td><?= htmlspecialchars($r['creation_date'] ?? '') ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="/homeplan/property_owner/request_view.php?id=<?= (int)$r['request_id'] ?>">
                      View
                    </a>
                  </td>
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
