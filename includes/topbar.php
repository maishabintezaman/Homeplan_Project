<?php
// /homeplan/includes/topbar.php
$name = $_SESSION['name'] ?? ($_SESSION['full_name'] ?? 'User');
$role = $_SESSION['role'] ?? '';
?>
<nav class="navbar navbar-expand-lg bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/homeplan/index.php">HomePlan</a>

    <div class="ms-auto d-flex align-items-center gap-3">
      <div class="text-end">
        <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($role) ?></div>
      </div>
      <a class="btn btn-outline-danger btn-sm" href="/homeplan/auth/logout.php">Logout</a>
    </div>
  </div>
</nav>

