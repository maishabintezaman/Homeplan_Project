<?php
session_start();
if (!empty($_SESSION['user_id'])) {
  header("Location: /homeplan/index.php");
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>HomePlan - Architect Registration</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:720px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h2 class="mb-2 text-center">HomePlan</h2>
      <h5 class="mb-4 text-center text-muted">Architect Registration</h5>

      <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">Your account has been created successfully. You can login now.</div>
      <?php endif; ?>

      <?php if (($_GET['err'] ?? '') === 'email'): ?>
        <div class="alert alert-danger">This email is already registered.</div>
      <?php elseif (($_GET['err'] ?? '') === 'cert'): ?>
        <div class="alert alert-danger">This certificate number is already registered.</div>
      <?php elseif (($_GET['err'] ?? '') === 'missing'): ?>
        <div class="alert alert-danger">Please fill in all required fields.</div>
      <?php elseif (($_GET['err'] ?? '') === 'server'): ?>
        <div class="alert alert-danger">Registration failed. Please try again.</div>
      <?php endif; ?>

      <form method="post" action="/homeplan/auth/register_architect_action.php" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input name="full_name" type="text" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input name="phone" type="text" class="form-control" placeholder="01XXXXXXXXX">
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>

        <hr class="my-2">

        <div class="col-md-6">
          <label class="form-label">Architecture Certificate Number</label>
          <input name="certificate_number" type="text" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Years of Experience</label>
          <input name="years_experience" type="number" class="form-control" min="0" value="0" required>
        </div>

        <div class="col-12">
          <label class="form-label">Expertise</label>
          <input
  name="expertise"
  type="text"
  class="form-control"
  placeholder="e.g. commercial, residential, mosque etc."
  required
>
<div class="form-text">Write your expertise separated by commas.</div>

        </div>

        <div class="col-12">
          <label class="form-label">Portfolio URL (optional)</label>
          <input name="portfolio_url" type="url" class="form-control" placeholder="https://">
        </div>

        <div class="col-12 d-flex gap-2">
          <button class="btn btn-primary">Create Account</button>
          <a class="btn btn-outline-secondary" href="/homeplan/auth/login.php">Back to Login</a>
        </div>
      </form>

    </div>
  </div>
</div>
</body>
</html>



