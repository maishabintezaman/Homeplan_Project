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
$success = (($_GET['success'] ?? '') === '1');
$error   = (($_GET['error'] ?? '') === '1');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Project</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width:900px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Add Project</h3>
    <a class="btn btn-outline-secondary" href="/homeplan/developer/dashboard.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger">❌ Upload failed. Please try again.</div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="/homeplan/developer/add_project_action.php" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Project Title</label>
            <input name="title" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Location (optional)</label>
            <input name="location" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">Description (optional)</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Project Image (JPG/PNG/WEBP)</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
          </div>
        </div>

        <button class="btn btn-primary mt-4 w-100">Upload Project</button>
      </form>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✅ Project Added</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Your project was uploaded successfully.
      </div>
      <div class="modal-footer">
        <a class="btn btn-primary" href="/homeplan/developer/add_project.php">Add Another</a>
        <a class="btn btn-outline-secondary" href="/homeplan/developer/dashboard.php">Back</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($success): ?>
<script>
  new bootstrap.Modal(document.getElementById('successModal')).show();
</script>
<?php endif; ?>
</body>
</html>
