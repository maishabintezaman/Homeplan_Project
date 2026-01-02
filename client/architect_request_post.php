<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (strtolower(trim($_SESSION['role'] ?? '')) !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$client_id = (int)$_SESSION['user_id'];

/* accept both keys to be safe */
$architect_id = (int)($_POST['architect_user_id'] ?? ($_POST['architect_id'] ?? 0));

$project_type   = trim($_POST['project_type'] ?? '');
$location       = trim($_POST['location'] ?? '');
$area_sqft      = (int)($_POST['area_sqft'] ?? 0);
$budget         = ($_POST['budget'] ?? null);
$preferred_date = trim($_POST['preferred_date'] ?? '');
$message        = trim($_POST['message'] ?? '');

if ($architect_id <= 0 || $project_type === '' || $location === '' || $area_sqft <= 0) {
  header("Location: /homeplan/client/architect_list.php");
  exit;
}

/* normalize optional fields */
$budget = ($budget === '' || $budget === null) ? null : (float)$budget;
$preferred_date = ($preferred_date === '') ? null : $preferred_date;

/* ensure architect exists */
$st = $conn->prepare("SELECT user_id FROM users WHERE user_id=? AND LOWER(TRIM(role))='architect' LIMIT 1");
$st->bind_param("i", $architect_id);
$st->execute();
if (!$st->get_result()->fetch_assoc()) {
  header("Location: /homeplan/client/architect_list.php");
  exit;
}

/* prevent duplicate pending requests (same client->same architect) */
$dup = $conn->prepare("
  SELECT request_id
  FROM architect_requests
  WHERE client_user_id=? AND architect_user_id=? AND status='pending'
  LIMIT 1
");
$dup->bind_param("ii", $client_id, $architect_id);
$dup->execute();
if ($dup->get_result()->fetch_assoc()) {
  header("Location: /homeplan/client/architect_view.php?architect_id={$architect_id}&ok=1");
  exit;
}

/* insert request (matches your table columns) */
$ins = $conn->prepare("
  INSERT INTO architect_requests
    (client_user_id, architect_user_id, project_type, location, area_sqft, message, status, budget, preferred_date)
  VALUES
    (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
");

$ins->bind_param(
  "iississs",
  $client_id,
  $architect_id,
  $project_type,
  $location,
  $area_sqft,
  $message,
  $budget,
  $preferred_date
);

$ins->execute();

/* OPTIONAL notification insert (won't break if notifications table differs) */
try {
  $msg = "New architect request: {$project_type} ({$location})";
  $nt = $conn->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
  $nt->bind_param("is", $architect_id, $msg);
  $nt->execute();
} catch (Throwable $e) {
  // ignore if notifications schema is different
}

header("Location: /homeplan/client/architect_view.php?architect_id={$architect_id}&ok=1");
exit;

