<?php
session_start();
require_once __DIR__ . '/../config/db.php';

function clean($v) {
    return trim((string)$v);
}

$errors = [];

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

// ONLY these roles are “providers” in your system:
$providerRoleSet = [
    'property_owner'    => true,
    'developer'         => true,
    'architect'         => true,
    'material_provider' => true,
    'worker_provider'   => true,
    'interior_designer' => true,
];

$providerTypes = [
    'developer'   => 'Developer',
    'builder'     => 'Builder',
    'contractor'  => 'Contractor',
    'architect'   => 'Architect',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = clean($_POST['full_name'] ?? '');
    $email     = clean($_POST['email'] ?? '');
    $phone     = clean($_POST['phone'] ?? '');
    $password  = (string)($_POST['password'] ?? '');
    $role      = clean($_POST['role'] ?? '');

    $provider_type = clean($_POST['provider_type'] ?? '');
    if ($provider_type === '') $provider_type = null;

    // validation
    if ($full_name === '') $errors[] = "Full name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($password === '' || strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if (!array_key_exists($role, $roles)) $errors[] = "Invalid role selected.";

    // provider_type rules
    if ($role === 'client' || $role === 'admin' || !isset($providerRoleSet[$role])) {
        $provider_type = null; // force NULL
    } else {
        // provider role: provider_type optional but if provided must be valid
        if ($provider_type !== null && !array_key_exists($provider_type, $providerTypes)) {
            $errors[] = "Invalid provider type selected.";
        }
    }

    if (empty($errors)) {
        try {
            // check existing email
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();

            if ($exists) {
                $errors[] = "This email is already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // insert user
                $stmt = $conn->prepare("
                    INSERT INTO users (role, provider_type, full_name, email, phone, password)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                // For NULL provider_type, mysqli needs bind_param + set null variable:
                $pt = $provider_type; // may be null
                $stmt->bind_param("ssssss", $role, $pt, $full_name, $email, $phone, $hash);
                $stmt->execute();

                $newUserId = (int)$conn->insert_id;

                // If client -> ensure clients row exists (FK requirement)
                if ($role === 'client') {
                    $stmt = $conn->prepare("INSERT IGNORE INTO clients (client_id) VALUES (?)");
                    $stmt->bind_param("i", $newUserId);
                    $stmt->execute();
                }

                // If provider roles -> ensure providers row exists (if you use provider_id FK in properties)
                $check = $conn->query("SHOW TABLES LIKE 'providers'");
                if ($check && $check->num_rows > 0 && isset($providerRoleSet[$role])) {
                    // Try minimal insert
                    try {
                        $stmt = $conn->prepare("INSERT IGNORE INTO providers (provider_id) VALUES (?)");
                        $stmt->bind_param("i", $newUserId);
                        $stmt->execute();
                    } catch (mysqli_sql_exception $e) {
                        // ignore if providers table has different columns
                    }
                }

                header("Location: /homeplan/auth/login.php?registered=1");
                exit;
            }

        } catch (mysqli_sql_exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .wrap { max-width: 520px; margin: 40px auto; }
  </style>
</head>
<body class="bg-light">

<div class="wrap">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h3 class="mb-3">Create Account</h3>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
          <div class="form-text">Minimum 6 characters</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Register As (Role)</label>
          <select name="role" id="role" class="form-select" required onchange="toggleProviderType()">
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $key => $label): ?>
              <option value="<?= htmlspecialchars($key) ?>"
                <?= (($_POST['role'] ?? '') === $key) ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3" id="providerTypeBox" style="display:none;">
          <label class="form-label">Provider Type (optional)</label>
          <select name="provider_type" class="form-select">
            <option value="">-- Select Provider Type --</option>
            <?php foreach ($providerTypes as $key => $label): ?>
              <option value="<?= htmlspecialchars($key) ?>"
                <?= (($_POST['provider_type'] ?? '') === $key) ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button class="btn btn-primary w-100">Register</button>

        <div class="text-center mt-3">
          Already have an account?
          <a href="/homeplan/auth/login.php">Login</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleProviderType() {
  const role = document.getElementById('role').value;
  const box = document.getElementById('providerTypeBox');

  // show provider_type for non-client provider roles only
  const providerRoles = ['property_owner','developer','architect','material_provider','worker_provider','interior_designer'];
  if (providerRoles.includes(role)) box.style.display = 'block';
  else box.style.display = 'none';
}
toggleProviderType();
</script>

</body>
</html>



