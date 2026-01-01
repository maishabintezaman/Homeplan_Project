<?php
$unreadCount = 0;

if (!empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM notifications
        WHERE user_id = ? AND status = 'unread'
    ");
    $stmt->execute([$uid]);
    $unreadCount = (int)$stmt->fetchColumn();
}
