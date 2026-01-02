<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$developer_id = (int)($_GET['developer_id'] ?? 0);
if ($developer_id <= 0) die("Invalid developer_id");

/* Developer basic + profile (developer_profiles optional) */
$stmt = $conn->prepare("
  SELECT u.user_id, u.full_name, u.email, u.phone
  FROM users u
  WHERE u.user_id = ? AND LOWER(TRIM(u.role))='developer'
  LIMIT 1
");
$stmt->bind_param("i", $developer_id);
$stmt->execute();
$dev = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dev) die("Developer not found");

/* Projects */
$projects = [];
$stmt2 = $conn->prepare("
  SELECT project_id, title, location, description, image_url, created_at
  FROM developer_projects
  WHERE developer_id = ?
  ORDER BY project_id DESC
");
$stmt2->bind_param("i", $developer_id);
$stmt2->execute();
$rs = $stmt2->get_result();
while ($row = $rs->fetch_assoc()) $projects[] = $row;
$stmt2->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($dev['full_name']) ?> - Projects</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width:1000px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0"><?= htmlspecialchars($dev['full_name']) ?></h3>
      <div class="text-muted">
        <?= htmlspecialchars($dev['email'] ?? '') ?>
        <?= !empty($dev['phone']) ? " | " . htmlspecialchars($dev['phone']) : "" ?>
      </div>
    </div>
    <a class="btn btn-outline-secondary" href="/homeplan/client/developer_list.php">Back</a>
  </div>

  <div class="d-flex gap-2 mb-3">
    <a class="btn btn-success"
       href="/homeplan/client/developer_land_request.php?developer_id=<?= (int)$developer_id ?>">
      Request Your Land
    </a>
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
              <img src="<?= htmlspecialchars($p['image_url']) ?>" class="card-img-top"
                   style="height:200px;object-fit:cover;">
            <?php endif; ?>
            <div class="card-body">
              <h6 class="mb-1"><?= htmlspecialchars($p['title']) ?></h6>
              <?php if (!empty($p['location'])): ?>
                <div class="text-muted small mb-2"><?= htmlspecialchars($p['location']) ?></div>
              <?php endif; ?>
              <?php if (!empty($p['description'])): ?>
                <div class="small"><?= nl2br(htmlspecialchars($p['description'])) ?></div>
              <?php endif; ?>
            </div>
            <div class="card-footer text-muted small">
              <?= htmlspecialchars($p['created_at']) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>


