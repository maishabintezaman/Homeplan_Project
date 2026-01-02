<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$client_id = (int)$_SESSION['user_id'];
$architect_id = (int)($_GET['id'] ?? 0);

if ($architect_id <= 0) {
  die("Invalid architect id");
}

/* ensure architect exists */
$st = $conn->prepare("
  SELECT u.user_id, u.full_name, u.email, u.phone
  FROM users u
  WHERE u.user_id=? AND LOWER(TRIM(u.role))='architect'
  LIMIT 1
");
$st->bind_param("i", $architect_id);
$st->execute();
$architect = $st->get_result()->fetch_assoc();

if (!$architect) {
  die("Architect not found");
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request Architect</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width: 900px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0">Request Architect</h3>
    <a class="btn btn-outline-dark" href="/homeplan/client/architect_view.php?architect_id=<?= (int)$architect_id ?>">Back</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-1"><?= htmlspecialchars($architect['full_name']) ?></h5>
      <div class="text-muted">
        <?= htmlspecialchars($architect['email'] ?? '') ?>
        <?= !empty($architect['phone']) ? " | " . htmlspecialchars($architect['phone']) : "" ?>
      </div>

      <hr>

      <form method="post" action="/homeplan/client/architect_request_post.php" class="row g-3">
        <!-- IMPORTANT: name must match POST handler -->
        <input type="hidden" name="architect_user_id" value="<?= (int)$architect_id ?>">

        <div class="col-md-6">
          <label class="form-label">Project Type</label>
          <select name="project_type" class="form-select" required>
            <option value="">Select...</option>
            <option value="Residential">Residential</option>
            <option value="Commercial">Commercial</option>
            <option value="Renovation">Renovation</option>
            <option value="Mosque">Mosque</option>
            <option value="Interior">Interior</option>
            <option value="Landscape">Landscape</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Location</label>
          <input name="location" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Area (sqft)</label>
          <input name="area_sqft" type="number" class="form-control" min="0" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Budget (BDT)</label>
          <input name="budget" type="number" class="form-control" min="0">
        </div>

        <div class="col-md-4">
          <label class="form-label">Preferred Date</label>
          <input name="preferred_date" type="date" class="form-control">
        </div>

        <div class="col-12">
          <label class="form-label">Message (optional)</label>
          <textarea name="message" class="form-control" rows="4"></textarea>
        </div>

        <div class="col-12">
          <button class="btn btn-primary">Send Request</button>
        </div>
      </form>

    </div>
  </div>

</div>
</body>
</html>


