<?php
include_once(__DIR__ . '/../config/db.php');
include_once(__DIR__ . '/../model/user.php');

if (session_status() === PHP_SESSION_NONE) session_start();

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

// Fetch roles allowed for creation
$allowedRolesResult = $conn->query("SELECT role_id, name FROM roles WHERE name IN ('admin', 'teacher')");
?>


<div class="container mt-4">
    <div class="lg-form-card">
        <h4><?= $isEdit ? 'Edit User' : 'Add New User' ?></h4>
        <p class="text-muted mb-3" style="font-size: 13.5px;">
            <?= $isEdit ? 'Update account details for this user.' : 'Create a new admin or teacher account.' ?>
        </p>
        <div class="lg-form-divider"></div>

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
                           value="<?= $isEdit ? htmlspecialchars($existingUser['username']) : '' ?>"
                           <?= $isEdit ? 'readonly' : '' ?>>
                </div>
            </div>

            <!-- Country Code and Phone -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Country Code</label>
                    <select class="form-select" name="country_code" required <?= $isEdit ? 'disabled' : '' ?>>
                        <option value="+265" <?= $isEdit && $existingUser['country_code'] === '+265' ? 'selected' : '' ?>>
                            Malawi (+265)
                        </option>
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
                            <option value="<?= $role['role_id'] ?>" 
                                <?= $isEdit && $existingUser['role_id'] == $role['role_id'] ? 'selected' : '' ?>>
                                <?= ucfirst($role['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- Password fields -->
            <div class="row mb-4">
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

            <!-- Buttons -->
            <div class="d-flex justify-content-start">
                <button type="submit" class="btn btn-ledger-solid me-2">
                    <?= $isEdit ? 'Update User' : 'Add User' ?>
                </button>
                <a href="admin.php?page=user" class="btn btn-ledger-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateForm() {
    const pwd = document.getElementById("password").value;
    const confirmPwd = document.getElementById("confirm_password").value;

    if (pwd !== confirmPwd) {
        alert("Passwords do not match.");
        return false;
    }

    if (pwd && pwd.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
    }

    return true;
}
</script>