<?php
// /homeplan/property_owner/delete_property.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'property_owner') {
  header("Location: /homeplan/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /homeplan/property_owner/my_properties.php");
  exit;
}

$provider_id    = (int)$_SESSION['user_id'];
$property_id = (int)($_POST['property_id'] ?? 0);
$csrf        = (string)($_POST['csrf_token'] ?? '');

if ($property_id <= 0) {
  header("Location: /homeplan/property_owner/my_properties.php?err=" . urlencode("Invalid property."));
  exit;
}

if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
  header("Location: /homeplan/property_owner/my_properties.php?err=" . urlencode("Invalid request token."));
  exit;
}

try {
  $conn->begin_transaction();

  // ensure property belongs to this owner
  $chk = $conn->prepare("SELECT property_id FROM properties WHERE property_id=? AND provider_id=? LIMIT 1");
  $chk->bind_param("ii", $property_id, $provider_id);
  $chk->execute();
  if (!$chk->get_result()->fetch_assoc()) {
    throw new Exception("Property not found or not yours.");
  }

  // delete requests first (if any)
  $delReq = $conn->prepare("DELETE FROM requests WHERE property_id=?");
  $delReq->bind_param("i", $property_id);
  $delReq->execute();

  // delete property
  $delProp = $conn->prepare("DELETE FROM properties WHERE property_id=? AND provider_id=?");
  $delProp->bind_param("ii", $property_id, $provider_id);
  $delProp->execute();

  $conn->commit();

  header("Location: /homeplan/property_owner/my_properties.php?msg=" . urlencode("Property removed successfully."));
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  header("Location: /homeplan/property_owner/my_properties.php?err=" . urlencode($e->getMessage()));
  exit;
}

