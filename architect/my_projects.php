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

/* Load projects */
$stmt = $conn->prepare("
  SELECT project_id, title, description, image_url, created_at
  FROM architect_projects
  WHERE architect_id = ?
  ORDER BY created_at DESC, project_id DESC
");
$stmt->bind_param("i", $architect_id);
$stmt->execute();
$res = $stmt->get_result();

$projects = [];
while ($row = $res->fetch_assoc()) {
  $projects[] = $row;
}
$stmt->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Projects</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .proj-img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-top-left-radius: .5rem;
      border-top-right-radius: .5rem;
      background: #f1f3f5;
    }
    .clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width:1100px;">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">My Projects</h3>
      <div class="text-muted">All projects you have added.</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-dark" href="/homeplan/architect/dashboard.php">Back</a>
      <a class="btn btn-primary" href="/homeplan/architect/projects.php">Add Project</a>
    </div>
  </div>

  <?php if (empty($projects)): ?>
    <div class="alert alert-info">No projects yet. Click <b>Add Project</b> to create your first one.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($projects as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm h-100">
            <?php
              $img = trim($p['image_url'] ?? '');
              $imgSafe = ($img !== '') ? htmlspecialchars($img) : '';
            ?>
            <?php if ($imgSafe !== ''): ?>
              <img class="proj-img" src="<?= $imgSafe ?>" alt="Project image">
            <?php else: ?>
              <div class="proj-img d-flex align-items-center justify-content-center text-muted">
                No Image
              </div>
            <?php endif; ?>

            <div class="card-body">
              <h5 class="mb-1"><?= htmlspecialchars($p['title'] ?? 'Untitled') ?></h5>
              <div class="text-muted small mb-2">
                Created: <?= htmlspecialchars($p['created_at'] ?? '') ?>
              </div>

              <?php $desc = trim($p['description'] ?? ''); ?>
              <?php if ($desc !== ''): ?>
                <p class="mb-0 clamp-2"><?= nl2br(htmlspecialchars($desc)) ?></p>
              <?php else: ?>
                <p class="text-muted mb-0">No description.</p>
              <?php endif; ?>
            </div>

            <div class="card-footer bg-white">
              <div class="small text-muted">Project ID: <?= (int)$p['project_id'] ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
