<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}
$architectId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT ar.request_id, ar.message, ar.status, ar.created_at,
         u.user_id AS client_id, u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone
  FROM architect_requests ar
  JOIN users u ON u.user_id = ar.client_user_id
  WHERE ar.architect_user_id = ?
  ORDER BY ar.created_at DESC
");
$stmt->execute([$architectId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Architect Requests</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Client Requests</h3>
    <a class="btn btn-outline-dark" href="/homeplan/architect/dashboard.php">Back</a>
  </div>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">No requests yet.</div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($rows as $r): ?>
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h5 class="mb-1"><?= htmlspecialchars($r['client_name']) ?></h5>
                <div class="text-muted small"><?= htmlspecialchars($r['client_email']) ?> · <?= htmlspecialchars($r['client_phone'] ?? '') ?></div>
              </div>
              <span class="badge bg-<?= $r['status']==='pending'?'warning':($r['status']==='accepted'?'success':'danger') ?>">
                <?= htmlspecialchars($r['status']) ?>
              </span>
            </div>

            <?php if (!empty($r['message'])): ?>
              <div class="mt-3">
                <strong>Message:</strong>
                <div class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($r['message'])) ?></div>
              </div>
            <?php endif; ?>

            <?php if ($r['status'] === 'pending'): ?>
              <div class="mt-3 d-flex gap-2">
                <form method="post" action="/homeplan/architect/request_action.php">
                  <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
                  <input type="hidden" name="action" value="accepted">
                  <button class="btn btn-success btn-sm">Accept</button>
                </form>

                <form method="post" action="/homeplan/architect/request_action.php">
                  <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
                  <input type="hidden" name="action" value="rejected">
                  <button class="btn btn-danger btn-sm">Reject</button>
                </form>
              </div>
            <?php endif; ?>

            <div class="text-muted small mt-3">Sent: <?= htmlspecialchars($r['created_at']) ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>
</body>
</html>

