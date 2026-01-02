<?php
// /homeplan/client/properties.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if (($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/index.php");
  exit;
}

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

      u.full_name AS owner_name,

      l.location_id,
      l.house,
      l.street,
      l.city,
      l.area_code

    FROM properties p
    JOIN users u       ON u.user_id = p.provider_id
    JOIN locations l   ON l.location_id = p.location_id

    WHERE p.availability_status = 'available'
    ORDER BY p.created_at DESC
  ";

  $res = $conn->query($sql);
  $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

} catch (Throwable $e) {
  $error = $e->getMessage();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Available Properties</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Available Properties</h3>
    <a class="btn btn-outline-secondary btn-sm" href="/homeplan/client/dashboard.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><b>Error:</b> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">No properties available right now.</div>
  <?php else: ?>

    <div class="table-responsive bg-white border rounded">
      <table class="table table-striped mb-0 align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Project</th>
            <th>Owner</th>
            <th>Location</th>
            <th>Size (sqft)</th>
            <th>Price</th>
            <th>Beds</th>
            <th>Baths</th>
            <th>Status</th>
            <th>Posted</th>
            <th style="width:120px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <?php
              $parts = [];
              if (!empty($r['house'])) $parts[] = $r['house'];
              if (!empty($r['street'])) $parts[] = $r['street'];
              if (!empty($r['city'])) $parts[] = $r['city'];
              $locText = implode(', ', $parts);

              if (!empty($r['area_code'])) {
                $locText .= " (" . $r['area_code'] . ")";
              }
              if ($locText === '') $locText = 'N/A';
            ?>
            <tr>
              <td><?= (int)$r['property_id'] ?></td>
              <td><?= htmlspecialchars($r['project_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['owner_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($locText) ?></td>
              <td><?= (int)($r['size_sqft'] ?? 0) ?></td>
              <td><?= number_format((float)($r['price'] ?? 0), 2) ?></td>
              <td><?= htmlspecialchars((string)($r['no_of_bedrooms'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($r['no_of_bathrooms'] ?? '-')) ?></td>
              <td><span class="badge bg-success"><?= htmlspecialchars($r['availability_status']) ?></span></td>
              <td>
                <?php
                  $dt = $r['created_at'] ?? '';
                  echo $dt ? htmlspecialchars(date('Y-m-d', strtotime($dt))) : '-';
                ?>
              </td>
              <td>
                <a class="btn btn-sm btn-primary"
                   href="/homeplan/client/property_view.php?id=<?= (int)$r['property_id'] ?>">
                  View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>

</div>
</body>
</html>

