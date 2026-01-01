<?php
// /properties/property_details.php
session_start();
require_once __DIR__ . '/../config/db.php';

$property_id = (int)($_GET['id'] ?? 0);
if ($property_id <= 0) {
    http_response_code(400);
    echo "Invalid property id.";
    exit;
}

$sql = "
SELECT 
  p.*,
  u.name  AS owner_name,
  u.phone AS owner_phone,
  l.name  AS location_name,
  l.area  AS location_area,
  l.city  AS location_city
FROM properties p
JOIN users u     ON u.user_id = p.provider_id
JOIN locations l ON l.location_id = p.location_id
WHERE p.property_id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    http_response_code(404);
    echo "Property not found.";
    exit;
}

// Optional: request status for logged-in client
$user_id = (int)($_SESSION['user_id'] ?? 0);
$role    = (string)($_SESSION['role'] ?? '');

$request_status = null;
$already_requested = false;

if ($user_id > 0 && $role === 'client') {
    $q = $conn->prepare("SELECT status FROM requests WHERE property_id=? AND client_id=? ORDER BY request_id DESC LIMIT 1");
    $q->bind_param("ii", $property_id, $user_id);
    $q->execute();
    $rr = $q->get_result()->fetch_assoc();
    if ($rr) {
        $request_status = $rr['status'];
        $already_requested = true;
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Property Details</title>
    <style>
        body{font-family:Arial, sans-serif;background:#f6f7fb;margin:0;padding:24px;}
        .card{max-width:720px;margin:0 auto;background:#fff;border:1px solid #ddd;border-radius:10px;padding:24px;}
        .row{margin:10px 0;}
        .label{font-weight:700;}
        .badge{display:inline-block;padding:6px 10px;border-radius:12px;font-size:12px;font-weight:700;}
        .badge-ok{background:#2e7d32;color:#fff;}
        .badge-warn{background:#fbc02d;color:#000;}
        .btn{display:inline-block;padding:10px 14px;border-radius:8px;border:1px solid #999;text-decoration:none;background:#fff;color:#000;}
        .btn-disabled{opacity:.5;pointer-events:none;}
    </style>
</head>
<body>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h2 style="margin:0;">Property Details</h2>
        <a class="btn" href="/dashboard.php">Back</a>
    </div>

    <h1 style="margin-top:10px;"><?= htmlspecialchars($row['title'] ?? $row['name'] ?? 'Property') ?></h1>

    <div class="row"><span class="label">Property ID:</span> <?= (int)$row['property_id'] ?></div>
    <div class="row"><span class="label">Size:</span> <?= htmlspecialchars((string)($row['size_sqft'] ?? $row['size'] ?? '')) ?> sqft</div>
    <div class="row"><span class="label">Price:</span> <?= htmlspecialchars((string)($row['price'] ?? '')) ?></div>
    <div class="row"><span class="label">Beds:</span> <?= htmlspecialchars((string)($row['beds'] ?? '')) ?></div>
    <div class="row"><span class="label">Baths:</span> <?= htmlspecialchars((string)($row['baths'] ?? '')) ?></div>

    <div class="row">
        <span class="label">Status:</span>
        <span class="badge badge-ok"><?= htmlspecialchars((string)$row['status']) ?></span>
    </div>

    <!-- ✅ CHANGED: provider_id -> owner_name -->
    <div class="row">
        <span class="label">Owner:</span>
        <?= htmlspecialchars($row['owner_name'] ?? '') ?>
        <?php if (!empty($row['owner_phone'])): ?>
            (<?= htmlspecialchars($row['owner_phone']) ?>)
        <?php endif; ?>
    </div>

    <!-- ✅ CHANGED: location_id -> location text -->
    <div class="row">
        <span class="label">Location:</span>
        <?= htmlspecialchars($row['location_name'] ?? '') ?>
        <?php
          $extra = trim(($row['location_area'] ?? '') . ', ' . ($row['location_city'] ?? ''), ", ");
          if ($extra !== '') echo " (" . htmlspecialchars($extra) . ")";
        ?>
    </div>

    <div class="row"><span class="label">Posted:</span> <?= htmlspecialchars((string)($row['posted_at'] ?? $row['posted'] ?? '')) ?></div>

    <hr>

    <div class="row">
        <span class="label">Your Request Status:</span>
        <?php if ($already_requested): ?>
            <span class="badge badge-warn"><?= htmlspecialchars($request_status) ?></span>
        <?php else: ?>
            <span class="badge">None</span>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if ($role === 'client'): ?>
            <?php if ($already_requested): ?>
                <a class="btn btn-disabled" href="javascript:void(0)">Already Requested</a>
            <?php else: ?>
                <a class="btn" href="/requests/create_request.php?property_id=<?= (int)$row['property_id'] ?>">Request</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
