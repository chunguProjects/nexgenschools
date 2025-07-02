<?php
// Load DB and model
include('../config/db.php');
include('../model/user.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Create user model
$userModel = new User($conn);

// Get school_id from logged-in user
$school_id = $_SESSION['user']['school_id'] ?? null;

// Fetch only users from this school
$users = $userModel->getAllUsers($school_id);
?>

<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>User Management</h4>
        <a href="schooladmin.php?page=add-user-form" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add User
        </a>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Users List</h5>
        </div>
        <div class="card-body">
            <?php if ($users && $users->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['user_id'] ?></td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['country_code'] . ' ' . $user['phone_number']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= ucfirst($user['role_name']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editUserBtn" data-id="<?= $user['user_id'] ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger deleteUserBtn" data-id="<?= $user['user_id'] ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No users found for this school.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
