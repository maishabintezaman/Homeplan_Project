<?php
// /homeplan/developer/add_project_action.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'developer') {
  header("Location: /homeplan/auth/login.php");
  exit;
}

$developer_id = (int)$_SESSION['user_id'];

$title = trim($_POST['title'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '') {
  header("Location: /homeplan/developer/add_project.php?error=1");
  exit;
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
  header("Location: /homeplan/developer/add_project.php?error=1");
  exit;
}

$uploadDirAbs = __DIR__ . '/../uploads/developer_projects/';
$uploadDirWeb = '/homeplan/uploads/developer_projects/';

if (!is_dir($uploadDirAbs)) {
  @mkdir($uploadDirAbs, 0777, true);
}

$tmp = $_FILES['image']['tmp_name'];
$origName = $_FILES['image']['name'] ?? 'image';
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

$allowed = ['jpg','jpeg','png','webp'];
if (!in_array($ext, $allowed, true)) {
  header("Location: /homeplan/developer/add_project.php?error=1");
  exit;
}

$filename = 'dev_' . $developer_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destAbs = $uploadDirAbs . $filename;
$destWeb = $uploadDirWeb . $filename;

if (!move_uploaded_file($tmp, $destAbs)) {
  header("Location: /homeplan/developer/add_project.php?error=1");
  exit;
}

$stmt = $conn->prepare("
  INSERT INTO developer_projects (developer_id, title, location, description, image_url)
  VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("issss", $developer_id, $title, $location, $description, $destWeb);

$ok = $stmt->execute();
$stmt->close();

if ($ok) {
  header("Location: /homeplan/developer/add_project.php?success=1");
} else {
  header("Location: /homeplan/developer/add_project.php?error=1");
}
exit;
