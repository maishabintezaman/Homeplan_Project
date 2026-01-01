<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';

if (!$full_name || !$email || !$password) {
    die("All fields required");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (role, full_name, email, password)
    VALUES ('client', ?, ?, ?)
");
$stmt->bind_param("sss", $full_name, $email, $hash);
$stmt->execute();

header("Location: login.php?msg=registered");
exit;

