<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') { header("Location:/homeplan/auth/login.php"); exit; }
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../partials/navbar.php';
$res = mysqli_query($conn, "SELECT user_id, full_name, city, phone FROM users WHERE role='material_provider' ORDER BY created_at DESC");
?>
<!doctype html><html><head><meta charset="utf-8"><title>Material Providers</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-4">
<a class="btn btn-outline-dark mb-3" href="/homeplan/client/provider_options.php">Back</a>
<h3>Material Providers</h3>
<?php while($r=mysqli_fetch_assoc($res)): ?>
  <div class="card mb-2"><div class="card-body">
    <b><?= htmlspecialchars($r['full_name']) ?></b> — <?= htmlspecialchars($r['city']??'') ?> — <?= htmlspecialchars($r['phone']??'') ?>
  </div></div>
<?php endwhile; ?>
</div></body></html>
