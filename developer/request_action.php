<?php
// /homeplan/developer/request_action.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$dev_id = (int)$_SESSION['user_id'];

$request_id = (int)($_POST['request_id'] ?? 0);
$action     = trim((string)($_POST['action'] ?? ''));

if ($request_id <= 0 || !in_array($action, ['accept','reject'], true)) {
  die("Invalid request/action.");
}

$newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

/**
 * 1) নিশ্চিত হই request টা এই developer-এরই কিনা
 * 2) status update
 * 3) client কে notification insert
 */
$sql = "SELECT request_id, client_id
        FROM requests
        WHERE request_id = ?
          AND provider_id = ?
          AND request_type = 'developer_land'
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $request_id, $dev_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
  die("Request not found or not yours.");
}

$client_id = (int)$row['client_id'];

// Update status
$sql = "UPDATE requests
        SET status = ?
        WHERE request_id = ?
          AND provider_id = ?
          AND request_type = 'developer_land'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sii", $newStatus, $request_id, $dev_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Insert notification (client)
$msg = ($newStatus === 'accepted')
  ? "✅ Your land request has been accepted by the developer."
  : "❌ Your land request has been rejected by the developer.";

$sql = "INSERT INTO notifications (user_id, message, status, creation_date)
        VALUES (?, ?, 'unread', NOW())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $client_id, $msg);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: /homeplan/developer/requests.php?ok=1");
exit;
