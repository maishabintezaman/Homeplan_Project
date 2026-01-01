<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// If you enforce client login:
if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$stmt = $pdo->prepare("
  SELECT
    u.user_id,
    u.full_name,
    u.email,
    u.phone,
    ap.years_experience,
    ap.expertise,
    ap.certificate_number
  FROM users u
  LEFT JOIN architect_profiles ap ON ap.architect_id = u.user_id
  WHERE LOWER(u.role) = 'architect'
  ORDER BY u.full_name ASC
");
$stmt->execute();
$architects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architect List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h2 class="mb-3">Choose an Architect</h2>

  <?php if (empty($architects)): ?>
    <div class="alert alert-warning">No architects found.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($architects as $a): ?>
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="mb-1"><?= htmlspecialchars($a['full_name'] ?? '') ?></h5>

              <div class="text-muted small">
                <?= htmlspecialchars($a['email'] ?? '') ?> |
                <?= htmlspecialchars($a['phone'] ?? '') ?>
              </div>

              <!-- ✅ Expertise -->
              <div class="mt-2 small">
                <div><strong>Expertise:</strong> <?= htmlspecialchars($a['expertise'] ?? 'Not provided') ?></div>
                <div><strong>Experience:</strong> <?= isset($a['years_experience']) ? (int)$a['years_experience'] . ' years' : 'N/A' ?></div>
              </div>

              <div class="mt-3">
                <!-- IMPORTANT: goes to profile page, not request form -->
                <a class="btn btn-primary btn-sm"
                   href="architect_view.php?architect_id=<?= (int)$a['user_id'] ?>">
                  View Profile
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

