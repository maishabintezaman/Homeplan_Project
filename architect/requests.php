<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$architect_id = (int)$_SESSION['user_id'];

$sql = "
  SELECT
    ar.request_id,
    ar.project_type,
    ar.location,
    ar.area_sqft,
    ar.budget,
    ar.preferred_date,
    ar.message,
    ar.status,
    ar.created_at,
    u.full_name AS client_name,
    u.email AS client_email
  FROM architect_requests ar
  JOIN users u ON u.user_id = ar.client_user_id
  WHERE ar.architect_user_id = ?
  ORDER BY ar.request_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $architect_id);
$stmt->execute();
$rows = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Requests</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Requests</h3>
    <a class="btn btn-outline-secondary" href="/homeplan/architect/dashboard.php">Back</a>
  </div>

  <?php if ($rows->num_rows === 0): ?>
    <div class="alert alert-info">No requests yet.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php while ($r = $rows->fetch_assoc()): ?>
        <?php
          $status = strtolower($r['status'] ?? 'pending');
          $badge = $status === 'accepted' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
        ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between">
            <div>
              <div class="fw-bold"><?= htmlspecialchars($r['client_name']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($r['client_email']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($r['created_at'] ?? '') ?></div>
            </div>
            <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
          </div>

          <div class="mt-2">
            <div><b>Project Type:</b> <?= htmlspecialchars($r['project_type']) ?></div>
            <div><b>Location:</b> <?= htmlspecialchars($r['location']) ?></div>
            <div><b>Area:</b> <?= (int)$r['area_sqft'] ?> sqft</div>
            <div><b>Budget:</b> <?= htmlspecialchars($r['budget'] ?? '') ?></div>
            <div><b>Preferred Date:</b> <?= htmlspecialchars($r['preferred_date'] ?? '') ?></div>

            <?php if (!empty($r['message'])): ?>
              <div class="mt-1"><b>Message:</b><br><?= nl2br(htmlspecialchars($r['message'])) ?></div>
            <?php endif; ?>
          </div>

          <div class="mt-3">
            <?php if ($status === 'pending'): ?>
              <form class="d-flex gap-2" method="post" action="/homeplan/architect/request_action.php">
                <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
                <button class="btn btn-success btn-sm" name="status" value="accepted">Accept</button>
                <button class="btn btn-danger btn-sm" name="status" value="rejected">Reject</button>
              </form>
            <?php else: ?>
              <div class="text-muted small">Already processed.</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>


