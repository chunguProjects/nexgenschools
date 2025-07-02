<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'superadmin') {
    header("Location: ../view/login.php");
    exit;
}

include('../config/db.php');

// Fetch all schools
$result = $conn->query("SELECT * FROM schools ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schools</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="mb-4 text-center">Manage Schools</h3>

    <!-- Add School Button -->
    <div class="mb-3 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSchoolModal">
            <i class="fa fa-plus"></i> Add School
        </button>
    </div>

    <!-- Schools Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>School Name</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $count = 1; while ($school = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($school['name']) ?></td>
                            <td>
                                <span class="badge <?= $school['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($school['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($school['created_at'])) ?></td>
                            <td>
                                <a href="edit-school.php?id=<?= $school['school_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php if ($school['status'] === 'active'): ?>
                                    <a href="../controllers/toggle-school-status.php?id=<?= $school['school_id'] ?>&status=inactive" class="btn btn-sm btn-danger">Deactivate</a>
                                <?php else: ?>
                                    <a href="../controllers/toggle-school-status.php?id=<?= $school['school_id'] ?>&status=active" class="btn btn-sm btn-success">Activate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No schools found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addSchoolModal" tabindex="-1" aria-labelledby="addSchoolModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="../controllers/school-controller.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New School & Assign Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- School Info -->
        <h6 class="text-primary mb-3">School Details</h6>
        <?php if (isset($_SESSION['modal_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert" id="modalMessage">
    <?= $_SESSION['modal_success']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php unset($_SESSION['modal_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['modal_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert" id="modalMessage">
    <?= $_SESSION['modal_error']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php unset($_SESSION['modal_error']); ?>
<?php endif; ?>

        <div class="row mb-3">
          <div class="col-md-8">
            <label class="form-label">School Name</label>
            <input type="text" name="school_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Admin Info -->
        <h6 class="text-success mb-3">Assign School Admin</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="admin_full_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" name="admin_username" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="admin_email" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Country Code</label>
            <select name="admin_country_code" class="form-select" required>
              <option value="+265" selected>Malawi (+265)</option>
              <option value="+254">Kenya (+254)</option>
              <option value="+260">Zambia (+260)</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Phone</label>
            <input type="text" name="admin_phone" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="admin_password" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="admin_confirm_password" class="form-control" required>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="add_school" class="btn btn-primary">Create School</button>
      </div>
    </form>
  </div>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const hasModalMsg = document.querySelector('#modalMessage');
    if (hasModalMsg) {
        const modal = new bootstrap.Modal(document.getElementById('addSchoolModal'));
        modal.show();

        // Auto fade after 5 seconds
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(document.getElementById('modalMessage'));
            alert.close();
        }, 5000);
    }
});
</script>

</body>
</html>
