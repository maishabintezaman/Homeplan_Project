<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

function back_err($msg) {
  header("Location: /homeplan/auth/register.php?err=" . urlencode($msg));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') back_err("Invalid request.");

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$password  = (string)($_POST['password'] ?? '');
$role      = trim($_POST['role'] ?? 'client');

$allowedRoles = [
  'client','property_owner','developer','architect',
  'material_provider','worker_provider','interior_designer'
];
if (!in_array($role, $allowedRoles, true)) back_err("Invalid role.");

if ($full_name === '' || $email === '' || $phone === '' || $password === '') {
  back_err("Please fill in all required fields.");
}
if (strlen($password) < 6) back_err("Password must be at least 6 characters.");

// Role-specific inputs
$architect_license_no = trim($_POST['architect_license_no'] ?? '');
$architect_years      = (int)($_POST['architect_years'] ?? 0);
$architect_portfolio  = trim($_POST['architect_portfolio'] ?? '');
$expertise_ids        = $_POST['expertise_ids'] ?? [];

if ($role === 'architect') {
  if ($architect_license_no === '') back_err("Architect license/certificate number is required.");

  if (!is_array($expertise_ids) || count($expertise_ids) === 0) {
    back_err("Please select at least one expertise for Architect.");
  }
}

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);
if (!$hash) back_err("Failed to hash password.");

// Create user + profile in transaction
$conn->begin_transaction();

try {
  // Ensure unique email per role (or globally). Here: globally unique email
  $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
  $chk->bind_param("s", $email);
  $chk->execute();
  if ($chk->get_result()->fetch_assoc()) {
    throw new Exception("Email already exists.");
  }

  // Insert user
  $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $full_name, $email, $phone, $hash, $role);
  $stmt->execute();

  $user_id = (int)$conn->insert_id;
  if ($user_id <= 0) throw new Exception("Failed to create user.");

  // Architect profile + expertise
  if ($role === 'architect') {
    // architect_profiles(user_id, license_no, years, portfolio)
    $p = $conn->prepare("INSERT INTO architect_profiles (user_id, license_no, years, portfolio) VALUES (?, ?, ?, ?)");
    $p->bind_param("isis", $user_id, $architect_license_no, $architect_years, $architect_portfolio);
    $p->execute();

    // architect_expertise(user_id, expertise_id)
    $ins = $conn->prepare("INSERT INTO architect_expertise (user_id, expertise_id) VALUES (?, ?)");
    foreach ($expertise_ids as $eid) {
      if (!ctype_digit((string)$eid)) continue;
      $eid = (int)$eid;
      if ($eid <= 0) continue;

      $ins->bind_param("ii", $user_id, $eid);
      $ins->execute();
    }
  }

  $conn->commit();

  // redirect to login
  header("Location: /homeplan/auth/login.php?ok=1");
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  back_err($e->getMessage());
}
