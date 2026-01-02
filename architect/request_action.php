<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
  header("Location: /homeplan/auth/login.php");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$architect_id = (int)$_SESSION['user_id'];

$request_id = (int)($_POST['request_id'] ?? 0);
$new_status = strtolower(trim($_POST['status'] ?? ''));

if ($request_id <= 0 || !in_array($new_status, ['accepted','rejected'], true)) {
  header("Location: /homeplan/architect/requests.php");
  exit;
}

// ensure request belongs to this architect + fetch client_id
$st = $conn->prepare("SELECT client_user_id, project_type, location FROM architect_requests WHERE request_id=? AND architect_user_id=? LIMIT 1");
$st->bind_param("ii", $request_id, $architect_id);
$st->execute();
$row = $st->get_result()->fetch_assoc();

if (!$row) {
  header("Location: /homeplan/architect/requests.php");
  exit;
}

// update status
$up = $conn->prepare("UPDATE architect_requests SET status=? WHERE request_id=? AND architect_user_id=?");
$up->bind_param("sii", $new_status, $request_id, $architect_id);
$up->execute();

// notify client
$client_id = (int)$row['client_user_id'];
$type = $row['project_type'] ?? 'Project';
$loc  = $row['location'] ?? '';

$msg = "Your request ({$type}".($loc ? " - {$loc}" : "").") was {$new_status} by the architect.";
$nt = $conn->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
$nt->bind_param("is", $client_id, $msg);
$nt->execute();

header("Location: /homeplan/architect/requests.php");
exit;

