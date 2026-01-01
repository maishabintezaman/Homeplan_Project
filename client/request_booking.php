<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$property_id = (int)($_POST['property_id'] ?? 0);
$client_id   = (int)$_SESSION['user_id'];

if ($property_id <= 0) {
  header("Location: /homeplan/client/properties.php");
  exit;
}

// Get property & provider
$stmt = $conn->prepare(
  "SELECT provider_id, availability_status
   FROM properties
   WHERE property_id=? LIMIT 1"
);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();

if (!$prop || $prop['availability_status'] !== 'available') {
  header("Location: /homeplan/client/property_view.php?id=$property_id&msg=not_available");
  exit;
}

// Insert booking request
$stmt = $conn->prepare(
  "INSERT INTO bookings (property_id, client_id, provider_id)
   VALUES (?, ?, ?)"
);
$stmt->bind_param("iii", $property_id, $client_id, $prop['provider_id']);

try {
  $stmt->execute();
  header("Location: /homeplan/client/property_view.php?id=$property_id&msg=requested");
  exit;
} catch (mysqli_sql_exception $e) {
  // Duplicate request
  header("Location: /homeplan/client/property_view.php?id=$property_id&msg=already_requested");
  exit;
}
