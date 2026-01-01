<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

$user_id = (int)$_SESSION['user_id'];

$uploadDir = __DIR__ . '/../uploads/projects/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $project_name = trim($_POST['project_name'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $summary = trim($_POST['summary'] ?? '');

  if ($project_name !== '') {
    $sql = "INSERT INTO projects (user_id, project_name, location, summary, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $project_name, $location, $summary);
    mysqli_stmt_execute($stmt);
    $project_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // handle images
    if (!empty($_FILES['images']['name'][0])) {
      foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
        if (!is_uploaded_file($tmp)) continue;

        $name = basename($_FILES['images']['name'][$i]);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;

        $newName = 'p'.$project_id.'_'.time().'_'.$i.'.'.$ext;
        $dest = $uploadDir . $newName;

        if (move_uploaded_file($tmp, $dest)) {
          $webPath = '/homeplan/uploads/projects/' . $newName;
          mysqli_query($conn, "INSERT INTO project_images (project_id, image_path) VALUES ($project_id, '".mysqli_real_escape_string($conn,$webPath)."')");
        }
      }
    }
  }

  header("Location: /homeplan/developer/projects.php");
  exit;
}

if (!empty($_GET['delete'])) {
  $pid = (int)$_GET['delete'];
  // delete only own
  $chk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT project_id FROM projects WHERE project_id=$pid AND user_id=$user_id LIMIT 1"));
  if ($chk) {
    mysqli_query($conn, "DELETE FROM projects WHERE project_id=$pid");
  }
  header("Location: /homeplan/developer/projects.php");
  exit;
}

$projects = mysqli_query($conn, "SELECT project_id, project_name, location, summary, created_at
                                FROM projects WHERE user_id=$user_id ORDER BY created_at DESC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Projects</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:950px;">
  <a href="/homeplan/developer/dashboard.php" class="btn btn-outline-dark mb-3">Back</a>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h4 class="mb-3">Add Project</h4>
      <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Project Name</label>
            <input name="project_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input name="location" class="form-control">
          </div>
          <div class="col-md-12">
            <label class="form-label">Summary</label>
            <textarea name="summary" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-md-12">
            <label class="form-label">Project Images (multiple)</label>
            <input type="file" name="images[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp">
          </div>
        </div>
        <button class="btn btn-primary mt-3">Save Project</button>
      </form>
    </div>
  </div>

  <h5>Your Projects</h5>
  <div class="row g-3">
    <?php while ($p = mysqli_fetch_assoc($projects)): ?>
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <h5 class="mb-1"><?= htmlspecialchars($p['project_name']) ?></h5>
              <a class="btn btn-sm btn-outline-danger"
                 href="/homeplan/developer/projects.php?delete=<?= (int)$p['project_id'] ?>"
                 onclick="return confirm('Delete this project?')">Delete</a>
            </div>
            <div class="text-muted small"><?= htmlspecialchars($p['location'] ?? '') ?></div>
            <p class="mt-2 mb-2"><?= nl2br(htmlspecialchars($p['summary'] ?? '')) ?></p>

            <?php $imgs = mysqli_query($conn, "SELECT image_path FROM project_images WHERE project_id=".(int)$p['project_id']." LIMIT 4"); ?>
            <div class="d-flex gap-2 flex-wrap">
              <?php while ($im = mysqli_fetch_assoc($imgs)): ?>
                <img src="<?= htmlspecialchars($im['image_path']) ?>" style="width:110px;height:75px;object-fit:cover;border-radius:8px;">
              <?php endwhile; ?>
            </div>

          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>
</body>
</html>
