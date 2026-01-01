<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'property_owner') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

$request_id = (int)($_POST['request_id'] ?? 0);
$action     = $_POST['action'] ?? '';

if ($request_id <= 0 || !in_array($action, ['accept','reject'], true)) {
    header("Location: /homeplan/property_owner/requests.php?err=" . urlencode("Invalid action"));
    exit;
}

$newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

// Make sure the request belongs to this property owner and is pending
$stmt = $conn->prepare("
    SELECT r.request_id, r.status
    FROM requests r
    INNER JOIN properties p ON p.property_id = r.property_id
    WHERE r.request_id = ? AND p.provider_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $request_id, $provider_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header("Location: /homeplan/property_owner/requests.php?err=" . urlencode("Request not found"));
    exit;
}

if (($row['status'] ?? '') !== 'pending') {
    header("Location: /homeplan/property_owner/request_view.php?id={$request_id}&msg=already_done");
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE requests SET status=? WHERE request_id=? LIMIT 1");
$stmt->bind_param("si", $newStatus, $request_id);
$stmt->execute();

header("Location: /homeplan/property_owner/request_view.php?id={$request_id}&msg={$newStatus}");
exit;
