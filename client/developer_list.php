<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

/* client only */
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$q = trim($_GET['q'] ?? '');

/* ---------- helper: check column exists ---------- */
function col_exists(mysqli $conn, string $table, string $col): bool {
  $sql = "SELECT 1
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
          LIMIT 1";
  $st = $conn->prepare($sql);
  $st->bind_param("ss", $table, $col);
  $st->execute();
  $ok = (bool)$st->get_result()->fetch_row();
  $st->close();
  return $ok;
}

$hasCity = col_exists($conn, "users", "city");
$hasCreatedAt = col_exists($conn, "users", "created_at");

/* ---------- build query ---------- */
$sql = "
SELECT
  u.user_id,
  u.full_name,
  u.email,
  u.phone
  " . ($hasCity ? ", u.city" : "") . ",
  dp.license_no
FROM users u
LEFT JOIN developer_profiles dp ON dp.developer_id = u.user_id
WHERE LOWER(TRIM(u.role)) = 'developer'
";

$params = [];
$types  = "";

if ($q !== '') {
  $sql .= " AND (
      u.full_name LIKE ?
      OR u.email LIKE ?
      OR u.phone LIKE ?
      " . ($hasCity ? " OR u.city LIKE ? " : "") . "
  )";
  $like = "%{$q}%";
  $params = [$like, $like, $like];
  $types  = "sss";
  if ($hasCity) {
    $params[] = $like;
    $types   .= "s";
  }
}

$sql .= $hasCreatedAt ? " ORDER BY u.created_at DESC" : " ORDER BY u.user_id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  die("SQL prepare failed: " . htmlspecialchars($conn->error));
}
if ($q !== '') {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Developers</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Developers</h3>
    <a href="/homeplan/client/providers.php" class="btn btn-outline-dark">Back</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-md-10">
      <input class="form-control" name="q" placeholder="Search developer..." value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-primary">Search</button>
    </div>
  </form>

  <div class="row g-3">
    <?php while ($row = $res->fetch_assoc()): ?>
      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="mb-1"><?= htmlspecialchars($row['full_name']) ?></h5>

            <?php if ($hasCity): ?>
              <div class="text-muted small mb-2"><?= htmlspecialchars($row['city'] ?? '') ?></div>
            <?php endif; ?>

            <div class="small">
              <div><b>Email:</b> <?= htmlspecialchars($row['email']) ?></div>
              <div><b>Phone:</b> <?= htmlspecialchars($row['phone'] ?? '') ?></div>
              <div><b>License:</b> <?= htmlspecialchars($row['license_no'] ?? 'Not set') ?></div>
            </div>

            <div class="d-flex gap-2 mt-3 flex-wrap">
              <a class="btn btn-primary btn-sm"
                 href="/homeplan/client/developer_projects.php?developer_id=<?= (int)$row['user_id'] ?>">
                See Details
              </a>
              <a class="btn btn-success btn-sm"
                 href="/homeplan/client/developer_land_request.php?developer_id=<?= (int)$row['user_id'] ?>">
                Request Your Land
              </a>
            </div>

          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>

</body>
</html>
<?php
$stmt->close();
?>



