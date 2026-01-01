<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$allowed = ['property_owner','developer','architect','material_provider','worker_provider','interior_designer','admin'];
if (!in_array(($_SESSION['role'] ?? ''), $allowed, true)) {
    header("Location: /homeplan/index.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];
$request_id  = (int)($_GET['id'] ?? 0);

if ($request_id <= 0) {
    header("Location: /homeplan/provider/requests.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT r.request_id, r.status, r.creation_date,
           p.property_id, p.project_name, p.price, p.availability_status,
           u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone
    FROM request_assignments ra
    JOIN requests r ON r.request_id = ra.request_id
    JOIN properties p ON p.property_id = r.property_id
    JOIN users u ON u.user_id = r.client_id
    WHERE ra.provider_id = ? AND r.request_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $provider_id, $request_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header("Location: /homeplan/provider/requests.php?msg=not_found");
    exit;
}

function badgeClass($status) {
    return match ($status) {
        'accepted' => 'bg-success',
        'rejected' => 'bg-danger',
        default    => 'bg-warning text-dark',
    };
}

$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request View</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Request #<?= (int)$row['request_id'] ?></h2>
    <a class="btn btn-outline-secondary" href="/homeplan/provider/requests.php">Back</a>
  </div>

  <?php if ($msg === 'updated'): ?>
    <div class="alert alert-success">Request updated successfully.</div>
  <?php elseif ($msg === 'already'): ?>
    <div class="alert alert-warning">This request was already processed.</div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6"><b>Project:</b> <?= htmlspecialchars($row['project_name']) ?></div>
        <div class="col-md-6"><b>Price:</b> <?= number_format((float)$row['price'], 0) ?></div>

        <div class="col-md-6"><b>Client:</b> <?= htmlspecialchars($row['client_name']) ?></div>
        <div class="col-md-6"><b>Client Phone:</b> <?= htmlspecialchars($row['client_phone'] ?? '-') ?></div>

        <div class="col-md-6">
          <b>Status:</b>
          <span class="badge <?= badgeClass($row['status']) ?>">
            <?= htmlspecialchars(ucfirst($row['status'])) ?>
          </span>
        </div>

        <div class="col-md-6"><b>Requested At:</b> <?= htmlspecialchars((string)$row['creation_date']) ?></div>
      </div>

      <hr class="my-4">

      <?php if ($row['status'] === 'pending'): ?>
        <form method="post" action="/homeplan/provider/request_action.php" class="d-flex gap-2">
          <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
          <button name="action" value="accepted" class="btn btn-success"
                  onclick="return confirm('Accept this request?');">
            Accept
          </button>
          <button name="action" value="rejected" class="btn btn-danger"
                  onclick="return confirm('Reject this request?');">
            Reject
          </button>
        </form>
      <?php else: ?>
        <div class="alert alert-info mb-0">
          This request is already <b><?= htmlspecialchars($row['status']) ?></b>.
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>
</body>
</html>
