<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/db.php'; // mysqli $conn

$client_id    = (int)$_SESSION['user_id'];
$developer_id = (int)($_GET['developer_id'] ?? $_POST['developer_id'] ?? 0);
if ($developer_id <= 0) {
  http_response_code(400);
  die("Invalid developer");
}

/* Load developer */
$devStmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE user_id=? AND role='developer' LIMIT 1");
$devStmt->bind_param("i", $developer_id);
$devStmt->execute();
$dev = $devStmt->get_result()->fetch_assoc();
$devStmt->close();

if (!$dev) {
  http_response_code(404);
  die("Developer not found");
}

/* Flags for modal/alert */
$success = (($_GET['success'] ?? '') === '1');
$error   = (($_GET['error'] ?? '') === '1');

/* Handle POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $area_unit      = trim($_POST['area_unit'] ?? 'sqft');
  $area_value     = (float)($_POST['area_value'] ?? 0);
  $location_text  = trim($_POST['location_text'] ?? '');
  $asking_price   = (float)($_POST['asking_price'] ?? 0);
  $road_width     = (trim($_POST['road_width'] ?? '') === '') ? null : (float)$_POST['road_width'];
  $ownership_type = trim($_POST['ownership_type'] ?? '');
  $notes          = trim($_POST['notes'] ?? '');

  if ($location_text === '' || $area_value <= 0 || $asking_price <= 0) {
    header("Location: /homeplan/client/developer_land_request.php?developer_id={$developer_id}&error=1");
    exit;
  }

  /* Use transaction: requests + details + notification */
  $conn->begin_transaction();

  try {
    // 1) Insert into requests (matches your current requests table)
    $road_width_in = trim($_POST['road_width'] ?? ''); // keep empty => NULL

$reqStmt = $conn->prepare("
  INSERT INTO requests
    (client_id, provider_id, property_id, request_type, status,
     area_unit, area_value, location_text, asking_price, road_width, ownership_type, notes)
  VALUES
    (?, ?, NULL, 'developer_land', 'pending',
     ?, ?, ?, ?, NULLIF(?, ''), ?, ?)
");

$reqStmt->bind_param(
  "iisdsdsss",
  $client_id,
  $developer_id,
  $area_unit,
  $area_value,
  $location_text,
  $asking_price,
  $road_width_in,
  $ownership_type,
  $notes
);

$reqStmt->execute();
$request_id = (int)$reqStmt->insert_id;
$reqStmt->close();


    // 2) Insert land details into developer_land_requests (Option A table)
    $detStmt = $conn->prepare("
      INSERT INTO developer_land_requests
        (request_id, developer_id, area_unit, area_value, location_text, asking_price, road_width, ownership_type, notes)
      VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // road_width can be NULL -> use "d" but pass null is OK in mysqli when variable is null
    $detStmt->bind_param(
      "iisdsddss",
      $request_id,
      $developer_id,
      $area_unit,
      $area_value,
      $location_text,
      $asking_price,
      $road_width,
      $ownership_type,
      $notes
    );
    $detStmt->execute();
    $detStmt->close();

    // 3) Notification to developer
    $msg = "New land request received (Request ID #{$request_id})";
    $nStmt = $conn->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
    $nStmt->bind_param("is", $developer_id, $msg);
    $nStmt->execute();
    $nStmt->close();

    $conn->commit();

    header("Location: /homeplan/client/developer_land_request.php?developer_id={$developer_id}&success=1");
    exit;

  } catch (Throwable $e) {
    $conn->rollback();
    // Optional: show debug in dev
    // die("Error: " . htmlspecialchars($e->getMessage()));
    header("Location: /homeplan/client/developer_land_request.php?developer_id={$developer_id}&error=1");
    exit;
  }
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request Land</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<div class="container py-4" style="max-width:800px;">

  <a href="/homeplan/client/developer_projects.php?developer_id=<?= (int)$developer_id ?>"
     class="btn btn-outline-dark mb-3">Back</a>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      ❌ <strong>Request failed!</strong> Please try again.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-1">Request land to: <?= htmlspecialchars($dev['full_name']) ?></h4>
      <p class="text-muted mb-3">Fill the land details below</p>

      <form method="post" action="/homeplan/client/developer_land_request.php">
        <input type="hidden" name="developer_id" value="<?= (int)$developer_id ?>">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Area Unit</label>
            <select name="area_unit" class="form-select" required>
              <option value="katha">Katha</option>
              <option value="sqft" selected>Sqft</option>
              <option value="sqm">Sqm</option>
              <option value="decimal">Decimal</option>
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label">Area</label>
            <input name="area_value" type="number" step="0.01" class="form-control" required>
          </div>

          <div class="col-12">
            <label class="form-label">Location</label>
            <input name="location_text" class="form-control" required placeholder="Area, City, Details">
          </div>

          <div class="col-md-6">
            <label class="form-label">Asking Price (BDT)</label>
            <input name="asking_price" type="number" step="0.01" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Road Width (ft) (optional)</label>
            <input name="road_width" type="number" step="0.01" class="form-control">
          </div>

          <div class="col-12">
            <label class="form-label">Ownership Type (optional)</label>
            <input name="ownership_type" class="form-control" placeholder="Single / Joint / Others">
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="4" placeholder="Any extra details..."></textarea>
          </div>
        </div>

        <button class="btn btn-success w-100 mt-4">Send Request</button>
      </form>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✅ Request Sent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Your land request was sent successfully. The developer will contact you soon.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($success): ?>
<script>
  new bootstrap.Modal(document.getElementById('successModal')).show();
</script>
<?php endif; ?>

</body>
</html>



