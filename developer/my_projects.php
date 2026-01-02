<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$developer_id = (int)$_SESSION['user_id'];

/* Fetch developer projects */
$stmt = $conn->prepare("
  SELECT project_id, title, location, description, image_url
  FROM developer_projects
  WHERE developer_id = ?
  ORDER BY project_id DESC
");
$stmt->bind_param("i", $developer_id);
$stmt->execute();
$res = $stmt->get_result();
$projects = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Projects</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width:1000px;">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <a href="/homeplan/developer/dashboard.php" class="btn btn-outline-dark">Back</a>
    <a href="/homeplan/developer/add_project.php" class="btn btn-primary">+ Add New Project</a>
  </div>

  <h3 class="mb-3">My Projects</h3>

  <?php if (empty($projects)): ?>
    <div class="alert alert-info">No projects yet. Click “Add New Project”.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($projects as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm h-100">
            <?php if (!empty($p['image_url'])): ?>
              <img
                src="<?= htmlspecialchars($p['image_url']) ?>"
                class="card-img-top"
                alt="Project image"
                style="height:200px; object-fit:cover;"
              >
            <?php endif; ?>

            <div class="card-body">
              <h5 class="card-title mb-1"><?= htmlspecialchars($p['title'] ?? '') ?></h5>

              <?php if (!empty($p['location'])): ?>
                <div class="text-muted mb-2"><?= htmlspecialchars($p['location']) ?></div>
              <?php endif; ?>

              <?php if (!empty($p['description'])): ?>
                <p class="card-text" style="white-space:pre-line;">
                  <?= htmlspecialchars($p['description']) ?>
                </p>
              <?php else: ?>
                <p class="card-text text-muted">No description.</p>
              <?php endif; ?>
            </div>

            <div class="card-footer bg-white border-0">
              <small class="text-muted">Project ID: <?= (int)$p['project_id'] ?></small>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

</body>
</html>

