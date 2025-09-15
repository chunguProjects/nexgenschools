<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>NextGen Schools</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <!-- Top Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">NextGen Schools</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm ms-2" href="views/login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="bg-light text-dark text-center py-5">
    <div class="container">
      <h1 class="display-4 fw-bold">Welcome to NextGen Schools</h1>
      <p class="lead">Smart, simple, and modern school management system.</p>
      <a href="views/login.php" class="btn btn-primary btn-lg mt-3">Login Now</a>
    </div>
  </header>

  <!-- Features -->
  <section id="features" class="py-5">
    <div class="container text-center">
      <div class="row">
        <div class="col-md-4">
          <i class="fa-solid fa-chalkboard-teacher fa-3x mb-3 text-primary"></i>
          <h5>Teacher Tools</h5>
          <p>Manage classes, subjects, and grades with ease.</p>
        </div>
        <div class="col-md-4">
          <i class="fa-solid fa-users fa-3x mb-3 text-success"></i>
          <h5>Parent Access</h5>
          <p>Stay updated with student progress and school events.</p>
        </div>
        <div class="col-md-4">
          <i class="fa-solid fa-school fa-3x mb-3 text-warning"></i>
          <h5>School Admin</h5>
          <p>Full control and powerful reporting for administrators.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="bg-light py-5">
    <div class="container text-center">
      <h2>About Us</h2>
      <p class="mt-3">NextGen Schools is a modern solution for school management. From student admissions 
         to report cards and communication, everything is simplified in one platform.</p>
    </div>
  </section>

  <!-- Contact -->
  <section id="contact" class="py-5">
    <div class="container text-center">
      <h2>Contact Us</h2>
      <p class="mt-3">Email: info@nextgenschools.mw | Phone: +265 999 000 000</p>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-light text-center py-3">
    <div class="container">
      <small>&copy; <?php echo date("Y"); ?> NextGen Schools. All Rights Reserved.</small>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
