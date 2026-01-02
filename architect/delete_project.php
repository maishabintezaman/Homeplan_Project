<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'architect') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$architect_id = (int)$_SESSION['user_id'];
$project_id = (int)($_POST['project_id'] ?? 0);

if ($project_id <= 0) {
  header("Location: /homeplan/architect/projects.php?err=Invalid project");
  exit;
}

// Ensure ownership
$stmt = $conn->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $project_id, $architect_id);
$stmt->execute();
$ok = $stmt->get_result()->fetch_assoc();

if (!$ok) {
  header("Location: /homeplan/architect/projects.php?err=Not allowed");
  exit;
}

// Delete images rows (files optional)
$conn->query("DELETE FROM project_images WHERE project_id = " . (int)$project_id);
// Delete project
$conn->query("DELETE FROM projects WHERE id = " . (int)$project_id);

header("Location: /homeplan/architect/projects.php?ok=1");
exit;
