<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

$dev_id = (int)$_SESSION['user_id'];

if (!empty($_GET['action']) && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $action = $_GET['action'];

  if (in_array($action, ['accept','reject'], true)) {
    $newStatus = ($action === 'accept') ? 'accepted' : 'rejected';
    $sql = "UPDATE requests SET status=? WHERE request_id=? AND provider_id=? AND request_type='developer_land'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $newStatus, $id, $dev_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  header("Location: /homeplan/developer/requests.php");
  exit;
}

$sql = "SELECT r.*, u.full_name AS client_name, u.phone AS client_phone, u.email AS client_email
        FROM requests r
        JOIN users u ON u.user_id = r.client_id
        WHERE r.provider_id=? AND r.request_type='developer_land'
        ORDER BY r.created_at DESC";

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
  <h4 class="mb-3">Land Requests</h4>

  <?php while ($r = mysqli_fetch_assoc($res)): ?>
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="mb-1">Client: <?= htmlspecialchars($r['client_name']) ?></h5>
            <div class="small text-muted">
              <?= htmlspecialchars($r['client_phone'] ?? '') ?> | <?= htmlspecialchars($r['client_email'] ?? '') ?>
            </div>
          </div>
          <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($r['status']) ?></span>
        </div>

        <hr>

        <div class="row g-2 small">
          <div class="col-md-4"><b>Area:</b> <?= htmlspecialchars($r['area_value']) ?> <?= htmlspecialchars($r['area_unit']) ?></div>
          <div class="col-md-4"><b>Asking Price:</b> <?= htmlspecialchars($r['asking_price']) ?></div>
          <div class="col-md-4"><b>Road Width:</b> <?= htmlspecialchars($r['road_width'] ?? '') ?></div>
          <div class="col-md-12"><b>Location:</b> <?= htmlspecialchars($r['location_text'] ?? '') ?></div>
          <div class="col-md-12"><b>Ownership:</b> <?= htmlspecialchars($r['ownership_type'] ?? '') ?></div>
          <div class="col-md-12"><b>Notes:</b> <?= nl2br(htmlspecialchars($r['notes'] ?? '')) ?></div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <a class="btn btn-success btn-sm" href="/homeplan/developer/requests.php?action=accept&id=<?= (int)$r['request_id'] ?>"
             onclick="return confirm('Accept this request?')">Accept</a>

          <a class="btn btn-danger btn-sm" href="/homeplan/developer/requests.php?action=reject&id=<?= (int)$r['request_id'] ?>"
             onclick="return confirm('Reject this request?')">Reject</a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>

</div>
</body>
</html>
<?php mysqli_stmt_close($stmt); ?>



