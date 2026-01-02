<?php
// /homeplan/includes/topbar.php
require_once __DIR__ . '/../config/session.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$name = $_SESSION['name'] ?? ($_SESSION['full_name'] ?? 'User');
$role = (string)($_SESSION['role'] ?? '');
$role_upper = strtoupper($role);

$homeLink = "/homeplan/index.php";
switch ($role) {
  case 'client':            $homeLink = "/homeplan/client/dashboard.php"; break;
  case 'property_owner':    $homeLink = "/homeplan/property_owner/dashboard.php"; break;
  case 'developer':         $homeLink = "/homeplan/developer/dashboard.php"; break;
  case 'architect':         $homeLink = "/homeplan/architect/dashboard.php"; break;
  case 'material_provider': $homeLink = "/homeplan/material_provider/dashboard.php"; break;
  case 'worker_provider':   $homeLink = "/homeplan/worker_provider/dashboard.php"; break;
  case 'interior_designer': $homeLink = "/homeplan/interior_designer/dashboard.php"; break;
  case 'admin':             $homeLink = "/homeplan/admin/dashboard.php"; break;
}
?>
<nav class="navbar navbar-expand-lg bg-dark border-bottom" style="height:72px;">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold text-white" href="<?= htmlspecialchars($homeLink) ?>" style="font-size:28px;">
      HomePlan
    </a>

    <div class="ms-auto d-flex align-items-center gap-4">
      <div class="text-end text-white">
        <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
        <div class="small text-muted"><?= htmlspecialchars($role_upper) ?></div>
      </div>

      <a class="btn btn-outline-light btn-sm" href="/homeplan/auth/logout.php">Logout</a>
    </div>
  </div>
</nav>

