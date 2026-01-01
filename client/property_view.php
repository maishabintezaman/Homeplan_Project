<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

/* ---------- Auth check ---------- */
if (empty($_SESSION['user_id'])) {
    header("Location: /homeplan/auth/login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/index.php");
    exit;
}

/* ---------- Get & validate property id ---------- */
$property_id = (int)($_GET['id'] ?? 0);
if ($property_id <= 0) {
    echo "Invalid property ID.";
    exit;
}

/* ---------- Fetch property with owner + location ---------- */
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

    u.full_name  AS owner_name,
    u.phone      AS owner_phone,

    l.house      AS location_house,
    l.street     AS location_street,
    l.city       AS location_city,
    l.area_code  AS location_area_code

FROM properties p
JOIN users u       ON u.user_id = p.provider_id
JOIN locations l   ON l.location_id = p.location_id

WHERE p.property_id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Prepare failed: " . htmlspecialchars($conn->error);
    exit;
}
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Property not found.";
    exit;
}

/* ---------- Build nice location text ---------- */
$locParts = [];
if (!empty($row['location_house']))  $locParts[] = $row['location_house'];
if (!empty($row['location_street'])) $locParts[] = $row['location_street'];
if (!empty($row['location_city']))   $locParts[] = $row['location_city'];

$location_text = implode(', ', $locParts);
if (!empty($row['location_area_code'])) {
    $location_text .= " (" . $row['location_area_code'] . ")";
}
if ($location_text === '') {
    $location_text = 'N/A';
}

/* ---------- Check client request status ---------- */
$client_id = (int)$_SESSION['user_id'];
$request_status = null;

$q = $conn->prepare("
    SELECT status
    FROM requests
    WHERE property_id = ? AND client_id = ?
    ORDER BY request_id DESC
    LIMIT 1
");
if ($q) {
    $q->bind_param("ii", $property_id, $client_id);
    $q->execute();
    $req = $q->get_result()->fetch_assoc();
    if ($req) {
        $request_status = $req['status'];
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Property Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Property Details</h3>
        <a href="/homeplan/client/properties.php" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="card-title mb-3">
                <?= htmlspecialchars($row['project_name'] ?? 'Property') ?>
            </h4>

            <table class="table table-bordered">
                <tr>
                    <th style="width:200px;">Property ID</th>
                    <td><?= (int)$row['property_id'] ?></td>
                </tr>

                <tr>
                    <th>Owner</th>
                    <td>
                        <?= htmlspecialchars($row['owner_name'] ?? '-') ?>
                        <?php if (!empty($row['owner_phone'])): ?>
                            <span class="text-muted">(<?= htmlspecialchars($row['owner_phone']) ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th>Location</th>
                    <td><?= htmlspecialchars($location_text) ?></td>
                </tr>

                <tr>
                    <th>Size</th>
                    <td><?= (int)($row['size_sqft'] ?? 0) ?> sqft</td>
                </tr>

                <tr>
                    <th>Price</th>
                    <td><?= number_format((float)($row['price'] ?? 0), 2) ?></td>
                </tr>

                <tr>
                    <th>Bedrooms</th>
                    <td><?= htmlspecialchars((string)($row['no_of_bedrooms'] ?? '-')) ?></td>
                </tr>

                <tr>
                    <th>Bathrooms</th>
                    <td><?= htmlspecialchars((string)($row['no_of_bathrooms'] ?? '-')) ?></td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge bg-success">
                            <?= htmlspecialchars($row['availability_status'] ?? '-') ?>
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Posted</th>
                    <td>
                        <?php
                        $dt = $row['created_at'] ?? '';
                        echo $dt ? htmlspecialchars(date('Y-m-d', strtotime($dt))) : '-';
                        ?>
                    </td>
                </tr>
            </table>

            <hr>

            <!-- Request section -->
            <div>
                <strong>Your Request Status:</strong>
                <?php if ($request_status): ?>
                    <span class="badge bg-warning text-dark">
                        <?= htmlspecialchars($request_status) ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">Not requested</span>
                <?php endif; ?>
            </div>

            <div class="mt-3">
                <?php if (!$request_status): ?>
                    <a href="/homeplan/client/request_property.php?id=<?= (int)$row['property_id'] ?>"
                       class="btn btn-primary">
                        Send Request
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        Already Requested
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

</body>
</html>

