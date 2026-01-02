<?php
// /homeplan/architect/update_request_status.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /homeplan/architect/requests.php");
  exit;
}

$architectId = (int)$_SESSION['user_id'];
$requestId   = (int)($_POST['request_id'] ?? 0);
$status      = strtolower(trim($_POST['status'] ?? ''));

if ($requestId <= 0 || !in_array($status, ['accepted','rejected'], true)) {
  header("Location: /homeplan/architect/requests.php?error=1&msg=Invalid request");
  exit;
}

try {
  $conn->begin_transaction();

  // 1) Load request (and verify it belongs to this architect)
  $stmt = $conn->prepare("
    SELECT client_user_id, project_type, location, status
    FROM architect_requests
    WHERE request_id = ? AND architect_user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $requestId, $architectId);
  $stmt->execute();
  $req = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$req) {
    $conn->rollback();
    header("Location: /homeplan/architect/requests.php?error=1&msg=Request not found");
    exit;
  }

  if (strtolower($req['status']) !== 'pending') {
    $conn->rollback();
    header("Location: /homeplan/architect/requests.php?error=1&msg=Request already processed");
    exit;
  }

  // 2) Update request status
  $stmt = $conn->prepare("
    UPDATE architect_requests
    SET status = ?
    WHERE request_id = ? AND architect_user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("sii", $status, $requestId, $architectId);
  $stmt->execute();
  $stmt->close();

  // 3) Get architect name
  $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $architectId);
  $stmt->execute();
  $architectName = (string)($stmt->get_result()->fetch_assoc()['full_name'] ?? 'Architect');
  $stmt->close();

  // 4) Insert notification to client
  $clientId = (int)$req['client_user_id'];
  $msg = "Architect {$architectName} has {$status} your request. "
       . "Project: {$req['project_type']}, Location: {$req['location']}.";

  $stmt = $conn->prepare("
    INSERT INTO notifications (user_id, message, status, creation_date)
    VALUES (?, ?, 'unread', NOW())
  ");
  $stmt->bind_param("is", $clientId, $msg);
  $stmt->execute();
  $stmt->close();

  $conn->commit();

  header("Location: /homeplan/architect/requests.php?success=1");
  exit;

} catch (Throwable $e) {
  if ($conn->errno) {
    $conn->rollback();
  }
  header("Location: /homeplan/architect/requests.php?error=1&msg=Server error");
  exit;
}

