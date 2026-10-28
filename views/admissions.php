<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This file lives in views/, so paths back to the project root need "../"
include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

$settings = [];
if ($conn) {
    $model = new GeneralSettingsModel($conn);
    $settings = $model->getSettings();
}

$schoolName   = $settings['school_name'] ?? 'NextGen Schools';
$logoPath     = $settings['logo_path'] ?? null;
$logoUrl      = ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)) ? '../' . $logoPath : null;
$contactEmail = $settings['contact_email'] ?? '';
$contactPhone = $settings['contact_phone'] ?? '';
$contactAddress = $settings['address'] ?: 'Lilongwe, Malawi';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($schoolName) ?></title>
  <?php if (!empty($logoUrl)): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

  <!-- Same theme as the homepage — everything now lives in one stylesheet -->
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark lp-navbar fixed-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="../index.php">
        <?php if ($logoUrl): ?>
          <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($schoolName) ?> Logo"
               class="me-2 rounded-circle" style="max-height: 34px; max-width: 34px; object-fit: contain;">
        <?php else: ?>
          <i class="fa-solid fa-school me-2"></i>
        <?php endif; ?>
        <?= htmlspecialchars($schoolName) ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="../index.php#why-us">Why Choose Us</a></li>
          <li class="nav-item"><a class="nav-link active" href="admissions.php">Admissions</a></li>
          <li class="nav-item"><a class="nav-link" href="view-announcements.php"><i class="fa-solid fa-bullhorn me-1"></i> Announcements</a></li>
        </ul>
        <a class="lp-btn-ghost ms-lg-3 mt-2 mt-lg-0 d-inline-block text-center" href="student-portal.php">
          <i class="fa-solid fa-file-lines me-1"></i> View Results
        </a>
        <a class="lp-btn-solid ms-lg-2 mt-2 mt-lg-0 d-inline-block text-center" href="login.php">
          <i class="fa-solid fa-user-lock me-1"></i> Staff Login
        </a>
      </div>
    </div>
  </nav>

  <div class="lp-admissions-hero">
    <div class="container">
      <h1>Admissions</h1>
      <p class="mb-0">Join <?= htmlspecialchars($schoolName) ?> for the new academic year.</p>
    </div>
  </div>

  <section class="lp-about">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="lp-step-card">
            <div class="lp-step-num mb-2">1</div>
            <h5>Enquire</h5>
            <p style="color:var(--muted);">Contact our admissions office by phone or email to ask about available places and requirements.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="lp-step-card">
            <div class="lp-step-num mb-2">2</div>
            <h5>Submit Documents</h5>
            <p style="color:var(--muted);">Bring the completed application form along with your child's academic records and identification documents.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="lp-step-card">
            <div class="lp-step-num mb-2">3</div>
            <h5>Confirmation</h5>
            <p style="color:var(--muted);">Our office will review your application and contact you to confirm placement and orientation details.</p>
          </div>
        </div>
      </div>

      <div class="text-center mt-5">
        <h4 class="mb-2">Ready to apply?</h4>
        <p style="color:var(--muted);">Reach out to our admissions office using the details below.</p>
        <div class="lp-card d-inline-block text-start p-4 mt-2">
          <p class="mb-2"><i class="fa-solid fa-location-dot me-2"></i> <?= htmlspecialchars($contactAddress) ?></p>
          <?php if ($contactPhone): ?>
            <p class="mb-2"><i class="fa-solid fa-phone me-2"></i> <?= htmlspecialchars($contactPhone) ?></p>
          <?php endif; ?>
          <?php if ($contactEmail): ?>
            <p class="mb-0"><i class="fa-solid fa-envelope me-2"></i>
              <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <div class="container text-center py-4">
    <a href="../index.php" class="lp-btn-ghost" style="display:inline-block;">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to Home
    </a>
  </div>

<?php include('general-footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>