<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'provider') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action     = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($request_id <= 0 || !in_array($action, ['accept', 'reject'], true)) {
    die("Invalid request/action.");
}

$newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

try {
    $pdo->beginTransaction();

    // Ensure request belongs to this provider (property request only)
    $stmt = $pdo->prepare("
        SELECT r.request_id, r.client_id, r.status, r.property_id
        FROM requests r
        JOIN properties p ON p.property_id = r.property_id
        WHERE r.request_id = ?
          AND p.provider_id = ?
          AND r.request_type = 'property'
        LIMIT 1
    ");
    $stmt->execute([$request_id, $provider_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->rollBack();
        die("Request not found or not yours.");
    }

    $client_id   = (int)$row['client_id'];
    $property_id = (int)$row['property_id'];

    // Update request status
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE request_id = ?");
    $stmt->execute([$newStatus, $request_id]);

    // Get property name for message
    $stmt = $pdo->prepare("SELECT project_name FROM properties WHERE property_id = ? LIMIT 1");
    $stmt->execute([$property_id]);
    $projectName = (string)$stmt->fetchColumn();

    // Insert notification for client (accepted OR rejected)
    if ($newStatus === 'accepted') {
        $notifMsg = "Your request for '{$projectName}' has been accepted by the provider.";
    } else {
        $notifMsg = "Your request for '{$projectName}' has been rejected by the provider.";
    }

    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, status, creation_date)
        VALUES (?, ?, 'unread', NOW())
    ");
    $stmt->execute([$client_id, $notifMsg]);

    $pdo->commit();

    header("Location: /homeplan/provider/requests.php?ok=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Action failed: " . $e->getMessage());
}

