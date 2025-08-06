<?php
include('../config/db.php');
include('../model/user.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$loggedInSchoolId = $_SESSION['user']['school_id'];
$userModel = new User($conn);

// Check if editing
$isEdit = isset($_GET['user_id']);
$existingUser = null;

if ($isEdit) {
    $userId = (int) $_GET['user_id'];
    $existingUser = $userModel->getUserById($userId);

    if (!$existingUser) {
        echo "<div class='alert alert-danger'>User not found.</div>";
        exit;
    }
}

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
    <?php if ($isEdit): ?>
        <input type="hidden" name="update_user" value="1">
        <input type="hidden" name="user_id" value="<?= $existingUser['user_id'] ?>">
    <?php else: ?>
        <input type="hidden" name="add_user" value="1">
    <?php endif; ?>

    <!-- Full Name and Username -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="full_name" required
                   value="<?= $isEdit ? htmlspecialchars($existingUser['full_name']) : '' ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" required
                   value="<?= $isEdit ? htmlspecialchars($existingUser['username']) : '' ?>" <?= $isEdit ? 'readonly' : '' ?>>
        </div>
    </div>

    <!-- Country Code and Phone -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Country Code</label>
            <select class="form-select" name="country_code" required <?= $isEdit ? 'disabled' : '' ?>>
                <option value="+265" <?= $isEdit && $existingUser['country_code'] === '+265' ? 'selected' : '' ?>>Malawi (+265)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone_number" required
                   value="<?= $isEdit ? htmlspecialchars($existingUser['phone_number']) : '' ?>">
        </div>
    </div>

    <!-- Email and Role -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required
                   value="<?= $isEdit ? htmlspecialchars($existingUser['email']) : '' ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select class="form-select" name="role_id" required <?= $isEdit ? 'disabled' : '' ?>>
                <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Role</option>
                <?php while ($role = $allowedRolesResult->fetch_assoc()): ?>
                    <option value="<?= $role['role_id'] ?>" <?= $isEdit && $existingUser['role_id'] == $role['role_id'] ? 'selected' : '' ?>>
                        <?= ucfirst($role['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <!-- Password fields -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">
                <?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password' ?>
            </label>
            <input type="password" class="form-control" name="password" id="password" <?= $isEdit ? '' : 'required' ?>>
        </div>
        <div class="col-md-6">
            <label class="form-label"><?= $isEdit ? 'Confirm New Password' : 'Confirm Password' ?></label>
            <input type="password" class="form-control" name="confirm_password" id="confirm_password" <?= $isEdit ? '' : 'required' ?>>
        </div>
    </div>

    <!-- Hidden: School ID -->
    <input type="hidden" name="school_id" value="<?= $loggedInSchoolId ?>">

    <!-- Buttons -->
    <div class="d-flex justify-content-start">
        <button type="submit" class="btn btn-primary me-2">
            <?= $isEdit ? 'Update User' : 'Add User' ?>
        </button>
        <a href="schooladmin.php?page=user" class="btn btn-secondary">Cancel</a>
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
