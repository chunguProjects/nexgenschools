<?php
session_start();

// Redirect if not superadmin
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'superadmin') {
    header("Location: ../views/login.php");
    exit;
}

$superAdminName = $_SESSION['user']['full_name'] ?? 'Super Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Super Admin Dashboard</title>
  <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css"/>
  <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
<div class="container py-5">

  <!-- Header -->
  <div class="text-center mb-5">
    <h2 class="fw-bold">Welcome, <?= htmlspecialchars($superAdminName) ?></h2>
    <p class="text-muted">System-wide Super Admin Dashboard</p>
  </div>

  <!-- Quick Stats -->
  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <i class="fa-solid fa-school fa-2x text-primary mb-2"></i>
          <h6>Total Schools</h6>
          <h3 class="fw-bold">12</h3> <!-- Replace with dynamic count -->
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <i class="fa-solid fa-users fa-2x text-success mb-2"></i>
          <h6>Total Users</h6>
          <h3 class="fw-bold">542</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <i class="fa-solid fa-right-to-bracket fa-2x text-warning mb-2"></i>
          <h6>Logins Today</h6>
          <h3 class="fw-bold">34</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <i class="fa-solid fa-toggle-on fa-2x text-info mb-2"></i>
          <h6>Active Schools</h6>
          <h3 class="fw-bold">10</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Functional Areas -->
  <div class="row g-4">
    <div class="col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fa-solid fa-school fa-2x mb-3 text-primary"></i>
          <h5>Manage Schools</h5>
          <p class="small text-muted">Create, update or deactivate schools.</p>
          <a href="manage-schools.php" class="btn btn-sm btn-outline-primary">Go</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fa-solid fa-user-shield fa-2x mb-3 text-success"></i>
          <h5>Manage Global Users</h5>
          <p class="small text-muted">Assign school admins and manage access.</p>
          <a href="../views/index.php?page=user" class="btn btn-sm btn-outline-success">Go</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fa-solid fa-chart-line fa-2x mb-3 text-warning"></i>
          <h5>Reports & Insights</h5>
          <p class="small text-muted">View system-wide reports and usage stats.</p>
          <a href="../views/index.php?page=report" class="btn btn-sm btn-outline-warning">Go</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fa-solid fa-headset fa-2x mb-3 text-danger"></i>
          <h5>Support & Notices</h5>
          <p class="small text-muted">Manage support requests and post notices.</p>
          <a href="../views/index.php?page=support" class="btn btn-sm btn-outline-danger">Go</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Logout -->
  <div class="text-center mt-5">
    <a href="../controllers/logout.php" class="btn btn-outline-dark">Logout</a>
  </div>

</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
