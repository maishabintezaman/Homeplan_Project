<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

/* Auth (client only) */
if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

/*
  List architects:
  - from users (role='architect')
  - left join architect_profiles (may not exist for old accounts)
*/
$sql = "
SELECT
  u.user_id,
  u.full_name,
  ap.certificate_number,
  ap.years_experience,
  ap.expertise,
  ap.portfolio_url
FROM users u
LEFT JOIN architect_profiles ap ON ap.architect_id = u.user_id
WHERE LOWER(TRIM(u.role)) = 'architect'
ORDER BY u.user_id DESC
";

$res = $conn->query($sql);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architects</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Architects</h3>
    <a class="btn btn-outline-secondary" href="/homeplan/client/providers.php">Back</a>
  </div>

  <?php if (!$res || $res->num_rows === 0): ?>
    <div class="alert alert-info">No architects found.</div>
  <?php else: ?>
    <?php while($a = $res->fetch_assoc()): ?>
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="mb-1"><?= htmlspecialchars($a['full_name'] ?? '') ?></h5>

              <div class="text-muted small">
                Certificate: <?= htmlspecialchars($a['certificate_number'] ?? 'Not set') ?>
              </div>

              <div class="text-muted small">
                Experience: <?= (int)($a['years_experience'] ?? 0) ?> years
              </div>

              <div class="text-muted small">
                Expertise: <?= htmlspecialchars($a['expertise'] ?? 'Not set') ?>
              </div>

              <?php if (!empty($a['portfolio_url'])): ?>
                <div class="small mt-1">
                  Portfolio:
                  <a href="<?= htmlspecialchars($a['portfolio_url']) ?>" target="_blank">
                    <?= htmlspecialchars($a['portfolio_url']) ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>

            <div class="text-end">
              <a class="btn btn-outline-primary btn-sm"
                 href="/homeplan/client/architect_view.php?architect_id=<?= (int)$a['user_id'] ?>">
                View
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

</body>
</html>

