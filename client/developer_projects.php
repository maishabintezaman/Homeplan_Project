<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

$developer_id = (int)($_GET['developer_id'] ?? 0);
if ($developer_id <= 0) die("Invalid developer");

$dev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id, full_name, city, phone, email FROM users WHERE user_id=$developer_id AND role='developer' LIMIT 1"));
if (!$dev) die("Developer not found");

$sql = "SELECT project_id, project_name, location, summary, created_at
        FROM projects
        WHERE user_id=?
        ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $developer_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Developer Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="/homeplan/client/developer_list.php" class="btn btn-outline-dark mb-3">Back</a>

  <div class="card mb-3">
    <div class="card-body">
      <h4 class="mb-0"><?= htmlspecialchars($dev['full_name']) ?></h4>
      <div class="text-muted"><?= htmlspecialchars($dev['city'] ?? '') ?></div>
      <div class="small mt-2">
        <div><b>Email:</b> <?= htmlspecialchars($dev['email']) ?></div>
        <div><b>Phone:</b> <?= htmlspecialchars($dev['phone'] ?? '') ?></div>
      </div>

      <a class="btn btn-success mt-3"
         href="/homeplan/client/developer_land_request.php?developer_id=<?= (int)$dev['user_id'] ?>">
        Request Your Land
      </a>
    </div>
  </div>

  <h5 class="mb-2">Completed / Ongoing Projects</h5>

  <div class="row g-3">
    <?php while ($p = mysqli_fetch_assoc($res)): ?>
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="mb-1"><?= htmlspecialchars($p['project_name']) ?></h5>
            <div class="text-muted small"><?= htmlspecialchars($p['location'] ?? '') ?></div>
            <p class="mt-2 mb-2"><?= nl2br(htmlspecialchars($p['summary'] ?? '')) ?></p>

            <?php
              $imgs = mysqli_query($conn, "SELECT image_path FROM project_images WHERE project_id=".(int)$p['project_id']." LIMIT 3");
            ?>
            <div class="d-flex gap-2 flex-wrap">
              <?php while ($im = mysqli_fetch_assoc($imgs)): ?>
                <img src="<?= htmlspecialchars($im['image_path']) ?>" style="width:110px;height:75px;object-fit:cover;border-radius:8px;">
              <?php endwhile; ?>
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
