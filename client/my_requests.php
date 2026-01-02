<?php
// /homeplan/client/my_requests.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$client_id = (int)$_SESSION['user_id'];

/* -------------------- Helpers -------------------- */
function table_exists(mysqli $conn, string $table): bool {
  $sql = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1";
  $st = $conn->prepare($sql);
  $st->bind_param("s", $table);
  $st->execute();
  $r = $st->get_result()->fetch_row();
  return !empty($r);
}

function table_has_column(mysqli $conn, string $table, string $col): bool {
  $sql = "SELECT 1
          FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
          LIMIT 1";
  $st = $conn->prepare($sql);
  $st->bind_param("ss", $table, $col);
  $st->execute();
  $r = $st->get_result()->fetch_row();
  return !empty($r);
}

function pick_col(mysqli $conn, string $table, array $candidates): ?string {
  foreach ($candidates as $c) {
    if (table_has_column($conn, $table, $c)) return $c;
  }
  return null;
}

function safe_dt($v): int {
  if (!$v) return 0;
  $t = strtotime($v);
  return $t ? $t : 0;
}

/* -------------------- Collect requests from multiple tables -------------------- */
$all = [];

/* ---------- 1) Architect Requests ---------- */
if (table_exists($conn, 'architect_requests')) {

  // detect columns
  $reqIdCol     = pick_col($conn, 'architect_requests', ['request_id', 'id']);
  $clientCol    = pick_col($conn, 'architect_requests', ['client_user_id','client_id','clientId']);
  $archCol      = pick_col($conn, 'architect_requests', ['architect_user_id','architect_id','architectId']);
  $typeCol      = pick_col($conn, 'architect_requests', ['project_type','type','service_type']);
  $locCol       = pick_col($conn, 'architect_requests', ['location','project_location','address','area']);
  $statusCol    = pick_col($conn, 'architect_requests', ['status','request_status']);
  $createdCol   = pick_col($conn, 'architect_requests', ['created_at','created','createdOn','sent_at']);

  // details col (optional)
  $detailCandidates = ['details', 'description', 'message', 'requirements', 'notes', 'note'];
  $detailsCol = pick_col($conn, 'architect_requests', $detailCandidates);

  // minimal requirements to query
  if ($clientCol && $archCol && $statusCol) {
    $select = [];
    $select[] = $reqIdCol ? "ar.`$reqIdCol` AS request_id" : "NULL AS request_id";
    $select[] = $typeCol ? "ar.`$typeCol` AS req_type" : "'' AS req_type";
    $select[] = $locCol ? "ar.`$locCol` AS location" : "'' AS location";
    $select[] = $detailsCol ? "ar.`$detailsCol` AS details" : "'' AS details";
    $select[] = "ar.`$statusCol` AS status";
    $select[] = $createdCol ? "ar.`$createdCol` AS created_at" : "NULL AS created_at";
    $select[] = "u.full_name AS to_name";
    $select[] = "u.email AS to_email";

    $sql = "
      SELECT " . implode(",\n", $select) . "
      FROM architect_requests ar
      JOIN users u ON u.user_id = ar.`$archCol`
      WHERE ar.`$clientCol` = ?
      ORDER BY " . ($reqIdCol ? "ar.`$reqIdCol`" : "created_at") . " DESC
    ";

    $st = $conn->prepare($sql);
    $st->bind_param("i", $client_id);
    $st->execute();
    $rs = $st->get_result();

    while ($r = $rs->fetch_assoc()) {
      $r['kind'] = 'Architect';
      $all[] = $r;
    }
  }
}

