<?php
include('config/db.php');
require_once('model/GeneralSettingsModel.php');

$settings = [];

if ($conn) {
    $model = new GeneralSettingsModel($conn);
    $settings = $model->getSettings();
}

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath   = $settings['logo_path'] ?? null;
$motto      = $settings['motto'] ?? 'Smart School Management System';
$contactEmail = $settings['contact_email'] ?? '';
$contactPhone = $settings['contact_phone'] ?? '';


// Check if logo file exists
$logoUrl = null;
if ($logoPath && file_exists(__DIR__ . '/' . $logoPath)) {
    $logoUrl = $logoPath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="description" content="School Management System for managing students, teachers, examinations and reports.">

<meta name="keywords" content="School Management System, Student Portal, Results, Education, Malawi">

<meta name="author" content="Steven Chungu">
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($schoolName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
        <?php if ($logoUrl): ?>
          <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($schoolName) ?> Logo"
               class="me-2 rounded" style="max-height: 35px; max-width: 35px; object-fit: contain;">
        <?php else: ?>
          <i class="fa-solid fa-school me-2"></i>
        <?php endif; ?>
        <?= htmlspecialchars($schoolName) ?>
      </a>
      <!-- <span class="text-light small ms-2"><?= htmlspecialchars($motto) ?></span> -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="views/announcements.php"><i class="fa-solid fa-bullhorn me-1"></i> Announcements</a></li>
        </ul>
        <!-- Navbar Buttons -->
        <a class="btn btn-outline-light btn-sm ms-3" href="views/student-portal.php">
          <i class="fa-solid fa-file-lines me-1"></i> View Results
        </a>
        <a class="btn btn-light btn-sm ms-2" href="views/login.php">
          <i class="fa-solid fa-user-lock me-1"></i> Staff Login
        </a>
      </div>
    </div>
  </nav>

  <!-- Hero Section (Floating Image Only) -->
  <header class="bg-light py-5">
    <div class="container text-center">
      <img src="assets/hero-illustration.png" alt="School Illustration" class="img-fluid rounded shadow" style="max-width: 450px;">
    </div>
  </header>

  <!-- Features Section -->
  <section id="features" class="py-5">
    <div class="container text-center">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <i class="fa-solid fa-chalkboard-teacher fa-3x mb-3 text-primary"></i>
              <h5>Teacher Tools</h5>
              <p>Manage classes, subjects, and grades with ease.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <i class="fa-solid fa-users fa-3x mb-3 text-success"></i>
              <h5>Parent Access</h5>
              <p>Stay updated with student progress and school events.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <i class="fa-solid fa-school fa-3x mb-3 text-warning"></i>
              <h5>School Admin</h5>
              <p>Full control and powerful reporting for administrators.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="bg-light py-5">
    <div class="container text-center">
      <h2>About Us</h2>
      <p class="mt-3">Our system provides modern solution for school management. From student admissions 
         to report cards and communication, everything is simplified in one platform.</p>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="py-5">
    <div class="container text-center">
      <h2>Contact Us</h2>
      <p class="mt-3">
        <?php if ($contactEmail): ?>
          Email: <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a>
        <?php endif; ?>
        <?php if ($contactPhone): ?>
          | Phone: <?= htmlspecialchars($contactPhone) ?>
        <?php endif; ?>
      </p>
    </div>
  </section>

  <!-- Footer -->
<?php include('views/general-footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
