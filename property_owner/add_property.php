<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

/* ---------- Auth ---------- */
if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if (($_SESSION['role'] ?? '') !== 'property_owner') {
  header("Location: /homeplan/index.php");
  exit;
}

$owner_id = (int)$_SESSION['user_id'];

$error = '';
$success = '';

/* ---------- Load locations for dropdown ---------- */
$locations = [];
try {
  $sqlLoc = "
    SELECT location_id, house, street, city, area_code
    FROM locations
    ORDER BY location_id DESC
  ";
  $resLoc = $conn->query($sqlLoc);
  $locations = $resLoc ? $resLoc->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $e) {
  $error = $e->getMessage();
}

/* ---------- Defaults / keep form values after submit ---------- */
$project_name     = '';
$size_sqft        = '';
$price            = '';
$no_of_bedrooms   = '0';
$no_of_bathrooms  = '0';
$availability     = 'available';

$location_mode    = 'existing';
$location_id      = 0;

$house            = '';
$street           = '';
$city             = '';
$area_code        = '';

/* ---------- Handle POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // property fields
  $project_name     = trim($_POST['project_name'] ?? '');
  $size_sqft        = (int)($_POST['size_sqft'] ?? 0);
  $price            = (float)($_POST['price'] ?? 0);
  $no_of_bedrooms   = (int)($_POST['no_of_bedrooms'] ?? 0);
  $no_of_bathrooms  = (int)($_POST['no_of_bathrooms'] ?? 0);
  $availability     = trim($_POST['availability_status'] ?? 'available');

  // location choice
  $location_mode    = trim($_POST['location_mode'] ?? 'existing'); // existing | new
  $location_id      = (int)($_POST['location_id'] ?? 0);

  // new location fields
  $house            = trim($_POST['house'] ?? '');
  $street           = trim($_POST['street'] ?? '');
  $city             = trim($_POST['city'] ?? '');
  $area_code        = trim($_POST['area_code'] ?? '');

  // basic validation
  if ($project_name === '' || $size_sqft <= 0 || $price <= 0) {
    $error = "Please fill Project Name, Size and Price properly.";
  } elseif ($no_of_bedrooms < 0 || $no_of_bathrooms < 0) {
    $error = "Beds/Baths cannot be negative.";
  } elseif (!in_array($availability, ['available','booked','sold'], true)) {
    // ✅ FIXED: matches DB enum('available','booked','sold')
    $error = "Invalid availability status.";
  } else {

    try {
      $conn->begin_transaction();

      /* ---------- Decide location_id ---------- */
      if ($location_mode === 'new') {
        // new location must have city at least
        if ($city === '') {
          throw new Exception("City is required for new location.");
        }

        $stmtLoc = $conn->prepare("
          INSERT INTO locations (house, street, city, area_code)
          VALUES (?, ?, ?, ?)
        ");
        $stmtLoc->bind_param("ssss", $house, $street, $city, $area_code);
        $stmtLoc->execute();

        $location_id = (int)$conn->insert_id;
      } else {
        // existing location must be selected
        if ($location_id <= 0) {
          throw new Exception("Please select an existing location, or choose 'Add new location'.");
        }
      }

      /* ---------- Insert property ---------- */
      $stmtProp = $conn->prepare("
        INSERT INTO properties
          (project_name, size_sqft, price, no_of_bedrooms, no_of_bathrooms, availability_status, provider_id, location_id)
        VALUES
          (?, ?, ?, ?, ?, ?, ?, ?)
      ");

      // ✅ FIXED: correct types (provider_id is int)
      $stmtProp->bind_param(
        "sidiisii",
        $project_name,
        $size_sqft,
        $price,
        $no_of_bedrooms,
        $no_of_bathrooms,
        $availability,
        $owner_id,
        $location_id
      );

      $stmtProp->execute();

      $conn->commit();
      $success = "Property added successfully!";
    } catch (Throwable $e) {
      $conn->rollback();
      $error = $e->getMessage();
    }
  }

  // reload locations list after insert so dropdown updates
  try {
    $resLoc = $conn->query("
      SELECT location_id, house, street, city, area_code
      FROM locations
      ORDER BY location_id DESC
    ");
    $locations = $resLoc ? $resLoc->fetch_all(MYSQLI_ASSOC) : [];
  } catch (Throwable $e) {
    // ignore
  }
}