/* ---------- 2) Property Requests (usually table: requests) ---------- */
if (table_exists($conn, 'requests')) {

  $reqIdCol   = pick_col($conn, 'requests', ['request_id','id']);
  $clientCol  = pick_col($conn, 'requests', ['client_id','client_user_id','clientId']);
  $propIdCol  = pick_col($conn, 'requests', ['property_id','propertyId']);
  // you renamed provider_id -> owner_id in some places; support both
  $ownerCol   = pick_col($conn, 'requests', ['owner_id','provider_id','property_owner_id','ownerId']);
  $statusCol  = pick_col($conn, 'requests', ['status','request_status']);
  $createdCol = pick_col($conn, 'requests', ['created_at','created','sent_at']);

  // properties table joins (for property name/location)
  $propNameCol = table_exists($conn, 'properties') ? pick_col($conn, 'properties', ['title','project_name','name']) : null;
  $propLocCol  = table_exists($conn, 'properties') ? pick_col($conn, 'properties', ['location','address','area']) : null;

  if ($clientCol && $statusCol) {
    $select = [];
    $select[] = $reqIdCol ? "r.`$reqIdCol` AS request_id" : "NULL AS request_id";
    $select[] = "'Property' AS req_type";
    // location: prefer properties.location if joinable; else requests.location if exists
    $reqLocCol = pick_col($conn, 'requests', ['location','address','area']);
    if ($propLocCol && $propIdCol && table_exists($conn,'properties')) {
      $select[] = "COALESCE(p.`$propLocCol`, '') AS location";
    } elseif ($reqLocCol) {
      $select[] = "r.`$reqLocCol` AS location";
    } else {
      $select[] = "'' AS location";
    }

    // details: show property title/name if possible
    $reqDetailsCol = pick_col($conn, 'requests', ['details','message','note','notes','description']);
    if ($propNameCol && $propIdCol && table_exists($conn,'properties')) {
      $select[] = "CONCAT('Property: ', COALESCE(p.`$propNameCol`, '')) AS details";
    } elseif ($reqDetailsCol) {
      $select[] = "r.`$reqDetailsCol` AS details";
    } else {
      $select[] = "'' AS details";
    }

    $select[] = "r.`$statusCol` AS status";
    $select[] = $createdCol ? "r.`$createdCol` AS created_at" : "NULL AS created_at";

    // send-to name/email (owner if exists; if not, blank)
    if ($ownerCol) {
      $select[] = "u.full_name AS to_name";
      $select[] = "u.email AS to_email";
    } else {
      $select[] = "'' AS to_name";
      $select[] = "'' AS to_email";
    }

    $from = "FROM requests r";
    $joins = [];

    if (table_exists($conn,'properties') && $propIdCol) {
      $joins[] = "LEFT JOIN properties p ON p.property_id = r.`$propIdCol`";
    }

    if ($ownerCol) {
      $joins[] = "LEFT JOIN users u ON u.user_id = r.`$ownerCol`";
    }

    $sql = "
      SELECT " . implode(",\n", $select) . "
      $from
      " . implode("\n", $joins) . "
      WHERE r.`$clientCol` = ?
      ORDER BY " . ($reqIdCol ? "r.`$reqIdCol`" : "created_at") . " DESC
    ";

    $st = $conn->prepare($sql);
    $st->bind_param("i", $client_id);
    $st->execute();
    $rs = $st->get_result();

    while ($r = $rs->fetch_assoc()) {
      $r['kind'] = 'Property';
      // normalize req_type shown in UI
      $r['req_type'] = 'Property';
      $all[] = $r;
    }
  }
}

/* ---------- 3) Land Requests (try common table names) ---------- */
$landTables = ['land_requests','developer_land_requests','developer_land_request','land_request'];
$landTable = null;
foreach ($landTables as $t) {
  if (table_exists($conn, $t)) { $landTable = $t; break; }
}

