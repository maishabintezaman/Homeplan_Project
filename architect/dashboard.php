<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';


if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$architect_id = (int)$_SESSION['user_id'];

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architect Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width:900px;">
  <h3 class="mb-3">Architect Dashboard</h3>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5>Add Projects</h5>
          <p class="text-muted mb-3">Upload your projects with image & details.</p>
          <a class="btn btn-primary w-100" href="/homeplan/architect/add_project.php">Open</a>
        </div>
      </div>
    </div>

    <!-- ✅ NEW: My Projects -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5>My Projects</h5>
          <p class="text-muted mb-3">See your added projects & future projects.</p>
          <a class="btn btn-outline-primary w-100" href="/homeplan/architect/projects.php">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5>My Requests</h5>
          <p class="text-muted mb-3">See land requests from clients.</p>
          <a class="btn btn-success w-100" href="/homeplan/architect/requests.php">Open</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5>Notifications</h5>
          <p class="text-muted mb-3">New requests & updates.</p>
          <a class="btn btn-dark w-100" href="/homeplan/architect/notifications.php">Open</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>


