<?php
// /homeplan/auth/login_post.php
session_start();
require_once __DIR__ . '/../config/db.php';

$email    = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$role     = trim($_POST['role'] ?? ''); // from login dropdown

if ($email === '' || $password === '' || $role === '') {
    header("Location: login.php?err=1");
    exit;
}

/*
  Allowed roles (must match your DB enum/text exactly)
  Update this list if you use different role strings.
*/
$allowed_roles = [
    'client',
    'property_owner',
    'developer',
    'architect',
    'material_provider',
    'worker_provider',
    'interior_designer',
    'admin'
];

if (!in_array($role, $allowed_roles, true)) {
    header("Location: login.php?err=1");
    exit;
}

$stmt = $conn->prepare("
    SELECT user_id, role, full_name, email, password
    FROM users
    WHERE email = ? AND role = ?
    LIMIT 1
");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: login.php?err=1");
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['role']    = $user['role'];
$_SESSION['email']   = $user['email'];
$_SESSION['name']    = $user['full_name'];

/* Redirect by role (so index.php doesn't block architects) */
switch ($user['role']) {
    case 'architect':
        header("Location: /homeplan/architect/dashboard.php");
        exit;

    case 'developer':
        header("Location: /homeplan/developer/dashboard.php");
        exit;

    case 'property_owner':
        header("Location: /homeplan/property_owner/dashboard.php");
        exit;

    case 'material_provider':
        header("Location: /homeplan/provider/dashboard.php");
        exit;

    case 'worker_provider':
        header("Location: /homeplan/provider/dashboard.php");
        exit;

    case 'interior_designer':
        header("Location: /homeplan/provider/dashboard.php");
        exit;

    case 'admin':
        header("Location: /homeplan/admin/dashboard.php");
        exit;

    case 'client':
    default:
        header("Location: /homeplan/client/dashboard.php");
        exit;
}

