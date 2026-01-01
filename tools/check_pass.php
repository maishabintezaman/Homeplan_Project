<?php
require_once __DIR__ . '/../config/db.php';

$email = $_GET['email'] ?? '';
$test  = $_GET['p'] ?? '';

$stmt = $conn->prepare("SELECT password FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
  die("User not found");
}

echo password_verify($test, $row['password'])
  ? "✅ MATCH"
  : "❌ NOT MATCH";

