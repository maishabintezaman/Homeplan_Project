<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../partials/navbar.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/db.php';

$architect_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Invalid+request");
  exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '') {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Title+is+required");
  exit;
}

/* ---------- Upload Image ---------- */
if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Image+upload+failed");
  exit;
}

$tmpName = $_FILES['image']['tmp_name'];
$origName = $_FILES['image']['name'] ?? 'image';
$size = (int)($_FILES['image']['size'] ?? 0);

if ($size <= 0 || $size > 5 * 1024 * 1024) {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Image+must+be+<=+5MB");
  exit;
}

$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp'];

if (!in_array($ext, $allowed, true)) {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Only+jpg+jpeg+png+webp+allowed");
  exit;
}

$uploadDir = realpath(__DIR__ . '/../uploads');
if ($uploadDir === false) {
  // uploads folder not found (should exist)
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Uploads+folder+missing");
  exit;
}

$projectDir = $uploadDir . '/projects';
if (!is_dir($projectDir)) {
  @mkdir($projectDir, 0777, true);
}

$filename = 'arch_' . $architect_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath = $projectDir . '/' . $filename;

// IMPORTANT: move_uploaded_file needs write permission to uploads/projects
if (!move_uploaded_file($tmpName, $targetPath)) {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=Failed+to+save+image");
  exit;
}

/* Store URL path (relative) */
$image_url = '/homeplan/uploads/projects/' . $filename;

/* ---------- Insert into architect_projects ---------- */
/*
Table columns you showed:
project_id (auto)
architect_id
title
description
image_url
created_at (auto timestamp)
*/

$stmt = $conn->prepare(
  "INSERT INTO architect_projects (architect_id, title, description, image_url)
   VALUES (?, ?, ?, ?)"
);
if (!$stmt) {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=DB+prepare+failed");
  exit;
}

$stmt->bind_param("isss", $architect_id, $title, $description, $image_url);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
  header("Location: /homeplan/architect/add_project.php?error=1&msg=DB+insert+failed");
  exit;
}

header("Location: /homeplan/architect/add_project.php?success=1");
exit;

