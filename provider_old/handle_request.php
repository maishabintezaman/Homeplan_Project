<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['property_owner','developer','architect','material_provider','worker_provider','interior_designer'], true)) {
    header("Location: /homeplan/index.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];
$request_id  = (int)($_POST['request_id'] ?? 0);
$action      = (string)($_POST['action'] ?? '');

if ($request_id <= 0 || !in_array($action, ['accept','reject'], true)) {
    header("Location: /homeplan/provider/requests.php?err=" . urlencode("Invalid action"));
    exit;
}

$newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

// IMPORTANT: provider can update ONLY assigned requests
$stmt = $pdo->prepare("
    UPDATE requests r
    JOIN request_assignments ra ON ra.request_id = r.request_id
    SET r.status = ?
    WHERE r.request_id = ?
      AND ra.provider_id = ?
      AND r.status = 'pending'
");
$stmt->execute([$newStatus, $request_id, $provider_id]);

header("Location: /homeplan/provider/requests.php?msg=" . urlencode("Updated"));
exit;
