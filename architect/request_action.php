<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /homeplan/architect/client_requests.php?err=Invalid request method");
  exit;
}

$architectId = (int)$_SESSION['user_id'];
$requestId   = (int)($_POST['request_id'] ?? 0);
$action      = strtolower(trim($_POST['action'] ?? ''));

// Accept either accept/reject OR accepted/rejected (support both)
if ($action === 'accept') $action = 'accepted';
if ($action === 'reject') $action = 'rejected';

if ($requestId <= 0 || !in_array($action, ['accepted','rejected'], true)) {
  header("Location: /homeplan/architect/client_requests.php?err=Invalid request data");
  exit;
}

try {
  $pdo->beginTransaction();

  // Load request (ensure it belongs to this architect)
  $q = $pdo->prepare("
    SELECT request_id, client_user_id, project_type, location, area_sqft, budget, status
    FROM architect_requests
    WHERE request_id = ? AND architect_user_id = ?
    LIMIT 1
  ");
  $q->execute([$requestId, $architectId]);
  $req = $q->fetch(PDO::FETCH_ASSOC);

  if (!$req) {
    $pdo->rollBack();
    header("Location: /homeplan/architect/client_requests.php?err=Request not found");
    exit;
  }

  if (strtolower((string)$req['status']) !== 'pending') {
    $pdo->rollBack();
    header("Location: /homeplan/architect/client_requests.php?err=Request already processed");
    exit;
  }

  // Update status
  $u = $pdo->prepare("
    UPDATE architect_requests
    SET status = ?
    WHERE request_id = ? AND architect_user_id = ?
  ");
  $u->execute([$action, $requestId, $architectId]);

  // Get architect name (optional, for nicer message)
  $stName = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? LIMIT 1");
  $stName->execute([$architectId]);
  $architectName = (string)$stName->fetchColumn();

  // Notify client
  $clientId = (int)$req['client_user_id'];

  $prettyAction = ($action === 'accepted') ? 'accepted' : 'rejected';
  $msg = "Architect {$architectName} has {$prettyAction} your request. "
       . "Project: {$req['project_type']}, Location: {$req['location']}.";

  $n = $pdo->prepare("
    INSERT INTO notifications (user_id, message, status, creation_date)
    VALUES (?, ?, 'unread', NOW())
  ");
  $n->execute([$clientId, $msg]);

  $pdo->commit();

  header("Location: /homeplan/architect/client_requests.php?ok=1");
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  header("Location: /homeplan/architect/client_requests.php?err=Server error");
  exit;
}

