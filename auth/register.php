<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

$expertiseList = [];
try {
  $q = $conn->query("SELECT expertise_id, type FROM expertise ORDER BY type ASC");
  while ($row = $q->fetch_assoc()) $expertiseList[] = $row;
} catch (Throwable $e) {
  // keep empty
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .card-wrap { max-width: 520px; margin: 40px auto; }
    .expertise-box { max-height: 210px; overflow:auto; border:1px solid #dee2e6; border-radius:12px; padding:12px; }
  </style>
</head>
<body class="bg-light">

<div class="card-wrap">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h2 class="mb-4">Create Account</h2>

      <?php if (!empty($_GET['err'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['err']) ?></div>
      <?php endif; ?>

      <form method="post" action="/homeplan/auth/register_post.php" class="d-grid gap-3">

        <div>
          <label class="form-label">Full Name</label>
          <input name="full_name" class="form-control" required>
        </div>

        <div>
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
        </div>

        <div>
          <label class="form-label">Phone</label>
          <input name="phone" class="form-control" required>
        </div>

        <div>
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" minlength="6" required>
          <div class="text-muted small mt-1">Minimum 6 characters</div>
        </div>

        <div>
          <label class="form-label">Register As (Role)</label>
          <select name="role" id="role" class="form-select" required>
            <option value="client">Client</option>
            <option value="property_owner">Property Owner</option>
            <option value="developer">Developer</option>
            <option value="architect">Architect</option>
            <option value="material_provider">Material Provider</option>
            <option value="worker_provider">Worker Provider</option>
            <option value="interior_designer">Interior Designer</option>
          </select>
        </div>

        <!-- Architect extra fields -->
        <div id="architectFields" class="d-none">
          <hr class="my-2">

          <div>
            <label class="form-label">Architect Certificate / License No</label>
            <input name="architect_license_no" class="form-control" placeholder="e.g. ARC-12345">
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Years of Experience</label>
              <input name="architect_years" type="number" min="0" value="0" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Portfolio (optional)</label>
              <input name="architect_portfolio" class="form-control" placeholder="URL or short text">
            </div>
          </div>

          <div>
            <label class="form-label">Expertise (select one or more)</label>

            <?php if (count($expertiseList) === 0): ?>
              <div class="alert alert-warning mb-0">
                Expertise list is empty. Insert rows into <code>expertise</code> table first.
              </div>
            <?php else: ?>
              <div class="expertise-box">
                <?php foreach ($expertiseList as $ex): ?>
                  <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="expertise_ids[]"
                           id="ex<?= (int)$ex['expertise_id'] ?>"
                           value="<?= (int)$ex['expertise_id'] ?>">
                    <label class="form-check-label" for="ex<?= (int)$ex['expertise_id'] ?>">
                      <?= htmlspecialchars($ex['type']) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="text-muted small mt-1">
                Tip: You can tick multiple checkboxes.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Developer extra fields (basic for now, DB table needed to store) -->
        <div id="developerFields" class="d-none">
          <hr class="my-2">
          <div>
            <label class="form-label">Developer License No</label>
            <input name="developer_license_no" class="form-control" placeholder="e.g. DEV-7788">
            <div class="text-muted small mt-1">
              (This field is collected now. We’ll save it once your developer profile table is confirmed.)
            </div>
          </div>
        </div>

        <button class="btn btn-primary btn-lg" type="submit">Register</button>

        <div class="text-center">
          Already have an account? <a href="/homeplan/auth/login.php">Login</a>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  const roleSel = document.getElementById('role');
  const architectFields = document.getElementById('architectFields');
  const developerFields = document.getElementById('developerFields');

  function toggleRoleFields() {
    const role = roleSel.value;

    architectFields.classList.toggle('d-none', role !== 'architect');
    developerFields.classList.toggle('d-none', role !== 'developer');

    // If not architect, clear checked expertise to avoid accidental submission
    if (role !== 'architect') {
      document.querySelectorAll('input[name="expertise_ids[]"]').forEach(cb => cb.checked = false);
      const aLic = document.querySelector('input[name="architect_license_no"]');
      if (aLic) aLic.value = '';
    }
  }

  roleSel.addEventListener('change', toggleRoleFields);
  toggleRoleFields();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


