<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /homeplan/auth/login.php");
    exit;
}

// allow these provider roles
$allowed = ['property_owner','developer','architect','material_provider','worker_provider','interior_designer','admin'];
if (!in_array(($_SESSION['role'] ?? ''), $allowed, true)) {
    header("Location: /homeplan/index.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT r.request_id, r.status, r.creation_date,
           p.property_id, p.project_name, p.price,
           u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone
    FROM request_assignments ra
    JOIN requests r ON r.request_id = ra.request_id
    JOIN properties p ON p.property_id = r.property_id
    JOIN users u ON u.user_id = r.client_id
    WHERE ra.provider_id = ?
    ORDER BY r.request_id DESC
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function badgeClass($status) {
    return match ($status) {
        'accepted' => 'bg-success',
        'rejected' => 'bg-danger',
        default    => 'bg-warning text-dark',
    };
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Property Requests</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Requests</h2>
    <a class="btn btn-outline-secondary" href="/homeplan/provider/dashboard.php">Back</a>
  </div>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">No requests found.</div>
  <?php else: ?>
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Request ID</th>
              <th>Project</th>
              <th>Client</th>
              <th>Status</th>
              <th>Date</th>
              <th style="width:160px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= (int)$r['request_id'] ?></td>
                <td><?= htmlspecialchars($r['project_name'] ?? '-') ?></td>
                <td>
                  <?= htmlspecialchars($r['client_name'] ?? '-') ?><br>
                  <small class="text-muted"><?= htmlspecialchars($r['client_email'] ?? '') ?></small>
                </td>
                <td>
                  <span class="badge <?= badgeClass($r['status']) ?>">
                    <?= htmlspecialchars(ucfirst($r['status'])) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars((string)$r['creation_date']) ?></td>
                <td>
                  <a class="btn btn-outline-primary btn-sm"
                     href="/homeplan/provider/request_view.php?id=<?= (int)$r['request_id'] ?>">
                    View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>
</body>
</html>
