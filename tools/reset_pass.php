<?php
require_once __DIR__ . '/../config/db.php';

$email = $_GET['email'] ?? '';
$newPass = $_GET['p'] ?? '';

if ($email === '' || $newPass === '') {
  die("email and p required");
}

$hash = password_hash($newPass, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
$stmt->bind_param("ss", $hash, $email);
$stmt->execute();

echo "Password reset successful for {$email}";

