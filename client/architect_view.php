<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$architectId = isset($_GET['architect_id']) ? (int)$_GET['architect_id'] : 0;
if ($architectId <= 0) {
  http_response_code(400);
  echo "Invalid architect id.";
  exit;
}

// Architect basic info
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

// Expertise (optional table)
$expertise = [];
try {
  $st2 = $pdo->prepare("
    SELECT expertise
    FROM architect_expertise
    WHERE architect_user_id = ?
    ORDER BY expertise ASC
  ");
  $st2->execute([$architectId]);
  $expertise = $st2->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
  // ignore
}

// Portfolio (one row = one image)
$st3 = $pdo->prepare("
  SELECT project_id, title, image_url, created_at
  FROM architect_projects
  WHERE architect_id = ?
  ORDER BY project_id DESC
");
$st3->execute([$architectId]);
$projects = $st3->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architect Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .portfolio-img { width:100%; height:220px; object-fit:cover; border-radius:10px; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">

  <a class="btn btn-outline-secondary btn-sm mb-3" href="architect_list.php">Back</a>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h3 class="mb-1"><?= htmlspecialchars($architect['full_name'] ?? '') ?></h3>
      <div class="text-muted">
        <?= htmlspecialchars($architect['email'] ?? '') ?> |
        <?= htmlspecialchars($architect['phone'] ?? '') ?>
      </div>

      <?php if (!empty($expertise)): ?>
        <div class="mt-3">
          <?php foreach ($expertise as $tag): ?>
            <span class="badge text-bg-primary me-1"><?= htmlspecialchars($tag) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="mt-3">
        <a href="/homeplan/client/architect_request_form.php?architect_id=<?= (int)$architect['user_id'] ?>"
           class="btn btn-primary">
          Request This Architect
        </a>
      </div>

    </div>
  </div>

  <h4 class="mb-3">Previous Projects</h4>

  <?php if (empty($projects)): ?>
    <div class="alert alert-info">No portfolio projects added yet.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($projects as $p): ?>
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <img class="portfolio-img"
                   src="<?= htmlspecialchars($p['image_url'] ?? '') ?>"
                   alt="<?= htmlspecialchars($p['title'] ?? 'Project') ?>">
              <h6 class="mt-2 mb-0"><?= htmlspecialchars($p['title'] ?? '') ?></h6>
              <div class="text-muted small"><?= htmlspecialchars($p['created_at'] ?? '') ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
</body>
</html>


