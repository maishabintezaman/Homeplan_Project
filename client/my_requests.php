 <?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$client_id = (int)$_SESSION['user_id'];

// Fetch requests
$stmt = $conn->prepare("
    SELECT r.request_id, r.property_id, r.status, r.creation_date,
           p.project_name, p.price
    FROM requests r
    JOIN properties p ON p.property_id = r.property_id
    WHERE r.client_id = ? AND r.request_type = 'property'
    ORDER BY r.request_id DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function badgeClass($status) {
    return match ($status) {
        'accepted' => 'bg-success',
        'rejected' => 'bg-danger',
        default    => 'bg-warning text-dark', // pending
    };
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Requests</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">My Requests</h2>
    <a class="btn btn-outline-secondary" href="/homeplan/client/dashboard.php">Back</a>
  </div>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">No requests yet.</div>
  <?php else: ?>
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th style="width:120px;">Request ID</th>
              <th>Project</th>
              <th style="width:160px;">Price</th>
              <th style="width:140px;">Status</th>
              <th style="width:180px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= (int)$r['request_id'] ?></td>
                <td><?= htmlspecialchars($r['project_name'] ?? '-') ?></td>
                <td><?= number_format((float)($r['price'] ?? 0), 0) ?></td>
                <td>
                  <span class="badge <?= badgeClass($r['status']) ?>">
                    <?= htmlspecialchars(ucfirst($r['status'])) ?>
                  </span>
                </td>
                <td>
                  <a class="btn btn-outline-primary btn-sm"
                     href="/homeplan/client/property_view.php?id=<?= (int)$r['property_id'] ?>">
                    View Property
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

