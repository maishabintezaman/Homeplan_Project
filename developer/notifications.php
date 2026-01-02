<?php
// /homeplan/developer/notifications.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

// Auth check: must be developer
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$developer_id = (int)$_SESSION['user_id'];

// Fetch notifications (latest first)
$sql = "
    SELECT notification_id, message, status, creation_date
    FROM notifications
    WHERE user_id = ?
    ORDER BY creation_date DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("DB Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $developer_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark all as read (AFTER fetching)
$upd = $conn->prepare("UPDATE notifications SET status='read' WHERE user_id=? AND status='unread'");
if ($upd) {
    $upd->bind_param("i", $developer_id);
    $upd->execute();
    $upd->close();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Notifications</h3>
    <a href="/homeplan/developer/dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if (!$result || $result->num_rows === 0): ?>
    <div class="alert alert-info">No notifications found.</div>
  <?php else: ?>
    <div class="list-group">
      <?php while ($n = $result->fetch_assoc()): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between gap-3">
            <div><?= htmlspecialchars($n['message']) ?></div>
            <small class="text-muted">
              <?= htmlspecialchars(date('Y-m-d H:i', strtotime($n['creation_date']))) ?>
            </small>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

</div>

</body>
</html>
<?php
$stmt->close();
?>

