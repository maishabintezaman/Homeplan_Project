<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$clientId = (int)$_SESSION['user_id'];
$architectId = isset($_GET['architect_id']) ? (int)$_GET['architect_id'] : 0;

if ($architectId <= 0) {
  http_response_code(400);
  echo "Architect not selected.";
  exit;
}

// fetch architect info for header
$stmt = $pdo->prepare("
  SELECT user_id, full_name, email, phone
  FROM users
  WHERE user_id = ? AND LOWER(role)='architect'
  LIMIT 1
");
$stmt->execute([$architectId]);
$architect = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$architect) {
  http_response_code(404);
  echo "Architect not found.";
  exit;
}

// Project type options
$projectTypes = [
  "Residential",
  "Commercial",
  "Interior Design",
  "Renovation",
  "Landscape",
  "Other"
];

// Alert messages
$successMsg = null;
$errorMsg = null;

if (isset($_GET['success']) && $_GET['success'] == '1') {
  $successMsg = "Request sent successfully!";
}

if (isset($_GET['error'])) {
  $error = $_GET['error'];
  if ($error === 'missing')  $errorMsg = "Required fields missing.";
  elseif ($error === 'notfound') $errorMsg = "Architect not found.";
  elseif ($error === 'already')  $errorMsg = "You already have a pending request for this architect.";
  else $errorMsg = "Something went wrong. Please try again.";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request Architect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Request Architect</h2>
    <a class="btn btn-outline-secondary" href="architect_view.php?architect_id=<?= (int)$architectId ?>">Back</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-0"><?= htmlspecialchars($architect['full_name'] ?? '') ?></h4>
      <div class="text-muted">
        <?= htmlspecialchars($architect['email'] ?? '') ?> | <?= htmlspecialchars($architect['phone'] ?? '') ?>
      </div>

      <hr>

      <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong><?= htmlspecialchars($successMsg) ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($errorMsg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="/homeplan/client/architect_request_post.php">

        <input type="hidden" name="architect_id" value="<?= (int)$architectId ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Project Type</label>
            <select class="form-select" name="project_type" required>
              <option value="">Select...</option>
              <?php foreach ($projectTypes as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input class="form-control" name="location" type="text" placeholder="e.g. Gulshan, Dhaka" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Area (sqft)</label>
            <input class="form-control" name="area_sqft" type="number" min="0" placeholder="e.g. 2500">
          </div>

          <div class="col-md-6">
            <label class="form-label">Budget (BDT)</label>
            <input class="form-control" name="budget" type="number" min="0" step="0.01" placeholder="e.g. 3000000">
          </div>

          <div class="col-md-6">
            <label class="form-label">Preferred Date</label>
            <input class="form-control" name="preferred_date" type="date">
          </div>

          <div class="col-12">
            <label class="form-label">Message (optional)</label>
            <textarea class="form-control" name="message" rows="5" placeholder="Write your project details..."></textarea>
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-primary" type="submit">Send Request</button>
        </div>
      </form>

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

