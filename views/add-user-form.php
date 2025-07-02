<?php
include('../config/db.php');

// Assume current school admin session is set
$loggedInSchoolId = $_SESSION['user']['school_id'];
$allowedRolesResult = $conn->query("SELECT role_id, name FROM roles WHERE name IN ('schooladmin', 'teacher')");
?>

<div class="container mt-5">
    <h4 class="mb-4">Add New User</h4>

    <!-- Display error/success messages if any -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="../controllers/user-controller.php" method="POST" onsubmit="return validateForm();">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="full_name" id="full_name" required>
            </div>
            <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="country_code" class="form-label">Country Code</label>
                <select class="form-select" name="country_code" id="country_code" required>
                    <option value="" disabled selected>Select Code</option>
                    <option value="+265">Malawi (+265)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" name="phone_number" id="phone_number" required>
            </div>
        </div>

      <div class="row mb-3">
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" id="email" required>
    </div>
    <div class="col-md-6">
        <label for="role_id" class="form-label">Role</label>
        <select class="form-select" name="role_id" id="role_id" required>
            <option value="" disabled selected>Select Role</option>
            <?php while ($role = $allowedRolesResult->fetch_assoc()): ?>
                <option value="<?= $role['role_id'] ?>"><?= ucfirst($role['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
</div>
<!-- Hidden input: school ID -->
<input type="hidden" name="school_id" value="<?= $loggedInSchoolId ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
            </div>
        </div>

        <div class="d-flex justify-content-start">
            <button type="submit" name="add_user" class="btn btn-warning me-2">Add User</button>
            <a href="index.php?page=user" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    function validateForm() {
        const pwd = document.getElementById("password").value;
        const confirmPwd = document.getElementById("confirm_password").value;
        if (pwd !== confirmPwd) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }
</script>
