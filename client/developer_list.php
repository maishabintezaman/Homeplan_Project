<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

$q = trim($_GET['q'] ?? '');

$sql = "SELECT user_id, full_name, email, phone, city
        FROM users
        WHERE role='developer'";

$params = [];
$types = "";

if ($q !== '') {
  $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?)";
  $like = "%$q%";
  $params = [$like,$like,$like,$like];
  $types = "ssss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($q !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Developers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Developers</h3>
    <a href="/homeplan/client/provider_options.php" class="btn btn-outline-dark">Back</a>
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
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="mb-1"><?= htmlspecialchars($row['full_name']) ?></h5>
            <div class="text-muted small mb-2"><?= htmlspecialchars($row['city'] ?? '') ?></div>
            <div class="small">
              <div><b>Email:</b> <?= htmlspecialchars($row['email']) ?></div>
              <div><b>Phone:</b> <?= htmlspecialchars($row['phone'] ?? '') ?></div>
            </div>
            <div class="d-flex gap-2 mt-3">
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
<?php mysqli_stmt_close($stmt); ?>


