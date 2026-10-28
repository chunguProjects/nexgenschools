<?php
session_start();

// Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    $roleId = $_SESSION['user']['role_id'] ?? null;

    switch ($roleId) {
        case 2: header("Location: ../dashboard/admin.php"); exit;
        case 3: header("Location: ../dashboard/teacher.php"); exit;
    }
}

$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType = $_SESSION['flash_type'] ?? null;
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// --- Branding, same source as index.php, so login matches the rest of the site ---
include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

$settings = [];
if ($conn) {
    $model = new GeneralSettingsModel($conn);
    $settings = $model->getSettings();
}

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath   = $settings['logo_path'] ?? null;

$logoUrl = null;
if ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)) {
    $logoUrl = BASE_URL . '/' . ltrim($logoPath, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($schoolName) ?></title>
   <?php if (!empty($logoUrl)): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <?php endif; ?>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

  <!-- Ledger theme (variables + all component styles) -->
  <link rel="stylesheet" href="../css/style.css">

</head>

<body>
 

  <div class="lg-wrap">
    <div class="lg-card">
      <p class="lg-eyebrow">Staff portal</p>
      <h1>Login</h1>
      <p class="lg-sub">Login to your account</p>
      <div class="lg-divider"></div>

      <!-- ✅ FLASH MESSAGE INSIDE FORM -->
      <?php if ($flashMessage): ?>
          <div class="alert alert-<?= $flashType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
              <?= htmlspecialchars($flashMessage) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      <?php endif; ?>

      <form action="../controllers/login.php" method="post">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input
            type="text"
            name="username"
            id="username"
            class="form-control"
            placeholder="Enter your username"
            required
          />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            placeholder="Enter your password"
            required
          />
        </div>
        <button type="submit" class="lg-btn-solid">Login</button>

        <div class="mt-3 text-center">
          <small class="text-muted">
            Forgot your password? <a href="#">Reset here</a>
          </small>
        </div>

        <!-- Back Button -->
        <div class="mt-3">
          <a href="../index.php" class="lg-btn-ghost d-inline-block text-center">&larr; Back to Homepage</a>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    setTimeout(() => {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            alert.classList.add('hide');
        }
    }, 4000);

  (function () {
    const INACTIVITY_LIMIT = 10 * 60 * 1000; // 10 minutes, in ms
    let timer;

    function resetTimer() {
      clearTimeout(timer);
      timer = setTimeout(() => {
        window.location.href = '../views/login.php?timeout=1';
      }, INACTIVITY_LIMIT);
    }

    ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'].forEach(evt => {
      document.addEventListener(evt, resetTimer, { passive: true });
    });

    resetTimer();
  })();
  </script>
</body>
</html>