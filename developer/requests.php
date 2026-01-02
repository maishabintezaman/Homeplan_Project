<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

$dev_id = (int)$_SESSION['user_id'];

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function showVal($v) {
  if ($v === null) return '—';
  $s = trim((string)$v);
  return ($s === '') ? '—' : h($s);
}

/* Handle accept/reject (ONLY if pending) */
if (!empty($_GET['action']) && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $action = $_GET['action'];

  if (in_array($action, ['accept','reject'], true)) {
    $newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

    $sql = "UPDATE requests
            SET status=?
            WHERE request_id=?
              AND provider_id=?
              AND request_type='developer_land'
              AND status='pending'";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $newStatus, $id, $dev_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  header("Location: /homeplan/developer/requests.php");
  exit;
}

/* Load requests for this developer */
$sql = "SELECT r.*, u.full_name AS client_name, u.phone AS client_phone, u.email AS client_email
        FROM requests r
        JOIN users u ON u.user_id = r.client_id
        WHERE r.provider_id=?
          AND r.request_type='developer_land'
        ORDER BY r.creation_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $dev_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Land Requests</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="/homeplan/developer/dashboard.php" class="btn btn-outline-dark mb-3">Back</a>
  <h3 class="mb-3">Land Requests</h3>

  <?php if (!$res || mysqli_num_rows($res) === 0): ?>
    <div class="alert alert-info">No land requests yet.</div>
  <?php else: ?>
    <?php while ($r = mysqli_fetch_assoc($res)): ?>
      <?php
        $status = strtolower((string)($r['status'] ?? 'pending'));
        $badgeClass = 'bg-secondary';
        if ($status === 'pending')  $badgeClass = 'bg-warning text-dark';
        if ($status === 'accepted') $badgeClass = 'bg-success';
        if ($status === 'rejected') $badgeClass = 'bg-danger';
      ?>

      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-1">Client: <?= h($r['client_name'] ?? '') ?></h5>
              <div class="small text-muted">
                <?= h($r['client_phone'] ?? '') ?> | <?= h($r['client_email'] ?? '') ?>
              </div>
              <div class="small mt-1">
                <b>Type:</b> <?= h($r['request_type'] ?? '') ?> |
                <b>Time:</b> <?= h($r['creation_date'] ?? '') ?>
              </div>
            </div>
            <span class="badge <?= $badgeClass ?> text-uppercase"><?= h($status) ?></span>
          </div>

          <hr>

          <div class="row g-2">
            <div class="col-md-6"><b>Area:</b> <?= showVal($r['area_value'] ?? null) ?> <?= showVal($r['area_unit'] ?? null) ?></div>
            <div class="col-md-6"><b>Asking Price:</b> <?= showVal($r['asking_price'] ?? null) ?></div>
            <div class="col-md-6"><b>Road Width:</b> <?= showVal($r['road_width'] ?? null) ?></div>
            <div class="col-md-6"><b>Location:</b> <?= showVal($r['location_text'] ?? null) ?></div>
            <div class="col-md-6"><b>Ownership:</b> <?= showVal($r['ownership_type'] ?? null) ?></div>
            <div class="col-md-12"><b>Notes:</b> <?= nl2br(showVal($r['notes'] ?? null)) ?></div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <?php if ($status === 'pending'): ?>
              <a class="btn btn-success" href="/homeplan/developer/requests.php?action=accept&id=<?= (int)$r['request_id'] ?>"
                 onclick="return confirm('Accept this request?')">Accept</a>

              <a class="btn btn-danger" href="/homeplan/developer/requests.php?action=reject&id=<?= (int)$r['request_id'] ?>"
                 onclick="return confirm('Reject this request?')">Reject</a>
            <?php else: ?>
              <div class="text-muted">
                This request is already <b><?= h($status) ?></b>.
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

    <?php endwhile; ?>
  <?php endif; ?>

</div>
</body>
</html>
<?php mysqli_stmt_close($stmt); ?>


