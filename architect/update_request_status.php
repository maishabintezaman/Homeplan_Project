<?php

session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /homeplan/architect/client_requests.php");
  exit;
}

$architectId = (int)$_SESSION['user_id'];
$requestId   = (int)($_POST['request_id'] ?? 0);
$status      = strtolower(trim($_POST['status'] ?? ''));

if ($requestId <= 0 || !in_array($status, ['accepted','rejected'], true)) {
  header("Location: /homeplan/architect/client_requests.php");
  exit;
}

try {
  // IMPORTANT: using request_id (not id)
  $stmt = $pdo->prepare("
    UPDATE architect_requests
    SET status = ?
    WHERE request_id = ? AND architect_user_id = ?
    LIMIT 1
  ");
  $stmt->execute([$status, $requestId, $architectId]);

  header("Location: /homeplan/architect/client_requests.php");
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo "<h2>Update failed</h2>";
  echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
  exit;
}
