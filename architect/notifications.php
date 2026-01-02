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

$user_id = (int)$_SESSION['user_id'];

/* mark as read */
if (isset($_GET['mark']) && $_GET['mark'] === 'all') {
  $st = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
  $st->bind_param("i", $user_id);
  $st->execute();
  header("Location: /homeplan/architect/notifications.php");
  exit;
}

/* load notifications */
$st = $conn->prepare("
SELECT notification_id, message, status, creation_date
FROM notifications
WHERE user_id = ?
ORDER BY notification_id DESC
");
$st->bind_param("i", $user_id);
$st->execute();
$notifs = $st->get_result();
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

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Notifications</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/homeplan/architect/dashboard.php">Back</a>
      <a class="btn btn-outline-primary" href="/homeplan/architect/notifications.php?mark=all">Mark all as read</a>
    </div>
  </div>

  <?php if ($notifs->num_rows === 0): ?>
    <div class="alert alert-info">No notifications yet.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php while ($n = $notifs->fetch_assoc()): ?>
        <?php $isUnread = strtolower($n['status'] ?? '') === 'unread'; ?>
        <div class="list-group-item <?= $isUnread ? 'list-group-item-warning' : '' ?>">
          <div class="d-flex justify-content-between">
            <div><?= htmlspecialchars($n['message']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($n['creation_date']) ?></div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