if ($landTable) {
  $reqIdCol   = pick_col($conn, $landTable, ['request_id','id']);
  $clientCol  = pick_col($conn, $landTable, ['client_id','client_user_id']);
  $devCol     = pick_col($conn, $landTable, ['developer_id','developer_user_id','developerId']);
  $locCol     = pick_col($conn, $landTable, ['location','address','area']);
  $statusCol  = pick_col($conn, $landTable, ['status','request_status']);
  $createdCol = pick_col($conn, $landTable, ['created_at','created','sent_at']);
  $sizeCol    = pick_col($conn, $landTable, ['land_size','size','area_sqft','sqft']);
  $budgetCol  = pick_col($conn, $landTable, ['budget','price','max_budget']);

  if ($clientCol && $statusCol) {
    $select = [];
    $select[] = $reqIdCol ? "lr.`$reqIdCol` AS request_id" : "NULL AS request_id";
    $select[] = "'Land' AS req_type";
    $select[] = $locCol ? "lr.`$locCol` AS location" : "'' AS location";

    // details build
    $parts = [];
    if ($sizeCol)   $parts[] = "CONCAT('Size: ', lr.`$sizeCol`)";
    if ($budgetCol) $parts[] = "CONCAT('Budget: ', lr.`$budgetCol`)";
    if (!empty($parts)) {
      $select[] = "CONCAT(" . implode(", ' | ', ", $parts) . ") AS details";
    } else {
      $detailsCol = pick_col($conn, $landTable, ['details','message','note','notes','description']);
      $select[] = $detailsCol ? "lr.`$detailsCol` AS details" : "'' AS details";
    }

    $select[] = "lr.`$statusCol` AS status";
    $select[] = $createdCol ? "lr.`$createdCol` AS created_at" : "NULL AS created_at";

    if ($devCol) {
      $select[] = "u.full_name AS to_name";
      $select[] = "u.email AS to_email";
    } else {
      $select[] = "'' AS to_name";
      $select[] = "'' AS to_email";
    }

    $sql = "
      SELECT " . implode(",\n", $select) . "
      FROM `$landTable` lr
      " . ($devCol ? "LEFT JOIN users u ON u.user_id = lr.`$devCol`" : "") . "
      WHERE lr.`$clientCol` = ?
      ORDER BY " . ($reqIdCol ? "lr.`$reqIdCol`" : "created_at") . " DESC
    ";

    $st = $conn->prepare($sql);
    $st->bind_param("i", $client_id);
    $st->execute();
    $rs = $st->get_result();

    while ($r = $rs->fetch_assoc()) {
      $r['kind'] = 'Land';
      $all[] = $r;
    }
  }
}

/* -------------------- Sort combined list by created_at (desc) -------------------- */
usort($all, function($a, $b) {
  $ta = safe_dt($a['created_at'] ?? '');
  $tb = safe_dt($b['created_at'] ?? '');
  return $tb <=> $ta;
});
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Requests</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<?php
$navPath = __DIR__ . '/../partials/navbar.php';
if (file_exists($navPath)) require_once $navPath;
?>

<div class="container py-4" style="max-width: 1050px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Requests</h3>
    <a class="btn btn-outline-secondary" href="/homeplan/client/dashboard.php">Back</a>
  </div>

  <?php if (count($all) === 0): ?>
    <div class="alert alert-info">No requests yet.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php foreach ($all as $r): ?>
        <?php
          $status = strtolower($r['status'] ?? 'pending');
          $badge = $status === 'accepted' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
          $kind = $r['kind'] ?? ($r['req_type'] ?? 'Request');
        ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-bold">
                <?= htmlspecialchars($r['to_name'] ?? '—') ?>
                <span class="badge text-bg-secondary ms-2"><?= htmlspecialchars($kind) ?></span>
              </div>
              <div class="text-muted small"><?= htmlspecialchars($r['to_email'] ?? '') ?></div>
            </div>
            <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
          </div>

          <div class="mt-2">
            <div><b>Type:</b> <?= htmlspecialchars($r['req_type'] ?? $kind) ?></div>
            <div><b>Location:</b> <?= htmlspecialchars($r['location'] ?? '') ?></div>

            <?php if (!empty($r['details'])): ?>
              <div class="mt-1"><b>Details:</b><br><?= nl2br(htmlspecialchars($r['details'])) ?></div>
            <?php endif; ?>

            <div class="text-muted small mt-2">Sent: <?= htmlspecialchars($r['created_at'] ?? '') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>

