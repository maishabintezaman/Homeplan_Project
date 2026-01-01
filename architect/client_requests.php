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

if ($requestId <= 0 || !in_array($status, ['accepted', 'rejected'], true)) {
    header("Location: /homeplan/architect/client_requests.php?err=Invalid request");
    exit;
}

try {
    $pdo->beginTransaction();

    // Load request and verify ownership
    $stmt = $pdo->prepare("
        SELECT
            ar.client_user_id,
            ar.project_type,
            ar.location,
            ar.status
        FROM architect_requests ar
        WHERE ar.request_id = ? AND ar.architect_user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$requestId, $architectId]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        $pdo->rollBack();
        header("Location: /homeplan/architect/client_requests.php?err=Request not found");
        exit;
    }

    if (strtolower($req['status']) !== 'pending') {
        $pdo->rollBack();
        header("Location: /homeplan/architect/client_requests.php?err=Request already processed");
        exit;
    }

    // Update request status
    $upd = $pdo->prepare("
        UPDATE architect_requests
        SET status = ?
        WHERE request_id = ? AND architect_user_id = ?
    ");
    $upd->execute([$status, $requestId, $architectId]);

    // Fetch architect name
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$architectId]);
    $architectName = (string)$stmt->fetchColumn();

    // Prepare notification
    $clientId = (int)$req['client_user_id'];
    $msg = "Architect {$architectName} has {$status} your request. "
         . "Project: {$req['project_type']}, Location: {$req['location']}.";

    // Insert notification
    $notif = $pdo->prepare("
        INSERT INTO notifications (user_id, message, status, creation_date)
        VALUES (?, ?, 'unread', NOW())
    ");
    $notif->execute([$clientId, $msg]);

    $pdo->commit();

    header("Location: /homeplan/architect/client_requests.php?ok=1");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header("Location: /homeplan/architect/client_requests.php?err=Server error");
    exit;
}


