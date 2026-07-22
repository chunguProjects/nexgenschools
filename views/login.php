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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">
  <div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow p-4">
          <div class="text-center mb-4">
            <h1 class="h4">Login</h1>
            <p class="text-muted mb-0">Login to your account</p>
          </div>
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
            <button type="submit" class="btn btn-primary w-100">Login</button>
           <div class="mt-3 text-center">
            <small class="text-muted">
              Forgot your password? <a href="#">Reset here</a>
            </small>
          </div>

          <!-- Back Button -->
          <div class="mt-3">
            <a href="../index.php" class="btn btn-outline-secondary w-100">← Back to Homepage</a>
          </div>
          </form>
        </div>
      </div>
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
</script>
</body>
</html>
