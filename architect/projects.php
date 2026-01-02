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
$error = '';
$success = '';

/* Delete */
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
  $pid = (int)$_GET['delete'];

  $stmt = $conn->prepare("SELECT image_url FROM architect_projects WHERE project_id=? AND architect_id=? LIMIT 1");
  $stmt->bind_param("ii", $pid, $architect_id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();

  if ($row) {
    $stmt2 = $conn->prepare("DELETE FROM architect_projects WHERE project_id=? AND architect_id=?");
    $stmt2->bind_param("ii", $pid, $architect_id);
    $stmt2->execute();

    // If you store local file paths, you can unlink here (optional)
    // Example: if image_url is like "/homeplan/uploads/..."
    if (!empty($row['image_url']) && str_starts_with($row['image_url'], "/homeplan/")) {
      $abs = __DIR__ . "/.." . $row['image_url'];
      if (is_file($abs)) @unlink($abs);
    }

    header("Location: /homeplan/architect/projects.php?ok=1");
    exit;
  } else {
    $error = "Project not found or not yours.";
  }
}

if (isset($_GET['ok'])) $success = "Project deleted.";

/* List */
$projects = [];
$stmt = $conn->prepare("
SELECT project_id, title, image_url, created_at
FROM architect_projects
WHERE architect_id = ?
ORDER BY project_id DESC
");
$stmt->bind_param("i", $architect_id);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $projects[] = $r;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Projects</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Projects</h3>
    <a class="btn btn-primary" href="/homeplan/architect/add_project.php">Add Project</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (count($projects) === 0): ?>
    <div class="alert alert-info">No projects yet. Click <b>Add Project</b>.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($projects as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm h-100">
            <?php if (!empty($p['image_url'])): ?>
  <img
    src="<?= htmlspecialchars($p['image_url']) ?>"
    class="card-img-top"
    style="height:200px; object-fit:cover;"
    onerror="this.onerror=null; this.src='/homeplan/assets/no-image.png';"
  >
<?php else: ?>
  <img
    src="/homeplan/assets/no-image.png"
    class="card-img-top"
    style="height:200px; object-fit:cover;"
  >
<?php endif; ?>


            <div class="card-body">
              <h5 class="card-title mb-1"><?= htmlspecialchars($p['title'] ?? '') ?></h5>

              <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted small">
                  <?= htmlspecialchars($p['created_at'] ?? '') ?>
                </span>

                <a class="btn btn-outline-danger btn-sm"
                   href="/homeplan/architect/projects.php?delete=<?= (int)$p['project_id'] ?>"
                   onclick="return confirm('Delete this project?')">
                  Delete
                </a>
              </div>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

</body>
</html>

