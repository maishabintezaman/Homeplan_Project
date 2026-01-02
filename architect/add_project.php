<?php
// /homeplan/architect/add_project.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

require_once __DIR__ . '/../includes/topbar.php';


$success = (($_GET['success'] ?? '') === '1');
$error   = (($_GET['error'] ?? '') === '1');
$msg     = trim($_GET['msg'] ?? '');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Project</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4" style="max-width:850px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Add Project</h3>
    <a href="/homeplan/architect/dashboard.php" class="btn btn-outline-dark">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      ❌ <strong>Failed!</strong>
      <?= htmlspecialchars($msg ?: "Please try again.") ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">

      <form action="/homeplan/architect/add_project_action.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Project Title</label>
          <input type="text" name="title" class="form-control" required maxlength="120" placeholder="e.g. Modern 5-Storey Building">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Project details, features, materials, etc..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Project Image</label>
          <input type="file" name="image" class="form-control" accept="image/*" required>
          <div class="form-text">Allowed: jpg, jpeg, png, webp (max ~5MB)</div>
        </div>

        <button class="btn btn-success w-100">Save Project</button>
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
        Your project has been added successfully.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
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

