<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$role    = (string)($_SESSION['role'] ?? '');

if ($user_id <= 0 || $role !== 'property_owner') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /homeplan/property_owner/add_property.php");
    exit;
}

// match your client pages column names
$project_name     = trim($_POST['project_name'] ?? '');
$size_sqft        = (int)($_POST['size_sqft'] ?? 0);
$price            = (float)($_POST['price'] ?? 0);
$no_of_bedrooms   = (int)($_POST['no_of_bedrooms'] ?? 0);
$no_of_bathrooms  = (int)($_POST['no_of_bathrooms'] ?? 0);
$location_id      = (int)($_POST['location_id'] ?? 0);
$availability     = trim($_POST['availability_status'] ?? 'available');

if (
    $project_name === '' ||
    $size_sqft <= 0 ||
    $price <= 0 ||
    $location_id <= 0 ||
    !in_array($availability, ['available', 'unavailable'], true)
) {
    header("Location: /homeplan/property_owner/add_property.php?error=" . urlencode("Please fill all required fields correctly."));
    exit;
}

// validate location exists
$chk = $conn->prepare("SELECT location_id FROM locations WHERE location_id = ? LIMIT 1");
$chk->bind_param("i", $location_id);
$chk->execute();
if (!$chk->get_result()->fetch_assoc()) {
    header("Location: /homeplan/property_owner/add_property.php?error=" . urlencode("Invalid location selected."));
    exit;
}

// insert property (columns aligned with your client/properties.php + client/property_view.php)
$sql = "
INSERT INTO properties
(project_name, size_sqft, price, no_of_bedrooms, no_of_bathrooms, availability_status, provider_id, location_id, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sidiiiii",
    $project_name,
    $size_sqft,
    $price,
    $no_of_bedrooms,
    $no_of_bathrooms,
    $availability,
    $user_id,
    $location_id
);

if (!$stmt->execute()) {
    header("Location: /homeplan/property_owner/add_property.php?error=" . urlencode("DB Error: " . $stmt->error));
    exit;
}

header("Location: /homeplan/property_owner/my_properties.php");
exit;

