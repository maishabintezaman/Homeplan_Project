<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$clientId = (int)$_SESSION['user_id'];
$architectUserId = (int)($_GET['id'] ?? 0);

if ($architectUserId <= 0) {
  http_response_code(400);
  echo "<h2>Invalid architect id</h2>";
  exit;
}

try {
  // SAFE: only use columns that almost certainly exist in users table
  $stmt = $pdo->prepare("
    SELECT
      u.user_id,
      u.full_name,
      u.email,
      u.phone,
      u.role
    FROM users u
    WHERE u.user_id = ?
    LIMIT 1
  ");
  $stmt->execute([$architectUserId]);
  $architect = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$architect) {
    http_response_code(404);
    echo "<h2>Architect not found</h2>";
    exit;
  }

  if (strtolower($architect['role'] ?? '') !== 'architect') {
    http_response_code(403);
    echo "<h2>This user is not an architect</h2>";
    exit;
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo "<h2>Database query failed</h2>";
  echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
  exit;
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
<div class="container py-4" style="max-width: 900px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Request Architect</h2>
    <a class="btn btn-outline-dark" href="/homeplan/client/architect_list.php">Back</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-1"><?= htmlspecialchars($architect['full_name'] ?? '') ?></h4>

      <div class="text-muted">
        <?= htmlspecialchars($architect['email'] ?? '') ?>
        <?= !empty($architect['phone']) ? " | " . htmlspecialchars($architect['phone']) : "" ?>
      </div>

      <hr>

      <form method="post" action="/homeplan/client/architect_request_post.php" class="row g-3">
        <input type="hidden" name="architect_user_id" value="<?= (int)$architect['user_id'] ?>">

        <div class="col-md-6">
          <label class="form-label">Project Type</label>
          <select name="project_type" class="form-select" required>
            <option value="">Select...</option>
            <option value="architecture">Architecture</option>
            <option value="interior">Interior</option>
            <option value="both">Both</option>
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
          <input name="budget" type="number" class="form-control" min="0" required>
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

