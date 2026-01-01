<?php
// /homeplan/auth/login.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$email = '';
$role  = '';

$roles = [
  'client'            => 'Client',
  'property_owner'    => 'Property Owner',
  'developer'         => 'Developer',
  'architect'         => 'Architect',
  'material_provider' => 'Material Provider',
  'worker_provider'   => 'Worker Provider',
  'interior_designer' => 'Interior Designer',
  'admin'             => 'Admin',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');
    $role  = trim($_POST['role'] ?? '');

    if ($email === '' || $pass === '' || $role === '') {
        $error = "Please fill in all fields.";
    } elseif (!array_key_exists($role, $roles)) {
        $error = "Invalid role selected.";
    } else {

        $sql = "
            SELECT user_id, full_name, email, password, role
            FROM users
            WHERE email = ? AND role = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "DB Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $email, $role);
            $stmt->execute();
            $res  = $stmt->get_result();
            $user = $res->fetch_assoc();

            if (!$user || !password_verify($pass, $user['password'])) {
                $error = "Invalid email, password, or role.";
            } else {

                session_regenerate_id(true);

                $_SESSION['user_id']    = (int)$user['user_id'];
                $_SESSION['email']      = (string)$user['email'];
                $_SESSION['role']       = (string)$user['role'];
                $_SESSION['full_name']  = (string)($user['full_name'] ?? '');
                $_SESSION['name']       = (string)($user['full_name'] ?? '');

                // Redirect by role (must exist)
                switch ($_SESSION['role']) {
                    case 'property_owner':
                        header("Location: /homeplan/property_owner/dashboard.php");
                        exit;
                    case 'client':
                        header("Location: /homeplan/client/dashboard.php");
                        exit;
                    case 'developer':
                        header("Location: /homeplan/developer/dashboard.php");
                        exit;
                    case 'architect':
                        header("Location: /homeplan/architect/dashboard.php");
                        exit;
                    case 'material_provider':
                        header("Location: /homeplan/material_provider/dashboard.php");
                        exit;
                    case 'worker_provider':
                        header("Location: /homeplan/worker_provider/dashboard.php");
                        exit;
                    case 'interior_designer':
                        header("Location: /homeplan/interior_designer/dashboard.php");
                        exit;
                    case 'admin':
                        header("Location: /homeplan/admin/dashboard.php");
                        exit;
                    default:
                        header("Location: /homeplan/index.php");
                        exit;
                }
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow-sm" style="width:420px;">
    <div class="card-body p-4">

      <h3 class="text-center mb-4">Login</h3>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="/homeplan/auth/login.php">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Login as</label>
          <select name="role" class="form-select" required>
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $k => $label): ?>
              <option value="<?= htmlspecialchars($k) ?>" <?= ($role === $k ? 'selected' : '') ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button class="btn btn-primary w-100" type="submit">Login</button>
      </form>

      <hr>

      <div class="text-center">
        <span class="text-muted">Don't have an account?</span><br>
        <a href="/homeplan/auth/register.php" class="btn btn-outline-secondary mt-2">Register</a>
      </div>

    </div>
  </div>
</div>

</body>
</html>

