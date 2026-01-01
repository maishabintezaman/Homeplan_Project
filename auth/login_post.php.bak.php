<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("
    SELECT user_id, role, full_name, email, password
    FROM users
    WHERE email = ?
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: login.php?err=1");
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role']    = $user['role'];
$_SESSION['email']   = $user['email'];
$_SESSION['name']    = $user['full_name'];

header("Location: /homeplan/index.php");
exit;

