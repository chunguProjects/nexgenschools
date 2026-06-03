<?php
include_once(__DIR__ . '/../config/db.php');
include_once(__DIR__ . '/../model/GeneralSettingsModel.php');

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings();
$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath = $settings['logo_path'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($schoolName) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      border-radius: 1rem;
      box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.25);
    }
    .card h1 {
      font-weight: 700;
      color: #4e73df;
    }
    .card h2 {
      font-size: 1.2rem;
      color: #555;
      margin-bottom: 1rem;
    }
    .form-control:focus {
      border-color: #1cc88a;
      box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
    }
    .btn-primary {
      background-color: #4e73df;
      border: none;
      font-weight: 600;
    }
    .btn-primary:hover {
      background-color: #2e59d9;
    }
    .btn-outline-secondary {
      border-radius: 50px;
    }
    a {
      text-decoration: none;
    }
    .portal-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .portal-header img {
      height: 60px;
      margin-bottom: 1rem;
    }
    .portal-subtitle {
      font-size: 1rem;
      color: #6c757d;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-12 col-md-6 col-lg-5">
        <div class="card p-4">
          <div class="portal-header">
            <!-- Dynamic School Logo -->
            <?php if ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)): ?>
              <img src="<?= htmlspecialchars('../' . $logoPath) ?>" alt="<?= htmlspecialchars($schoolName) ?> Logo">
            <?php else: ?>
              <h1><?= htmlspecialchars($schoolName) ?></h1>
            <?php endif; ?>
            <h2>Student Portal</h2>
            <p class="portal-subtitle">Access your exam results securely with your student number and date of birth.</p>
          </div>

          <form action="../controllers/login.php" method="post">
            <div class="mb-3">
              <label for="student_number" class="form-label">Student Number</label>
              <input
                type="text"
                name="student_number"
                id="student_number"
                class="form-control"
                placeholder="Enter your student number"
                required
              />
            </div>

            <div class="mb-3">
              <label for="dob" class="form-label">Date of Birth</label>
              <input
                type="date"
                name="dob"
                id="dob"
                class="form-control"
                required
              />
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">View Results</button>

            <div class="text-center mb-3">
              <small class="text-muted">
                Trouble logging in? <a href="#">Contact support</a>
              </small>
            </div>

            <a href="../index.php" class="btn btn-outline-secondary w-100">← Back to Homepage</a>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>
