<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

// Must be logged in as provider
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'provider') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

if (!isset($_GET['request_id'])) {
    die("Request ID missing.");
}

$request_id = (int)$_GET['request_id'];

if ($request_id <= 0) {
    die("Invalid request id.");
}

// Load request (only if belongs to this provider)
$stmt = $pdo->prepare("
    SELECT
        r.request_id,
        r.status,
        r.creation_date,
        r.client_id,
        r.property_id,

        cu.full_name AS client_name,
        cu.email AS client_email,
        cu.phone AS client_phone,

        p.project_name,
        p.price,
        p.size_sqft,
        p.no_of_bedrooms,
        p.no_of_bathrooms,
        p.availability_status,
        p.created_at,

        l.street,
        l.city
    FROM requests r
    JOIN properties p ON p.property_id = r.property_id
    JOIN users cu ON cu.user_id = r.client_id
    JOIN locations l ON l.location_id = p.location_id
    WHERE r.request_id = ?
      AND r.request_type = 'property'
      AND p.provider_id = ?
    LIMIT 1
");
$stmt->execute([$request_id, $provider_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Request not found or you don't have permission.");
}

function badgeClass($s) {
    return match($s) {
        'accepted' => 'bg-success',
        'rejected' => 'bg-danger',
        default    => 'bg-warning text-dark'
    };
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request View</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">

  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Action saved.</div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0">Request #<?= (int)$row['request_id'] ?></h3>
    <span class="badge <?= badgeClass($row['status']) ?>">
      <?= htmlspecialchars($row['status']) ?>
    </span>
  </div>

  <div class="card p-4 mb-4">
    <h5 class="mb-3">Client Information</h5>
    <div><strong>Name:</strong> <?= htmlspecialchars($row['client_name']) ?></div>
    <div><strong>Email:</strong> <?= htmlspecialchars($row['client_email']) ?></div>
    <div><strong>Phone:</strong> <?= htmlspecialchars($row['client_phone']) ?></div>
  </div>

  <div class="card p-4 mb-4">
    <h5 class="mb-3">Property Information</h5>

    <div class="mb-2"><strong>Project:</strong> <?= htmlspecialchars($row['project_name']) ?></div>
    <div class="mb-2"><strong>Location:</strong> <?= htmlspecialchars($row['street'] . ', ' . $row['city']) ?></div>

    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <div><strong>Price:</strong> ৳ <?= number_format((float)$row['price']) ?></div>
        <div><strong>Size:</strong> <?= (int)$row['size_sqft'] ?> sqft</div>
      </div>
      <div class="col-md-6">
        <div><strong>Bedrooms:</strong> <?= (int)$row['no_of_bedrooms'] ?></div>
        <div><strong>Bathrooms:</strong> <?= (int)$row['no_of_bathrooms'] ?></div>
      </div>
    </div>

    <div class="mt-3">
      <div><strong>Availability:</strong> <?= htmlspecialchars($row['availability_status']) ?></div>
      <div class="text-muted small">Property posted: <?= htmlspecialchars($row['created_at']) ?></div>
    </div>
  </div>

  <div class="card p-4 mb-4">
    <h5 class="mb-3">Request Details</h5>
    <div><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></div>
    <div><strong>Requested On:</strong> <?= htmlspecialchars($row['creation_date']) ?></div>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="/homeplan/provider/requests.php">⬅ Back to Requests</a>

    <?php if ($row['status'] === 'pending'): ?>
      <form method="POST" action="/homeplan/provider/request_action.php" class="m-0">
        <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
        <input type="hidden" name="action" value="accept">
        <button class="btn btn-success">Accept</button>
      </form>

      <form method="POST" action="/homeplan/provider/request_action.php" class="m-0">
        <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
        <input type="hidden" name="action" value="reject">
        <button class="btn btn-danger">Reject</button>
      </form>
    <?php else: ?>
      <span class="text-muted align-self-center">This request is already <?= htmlspecialchars($row['status']) ?>.</span>
    <?php endif; ?>
  </div>

</div>

</body>
</html>
