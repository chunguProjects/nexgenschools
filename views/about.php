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

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath   = $settings['logo_path'] ?? null;
$logoUrl    = ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)) ? '../' . $logoPath : null;

$aboutText   = $settings['about_text'] ?: "Our system provides a modern solution for school management. From student admissions to report cards and communication, everything is simplified in one platform.";
$visionText  = $settings['vision_text']  ?? 'To be a leading institution shaping future-ready learners.';
$missionText = $settings['mission_text'] ?? 'To provide quality, holistic education that empowers every student to reach their full potential.';
$valuesText  = $settings['values_text']  ?? 'Integrity, Excellence, Respect, and Community.';

$aboutImagePath = $settings['about_image'] ?? null;
if ($aboutImagePath && file_exists(__DIR__ . '/../' . $aboutImagePath)) {
    $aboutImageUrl = '../' . $aboutImagePath;
} else {
    $fallbackHero = $settings['hero_image_path'] ?? null;
    $aboutImageUrl = ($fallbackHero && file_exists(__DIR__ . '/../' . $fallbackHero)) ? '../' . $fallbackHero : '../images/school.png';
}

$htName    = $settings['head_teacher_name'] ?? '';
$htMessage = $settings['head_teacher_message'] ?? '';
$htPhoto   = $settings['head_teacher_photo'] ?? null;
$htPhotoUrl = ($htPhoto && file_exists(__DIR__ . '/../' . $htPhoto)) ? '../' . $htPhoto : '../images/avatar-placeholder.png';
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
          <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="../index.php#why-us">Why Choose Us</a></li>
          <li class="nav-item"><a class="nav-link" href="admissions.php">Admissions</a></li>
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

  <div class="lp-about-hero" style="background-image: linear-gradient(135deg, rgba(18,79,50,.92), rgba(11,51,32,.92)), url('<?= htmlspecialchars($aboutImageUrl) ?>');">
    <div class="container">
      <h1>About <?= htmlspecialchars($schoolName) ?></h1>
    </div>
  </div>

  <section class="lp-about">
    <div class="container">
      <div class="lp-about-block">
        <h5>Who We Are</h5>
        <p style="white-space:pre-line;"><?= htmlspecialchars($aboutText) ?></p>

        <h5>Our Vision</h5>
        <p style="white-space:pre-line;"><?= htmlspecialchars($visionText) ?></p>

        <h5>Our Mission</h5>
        <p style="white-space:pre-line;"><?= htmlspecialchars($missionText) ?></p>

        <?php if ($valuesText): ?>
          <h5>Core Values</h5>
          <p style="white-space:pre-line;"><?= htmlspecialchars($valuesText) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if ($htName): ?>
  <section class="lp-about" style="background:var(--rule, #f4f1ea);">
    <div class="container">
      <div class="row justify-content-center align-items-center g-4">
        <div class="col-md-3 text-center">
          <img src="<?= htmlspecialchars($htPhotoUrl) ?>" alt="<?= htmlspecialchars($htName) ?>"
               class="rounded-circle lp-ht-photo">
        </div>
        <div class="col-md-7">
          <p class="lp-ht-quote">"<?= htmlspecialchars($htMessage) ?>"</p>
          <p class="lp-eyebrow mb-0">— <?= htmlspecialchars($htName) ?>, Head Teacher</p>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <div class="container text-center py-5">
    <a href="../index.php" class="lp-btn-ghost" style="display:inline-block;">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to Home
    </a>
  </div>

<?php include('general-footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>