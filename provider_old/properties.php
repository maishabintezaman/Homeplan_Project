<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'provider') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT p.property_id, p.project_name, p.price, p.size_sqft,
           p.no_of_bedrooms, p.no_of_bathrooms, p.availability_status,
           l.city, l.street, p.created_at
    FROM properties p
    JOIN locations l ON l.location_id = p.location_id
    WHERE p.provider_id = ?
    ORDER BY p.property_id DESC
");
$stmt->execute([$provider_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Properties</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0">My Properties</h3>
    <a class="btn btn-primary" href="/homeplan/provider/property_add.php">+ Add Property</a>
  </div>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
    <div class="alert alert-success">Property added successfully.</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success">Property updated successfully.</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success">Property deleted successfully.</div>
  <?php endif; ?>

  <?php if (!$rows): ?>
    <div class="alert alert-info">No properties yet. Click “Add Property”.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Project</th>
            <th>Location</th>
            <th>Size</th>
            <th>Bed/Bath</th>
            <th>Price</th>
            <th>Status</th>
            <th>Created</th>
            <th style="width:180px;">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['property_id'] ?></td>
            <td><?= htmlspecialchars($r['project_name']) ?></td>
            <td><?= htmlspecialchars($r['street'] . ', ' . $r['city']) ?></td>
            <td><?= (int)$r['size_sqft'] ?> sqft</td>
            <td><?= (int)$r['no_of_bedrooms'] ?> / <?= (int)$r['no_of_bathrooms'] ?></td>
            <td>৳ <?= number_format((float)$r['price']) ?></td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['availability_status']) ?></span></td>
            <td class="small"><?= htmlspecialchars($r['created_at']) ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="/homeplan/provider/property_edit.php?id=<?= (int)$r['property_id'] ?>">Edit</a>

              <form method="POST" action="/homeplan/provider/property_delete.php" class="d-inline"
                    onsubmit="return confirm('Delete this property?');">
                <input type="hidden" name="property_id" value="<?= (int)$r['property_id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <a class="btn btn-outline-secondary" href="/homeplan/provider/dashboard.php">⬅ Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
