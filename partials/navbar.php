<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db.php';

$userName = $_SESSION['full_name'] ?? 'User';
$role     = $_SESSION['role'] ?? '';
$userId   = $_SESSION['user_id'] ?? null;

$dashboardLink = '/homeplan/index.php';
if ($role === 'client') $dashboardLink = '/homeplan/client/dashboard.php';
elseif ($role === 'property_owner') $dashboardLink = '/homeplan/property_owner/dashboard.php';
elseif ($role === 'developer') $dashboardLink = '/homeplan/developer/dashboard.php';
elseif ($role === 'architect') $dashboardLink = '/homeplan/architect/dashboard.php';
elseif ($role === 'material_provider') $dashboardLink = '/homeplan/material_provider/dashboard.php';
elseif ($role === 'worker_provider') $dashboardLink = '/homeplan/worker_provider/dashboard.php';
elseif ($role === 'interior_designer') $dashboardLink = '/homeplan/interior_designer/dashboard.php';
elseif ($role === 'admin') $dashboardLink = '/homeplan/admin/dashboard.php';

$unreadCount = 0;
if ($userId && isset($conn) && $conn) {
  $sql = "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id=? AND status='unread'";
  if ($stmt = mysqli_prepare($conn, $sql)) {
    $uid = (int)$userId;
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res && ($row = mysqli_fetch_assoc($res))) $unreadCount = (int)$row['cnt'];
    mysqli_stmt_close($stmt);
  }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= htmlspecialchars($dashboardLink) ?>">HomePlan</a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <?php if ($userId): ?>
        

        <div class="dropdown">
          <button class="btn btn-dark dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <span class="fw-semibold"><?= htmlspecialchars($userName) ?></span>
            <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($role) ?></span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= htmlspecialchars($dashboardLink) ?>">Dashboard</a></li>
            <li><a class="dropdown-item" href="/homeplan/notifications.php">Notifications</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/homeplan/auth/logout.php">Logout</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a class="btn btn-outline-light" href="/homeplan/auth/login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
