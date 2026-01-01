<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$clientId  = (int)$_SESSION['user_id'];
$requestId = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

if ($requestId <= 0) {
  http_response_code(400);
  echo "Invalid request id.";
  exit;
}

// Fetch request ensuring it belongs to this client
$stmt = $pdo->prepare("
  SELECT
    ar.request_id,
    ar.client_user_id,
    ar.architect_user_id,
    ar.project_type,
    ar.location,
    ar.area_sqft,
    ar.budget,
    ar.preferred_date,
    ar.message,
    ar.status,
    ar.created_at,
    u.full_name AS architect_name,
    u.email AS architect_email,
    u.phone AS architect_phone
  FROM architect_requests ar
  JOIN users u ON u.user_id = ar.architect_user_id
  WHERE ar.request_id = ?
    AND ar.client_user_id = ?
  LIMIT 1
");
$stmt->execute([$requestId, $clientId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  http_response_code(404);
  echo "Request not found.";
  exit;
}

function badgeClass2($s) {
  $s = strtolower((string)$s);
  if ($s === 'accepted') return 'bg-success';
  if ($s === 'rejected') return 'bg-danger';
  if ($s === 'pending')  return 'bg-warning text-dark';
  return 'bg-secondary';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architect Request Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Architect Request Details</h3>
    <a class="btn btn-outline-secondary" href="/homeplan/client/my_requests.php">Back</a>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <h5 class="mb-1"><?= htmlspecialchars($row['architect_name'] ?? '') ?></h5>
          <div class="text-muted">
            <?= htmlspecialchars($row['architect_email'] ?? '') ?> |
            <?= htmlspecialchars($row['architect_phone'] ?? '') ?>
          </div>
        </div>
        <div class="text-end">
          <div class="mb-1">
            <span class="badge <?= badgeClass2($row['status'] ?? '') ?>">
              <?= htmlspecialchars($row['status'] ?? '') ?>
            </span>
          </div>
          <div class="small text-muted">
            Requested on: <?= htmlspecialchars($row['created_at'] ?? '') ?>
          </div>
        </div>
      </div>

      <hr>

      <div class="row g-3">
        <div class="col-md-6">
          <div class="fw-semibold">Project Type</div>
          <div><?= htmlspecialchars($row['project_type'] ?? '') ?></div>
        </div>

        <div class="col-md-6">
          <div class="fw-semibold">Location</div>
          <div><?= htmlspecialchars($row['location'] ?? '') ?></div>
        </div>

        <div class="col-md-4">
          <div class="fw-semibold">Area (sqft)</div>
          <div><?= htmlspecialchars((string)($row['area_sqft'] ?? '')) ?></div>
        </div>

        <div class="col-md-4">
          <div class="fw-semibold">Budget (BDT)</div>
          <div>
            <?php if ($row['budget'] !== null && $row['budget'] !== ''): ?>
              ৳ <?= number_format((float)$row['budget']) ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="fw-semibold">Preferred Date</div>
          <div><?= htmlspecialchars((string)($row['preferred_date'] ?? '—')) ?></div>
        </div>

        <div class="col-12">
          <div class="fw-semibold">Message</div>
          <div class="border rounded p-3 bg-white">
            <?= nl2br(htmlspecialchars((string)($row['message'] ?? '—'))) ?>
          </div>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <a class="btn btn-outline-primary"
           href="/homeplan/client/architect_view.php?architect_id=<?= (int)$row['architect_user_id'] ?>">
          View Architect Profile
        </a>
      </div>

    </div>
  </div>

</div>

</body>
</html>
