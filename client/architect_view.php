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
if (strtolower(trim($_SESSION['role'] ?? '')) !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

/* accept architect_id OR id (backward compatible) */
$rawId = $_GET['architect_id'] ?? ($_GET['id'] ?? null);
if ($rawId === null || !ctype_digit((string)$rawId)) {
  die('Invalid architect id.');
}
$architect_id = (int)$rawId;

/* Load architect basic + profile */
$st = $conn->prepare("
  SELECT
    u.user_id, u.full_name, u.email, u.phone,
    ap.certificate_number, ap.years_experience, ap.expertise, ap.portfolio_url
  FROM users u
  LEFT JOIN architect_profiles ap ON ap.architect_id = u.user_id
  WHERE u.user_id = ? AND LOWER(TRIM(u.role))='architect'
  LIMIT 1
");
$st->bind_param("i", $architect_id);
$st->execute();
$a = $st->get_result()->fetch_assoc();

if (!$a) {
  die('Architect not found.');
}

/* Load projects */
$projects = [];
$st2 = $conn->prepare("
  SELECT project_id, title, image_url, created_at
  FROM architect_projects
  WHERE architect_id = ?
  ORDER BY project_id DESC
");
$st2->bind_param("i", $architect_id);
$st2->execute();
$rs2 = $st2->get_result();
while ($p = $rs2->fetch_assoc()) $projects[] = $p;

/* ok message */
$ok = isset($_GET['ok']) ? (int)$_GET['ok'] : 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($a['full_name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= htmlspecialchars($a['full_name']) ?></h3>
    <a class="btn btn-outline-secondary" href="/homeplan/client/architect_list.php">Back</a>
  </div>

  <?php if ($ok === 1): ?>
    <div class="alert alert-success">Request sent successfully.</div>
  <?php endif; ?>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div>Email: <?= htmlspecialchars($a['email'] ?? '') ?></div>
      <div>Phone: <?= htmlspecialchars($a['phone'] ?? '') ?></div>
      <div>Certificate: <?= htmlspecialchars($a['certificate_number'] ?? 'Not set') ?></div>
      <div>Experience: <?= (int)($a['years_experience'] ?? 0) ?> years</div>
      <div>Expertise: <?= htmlspecialchars($a['expertise'] ?? 'Not set') ?></div>

      <?php if (!empty($a['portfolio_url'])): ?>
        <div class="mt-1">
          Portfolio:
          <a href="<?= htmlspecialchars($a['portfolio_url']) ?>" target="_blank" rel="noopener">
            <?= htmlspecialchars($a['portfolio_url']) ?>
          </a>
        </div>
      <?php endif; ?>

      <div class="mt-3">
        <!-- FIXED: use $architect_id -->
        <a href="/homeplan/client/architect_request.php?id=<?= (int)$architect_id ?>" class="btn btn-success">
          Request Architect
        </a>
      </div>
    </div>
  </div>

  <h5 class="mb-2">Projects</h5>

  <?php if (count($projects) === 0): ?>
    <div class="alert alert-info">No projects uploaded yet.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($projects as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm">
            <?php if (!empty($p['image_url'])): ?>
              <img
                src="<?= htmlspecialchars($p['image_url']) ?>"
                class="card-img-top"
                style="height:200px;object-fit:cover;"
                alt="Project image">
            <?php endif; ?>

            <div class="card-body">
              <h6 class="mb-1"><?= htmlspecialchars($p['title'] ?? '') ?></h6>
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

