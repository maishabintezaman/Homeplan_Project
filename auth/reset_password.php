<?php
require_once __DIR__ . '/../config/db.php';

$email = trim($_GET['email'] ?? '');
$pass  = (string)($_GET['pass'] ?? '');

if ($email === '' || $pass === '') {
    echo "Use: /homeplan/auth/reset_password.php?email=rahim.client@gmail.com&pass=123456";
    exit;
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ? LIMIT 1");
$stmt->execute([$hash, $email]);

echo "OK";
