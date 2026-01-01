<?php
// /homeplan/property_owner/my_properties.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'property_owner') {
  header("Location: /homeplan/index.php");
  exit;
}

$owner_id = (int)$_SESSION['user_id'];

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

$rows = [];
$error = '';

try {
  $sql = "
    SELECT
      p.property_id,
      p.project_name,
      p.size_sqft,
      p.price,
      p.no_of_bedrooms,
      p.no_of_bathrooms,
      p.availability_status,
      p.created_at,
      l.house, l.street, l.city, l.area_code
    FROM properties p
    JOIN locations l ON l.location_id = p.location_id
    WHERE p.provider_id = ?
    ORDER BY p.property_id DESC
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $owner_id);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
  $error = $e->getMessage();
}

function loc_label($r) {
  $parts = [];
  if (!empty($r['house'])) $parts[] = $r['house'];
  if (!empty($r['street'])) $parts[] = $r['street'];
  if (!empty($r['city'])) $parts[] = $r['city'];
  $txt = implode(', ', $parts);
  if (!empty($r['area_code'])) $txt .= " (" . $r['area_code'] . ")";
  return $txt ?: '—';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Properties</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Properties</h3>
    <a class="btn btn-outline-secondary btn-sm" href="/homeplan/property_owner/dashboard.php">Back</a>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if (!empty($_GET['err'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['err']) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger"><b>Error:</b> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body table-responsive">
      <?php if (!$rows): ?>
        <div class="alert alert-info mb-0">No properties found.</div>
      <?php else: ?>
        <table class="table table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>Location</th>
              <th>Size</th>
              <th>Price</th>
              <th>Beds</th>
              <th>Baths</th>
              <th>Status</th>
              <th>Posted</th>
              <th style="width:160px;">Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars(loc_label($r)) ?></td>
              <td><?= (int)$r['size_sqft'] ?> sqft</td>
              <td><?= number_format((float)$r['price'], 2) ?></td>
              <td><?= (int)$r['no_of_bedrooms'] ?></td>
              <td><?= (int)$r['no_of_bathrooms'] ?></td>
              <td>
                <span class="badge bg-success"><?= htmlspecialchars($r['availability_status']) ?></span>
              </td>
              <td><?= htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))) ?></td>
              <td>
                <form method="post" action="/homeplan/property_owner/delete_property.php"
                      onsubmit="return confirm('Are you sure you want to remove this property?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="property_id" value="<?= (int)$r['property_id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

</div>
</body>
</html>

