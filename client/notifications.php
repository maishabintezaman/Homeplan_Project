<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT notification_id, message, status, creation_date
    FROM notifications
    WHERE user_id = ?
    ORDER BY creation_date DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdo->prepare("
    UPDATE notifications
    SET status = 'read'
    WHERE user_id = ? AND status = 'unread'
")->execute([$user_id]);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">Notifications</h3>
        <a href="/homeplan/client/dashboard.php" class="btn btn-outline-dark">Back</a>
    </div>

    <?php if (!$notifications): ?>
        <div class="alert alert-info">No notifications yet.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item <?= $n['status'] === 'unread' ? 'list-group-item-warning' : '' ?>">
                    <div class="fw-bold mb-1">
                        <?= htmlspecialchars($n['message']) ?>
                    </div>
                    <div class="small text-muted">
                        <?= date('d M Y, h:i A', strtotime($n['creation_date'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

