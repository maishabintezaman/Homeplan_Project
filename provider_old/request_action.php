<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$allowed = ['property_owner','developer','architect','material_provider','worker_provider','interior_designer','admin'];
if (!in_array(($_SESSION['role'] ?? ''), $allowed, true)) {
    header("Location: /homeplan/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /homeplan/provider/requests.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];
$request_id  = (int)($_POST['request_id'] ?? 0);
$action      = $_POST['action'] ?? '';

if ($request_id <= 0 || !in_array($action, ['accepted','rejected'], true)) {
    header("Location: /homeplan/provider/requests.php?msg=invalid");
    exit;
}

// Confirm this request belongs to this provider
$stmt = $conn->prepare("SELECT request_id FROM request_assignments WHERE request_id = ? AND provider_id = ? LIMIT 1");
$stmt->bind_param("ii", $request_id, $provider_id);
$stmt->execute();
$ok = $stmt->get_result()->fetch_assoc();

if (!$ok) {
    header("Location: /homeplan/provider/requests.php?msg=not_allowed");
    exit;
}

// Update only if pending
$stmt = $conn->prepare("UPDATE requests SET status = ? WHERE request_id = ? AND status = 'pending'");
$stmt->bind_param("si", $action, $request_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: /homeplan/provider/request_view.php?id={$request_id}&msg=updated");
    exit;
}

header("Location: /homeplan/provider/request_view.php?id={$request_id}&msg=already");
exit;


