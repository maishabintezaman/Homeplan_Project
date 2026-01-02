<?php
// /homeplan/client/notifications.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$client_id = (int)$_SESSION['user_id'];

$sql = "
  SELECT 
    r.request_id,
    r.status,
    u.full_name AS provider_name,
    p.project_name AS property_title,
    p.property_id
  FROM requests r
  JOIN properties p ON p.property_id = r.property_id
  JOIN users u ON u.user_id = p.provider_id
  WHERE r.client_id = ?
    AND r.status IN ('accepted', 'rejected')
  ORDER BY r.request_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width: 900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Notifications</h3>
    <a class="btn btn-outline-secondary btn-sm" href="/homeplan/client/dashboard.php">Back</a>
  </div>

  <?php if ($notifications->num_rows === 0): ?>
    <div class="alert alert-info">No notifications yet.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php while ($row = $notifications->fetch_assoc()): ?>
        <?php
          $status = strtolower($row['status'] ?? '');
          $badge  = ($status === 'accepted') ? 'success' : 'danger';
          $icon   = ($status === 'accepted') ? '✅' : '❌';

          $provider = $row['provider_name'] ?? 'Provider';
          $propName = trim((string)($row['property_title'] ?? ''));
          $propId   = (int)($row['property_id'] ?? 0);

          if ($propName === '') $propName = "Property #".$propId;
        ?>
        <div class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-bold">
              <?= $icon ?>
              <?= htmlspecialchars($provider) ?>
              <?= ($status === 'accepted') ? 'accepted' : 'rejected' ?>
              your request
            </div>
            <div class="text-muted small">
              Property:
              <a href="/homeplan/client/property_view.php?id=<?= $propId ?>">
                <?= htmlspecialchars($propName) ?>
              </a>
            </div>
          </div>

          <span class="badge bg-<?= $badge ?>">
            <?= htmlspecialchars(ucfirst($status)) ?>
          </span>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


