<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'client') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /homeplan/client/architect_list.php");
  exit;
}

$clientId      = (int)$_SESSION['user_id'];
$architectId   = isset($_POST['architect_id']) ? (int)$_POST['architect_id'] : 0;
$projectType   = trim($_POST['project_type'] ?? '');
$location      = trim($_POST['location'] ?? '');
$areaSqft      = ($_POST['area_sqft'] ?? '') !== '' ? (int)$_POST['area_sqft'] : null;
$budget        = ($_POST['budget'] ?? '') !== '' ? (float)$_POST['budget'] : null;
$preferredDate = trim($_POST['preferred_date'] ?? '');
$message       = trim($_POST['message'] ?? '');

$backUrl = "/homeplan/client/architect_request_form.php?architect_id=" . (int)$architectId;

if ($architectId <= 0 || $projectType === '' || $location === '') {
  header("Location: {$backUrl}&error=missing");
  exit;
}

// Ensure architect exists
$check = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND LOWER(role)='architect' LIMIT 1");
$check->execute([$architectId]);
if (!$check->fetchColumn()) {
  header("Location: {$backUrl}&error=notfound");
  exit;
}

// Normalize date
if ($preferredDate === '') {
  $preferredDate = null;
}

// Prevent duplicate pending request
$dup = $pdo->prepare("
  SELECT request_id
  FROM architect_requests
  WHERE client_user_id = ? AND architect_user_id = ? AND status = 'pending'
  ORDER BY request_id DESC
  LIMIT 1
");
$dup->execute([$clientId, $architectId]);
if ($dup->fetchColumn()) {
  header("Location: {$backUrl}&error=already");
  exit;
}

// Insert (IMPORTANT: do NOT include request_id column; it is AUTO_INCREMENT)
$stmt = $pdo->prepare("
  INSERT INTO architect_requests
    (client_user_id, architect_user_id, project_type, location, area_sqft, budget, preferred_date, message, status)
  VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([
  $clientId,
  $architectId,
  $projectType,
  $location,
  $areaSqft,
  $budget,
  $preferredDate,
  ($message === '' ? null : $message)
]);
// ===== Notify architect =====

// Fetch client name
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$clientId]);
$clientName = (string)$stmt->fetchColumn();

// Count pending requests for this architect
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM architect_requests 
    WHERE architect_user_id = ? AND status = 'pending'
");
$stmt->execute([$architectId]);
$pendingCount = (int)$stmt->fetchColumn();

// Build message
$notifMsg = "You have {$pendingCount} pending request"
          . ($pendingCount > 1 ? "s" : "")
          . " from {$clientName}.";

// Insert notification
$stmt = $pdo->prepare("
    INSERT INTO notifications (user_id, message, status, creation_date)
    VALUES (?, ?, 'unread', NOW())
");
$stmt->execute([$architectId, $notifMsg]);

header("Location: {$backUrl}&success=1");
exit;


