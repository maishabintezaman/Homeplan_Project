<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'provider') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

// Load locations for dropdown
$locations = $pdo->query("SELECT location_id, street, city FROM locations ORDER BY city, street")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location_id = (int)($_POST['location_id'] ?? 0);
    $project_name = trim($_POST['project_name'] ?? '');
    $size_sqft = (int)($_POST['size_sqft'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $bed = (int)($_POST['no_of_bedrooms'] ?? 0);
    $bath = (int)($_POST['no_of_bathrooms'] ?? 0);
    $status = trim($_POST['availability_status'] ?? 'available');

    if ($location_id <= 0 || $project_name === '' || $size_sqft <= 0 || $price <= 0) {
        $error = "Please fill all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO properties
            (provider_id, location_id, project_name, size_sqft, price, no_of_bedrooms, no_of_bathrooms, availability_status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$provider_id, $location_id, $project_name, $size_sqft, $price, $bed, $bath, $status]);

        header("Location: /homeplan/provider/properties.php?msg=added");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Property</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
  <h3 class="mb-3">Add Property</h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="card p-4">
    <div class="mb-3">
      <label class="form-label">Location *</label>
      <select name="location_id" class="form-select" required>
        <option value="">Select Location</option>
        <?php foreach ($locations as $loc): ?>
          <option value="<?= (int)$loc['location_id'] ?>">
            <?= htmlspecialchars($loc['street'] . ', ' . $loc['city']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Project Name *</label>
      <input type="text" name="project_name" class="form-control" required>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Size (sqft) *</label>
        <input type="number" name="size_sqft" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Price *</label>
        <input type="number" name="price" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Bedrooms</label>
        <input type="number" name="no_of_bedrooms" class="form-control" value="0">
      </div>
      <div class="col-md-2">
        <label class="form-label">Bathrooms</label>
        <input type="number" name="no_of_bathrooms" class="form-control" value="0">
      </div>
    </div>

    <div class="mt-3 mb-3">
      <label class="form-label">Availability Status</label>
      <select name="availability_status" class="form-select">
        <option value="available">available</option>
        <option value="sold">sold</option>
        <option value="booked">booked</option>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-outline-secondary" href="/homeplan/provider/properties.php">Cancel</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