/* helper to render location label */
function location_label(array $l): string {
  $parts = [];
  if (!empty($l['house']))  $parts[] = $l['house'];
  if (!empty($l['street'])) $parts[] = $l['street'];
  if (!empty($l['city']))   $parts[] = $l['city'];
  $txt = implode(', ', $parts);
  if (!empty($l['area_code'])) $txt .= " (" . $l['area_code'] . ")";
  if ($txt === '') $txt = "Location #" . (int)$l['location_id'];
  return $txt;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Property</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script>
    function toggleLocationMode() {
      const checked = document.querySelector('input[name="location_mode"]:checked');
      const mode = checked ? checked.value : 'existing';
      document.getElementById('existingLocationBox').style.display = (mode === 'existing') ? 'block' : 'none';
      document.getElementById('newLocationBox').style.display = (mode === 'new') ? 'block' : 'none';
    }
    window.addEventListener('DOMContentLoaded', toggleLocationMode);
  </script>
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Add Property</h3>
    <a class="btn btn-outline-secondary btn-sm" href="/homeplan/property_owner/dashboard.php">Back</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger"><b>Error:</b> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="">

        <div class="mb-3">
          <label class="form-label">Project Name</label>
          <input type="text" name="project_name" class="form-control" required
                 value="<?= htmlspecialchars($project_name) ?>">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Size (sqft)</label>
            <input type="number" name="size_sqft" class="form-control" min="1" required
                   value="<?= htmlspecialchars((string)$size_sqft) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Price</label>
            <input type="number" name="price" class="form-control" min="1" step="0.01" required
                   value="<?= htmlspecialchars((string)$price) ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Bedrooms</label>
            <input type="number" name="no_of_bedrooms" class="form-control" min="0" required
                   value="<?= htmlspecialchars((string)$no_of_bedrooms) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Bathrooms</label>
            <input type="number" name="no_of_bathrooms" class="form-control" min="0" required
                   value="<?= htmlspecialchars((string)$no_of_bathrooms) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Availability Status</label>
          <select name="availability_status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="available" <?= ($availability==='available')?'selected':''; ?>>Available</option>
            <option value="booked"    <?= ($availability==='booked')?'selected':''; ?>>Booked</option>
            <option value="sold"      <?= ($availability==='sold')?'selected':''; ?>>Sold</option>
          </select>
        </div>

        <hr>
        <h5 class="mb-3">Location</h5>

        <div class="mb-2">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="location_mode" value="existing"
                   <?= ($location_mode==='existing')?'checked':''; ?>
                   onclick="toggleLocationMode()">
            <label class="form-check-label">Use existing location</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="location_mode" value="new"
                   <?= ($location_mode==='new')?'checked':''; ?>
                   onclick="toggleLocationMode()">
            <label class="form-check-label">Add new location</label>
          </div>
        </div>

        <div id="existingLocationBox" class="mb-3">
          <label class="form-label">Select Location</label>
          <select name="location_id" class="form-select">
            <option value="0">-- Select --</option>
            <?php foreach ($locations as $l): ?>
              <option value="<?= (int)$l['location_id'] ?>" <?= ((int)$location_id === (int)$l['location_id'])?'selected':''; ?>>
                <?= htmlspecialchars(location_label($l)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div id="newLocationBox" class="border rounded p-3 mb-3 bg-white">
          <div class="mb-3">
            <label class="form-label">House</label>
            <input type="text" name="house" class="form-control" value="<?= htmlspecialchars($house) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Street</label>
            <input type="text" name="street" class="form-control" value="<?= htmlspecialchars($street) ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">City <span class="text-danger">*</span></label>
              <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($city) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Area Code</label>
              <input type="text" name="area_code" class="form-control" value="<?= htmlspecialchars($area_code) ?>">
            </div>
          </div>
          <div class="text-muted small">City is required when adding a new location.</div>
        </div>

        <button class="btn btn-primary" type="submit">Save Property</button>

      </form>
    </div>
  </div>
</div>

</body>
</html>

