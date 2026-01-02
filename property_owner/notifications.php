<?php
// /homeplan/property_owner/notifications.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

/* ---------- Auth ---------- */
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'property_owner') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$provider_id = (int)$_SESSION['user_id'];

/* ---------- Column detector helpers (NO prepared SHOW) ---------- */
function table_has_column(mysqli $conn, string $table, string $col): bool {
  // table/col are from our own fixed candidate lists, not user input
  $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
  $col   = preg_replace('/[^a-zA-Z0-9_]/', '', $col);

  $sql = "SHOW COLUMNS FROM `$table` LIKE '" . $conn->real_escape_string($col) . "'";
  $res = $conn->query($sql);
  return $res && $res->num_rows > 0;
}

function pick_col(mysqli $conn, string $table, array $candidates): ?string {
  foreach ($candidates as $c) {
    if (table_has_column($conn, $table, $c)) return $c;
  }
  return null;
}

/* ---------- Detect columns we need ---------- */
$propertyTitleCol = pick_col($conn, 'properties', [
  'project_name','title','name','property_name','property_title'
]);

$userNameCol = pick_col($conn, 'users', [
  'name','full_name','fullname','username','user_name','client_name','first_name','email'
]);

$clientIdCol = pick_col($conn, 'requests', [
  'client_id','user_id','requester_id'
]);

if (!$propertyTitleCol) {
  die("DB Error: In `properties`, couldn't find a title column. Expected one of: project_name/title/name/property_name/property_title");
}
if (!$userNameCol) {
  die("DB Error: In `users`, couldn't find a name-like column. Expected one of: full_name/username/user_name/first_name/email etc.");
}
if (!$clientIdCol) {
  die("DB Error: In `requests`, couldn't find client id column. Expected one of: client_id/user_id/requester_id");
}

/* If first_name exists and last_name exists, display full name */
$showClientExpr = "u.`$userNameCol`";
if ($userNameCol === 'first_name' && table_has_column($conn, 'users', 'last_name')) {
  $showClientExpr = "TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')))";
}

/* ---------- Fetch Notifications ---------- */
$sql = "
  SELECT
    r.request_id,
    r.status,
    COALESCE(NULLIF(TRIM(p.`$propertyTitleCol`), ''), CONCAT('Property #', p.property_id)) AS property_title,
    p.price,
    p.size_sqft,
    COALESCE(NULLIF($showClientExpr, ''), CONCAT('Client #', r.`$clientIdCol`)) AS client_name
  FROM requests r
  JOIN properties p ON p.property_id = r.property_id
  JOIN users u ON u.user_id = r.`$clientIdCol`
  WHERE p.provider_id = ?
  ORDER BY r.request_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../includes/topbar.php'; ?>

<div class="container py-4" style="max-width:900px;">
  <h4 class="mb-3">Property Requests</h4>

  <?php if (!$result || $result->num_rows === 0): ?>
    <div class="alert alert-info">No notifications yet.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
          $status = strtolower((string)$row['status']);
          $badge = 'bg-secondary';
          if ($status === 'pending') $badge = 'bg-warning';
          elseif ($status === 'accepted' || $status === 'approved') $badge = 'bg-success';
          elseif ($status === 'rejected' || $status === 'declined') $badge = 'bg-danger';
        ?>
        <div class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <strong><?= htmlspecialchars((string)$row['client_name']) ?></strong>
            requested
            <strong><?= htmlspecialchars((string)$row['property_title']) ?></strong>

            <div class="small text-muted">
              <?php if (!empty($row['size_sqft'])): ?>
                Size: <?= (int)$row['size_sqft'] ?> sqft
              <?php endif; ?>
              <?php if (isset($row['price'])): ?>
                <?= !empty($row['size_sqft']) ? ' | ' : '' ?>
                Price: <?= htmlspecialchars((string)$row['price']) ?>
              <?php endif; ?>
              <span class="ms-2">Request ID: <?= (int)$row['request_id'] ?></span>
            </div>
          </div>

          <span class="badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>



