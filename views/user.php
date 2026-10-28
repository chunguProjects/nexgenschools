<?php
include_once(__DIR__ . '/../config/db.php');
include_once(__DIR__ . '/../model/user.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$userModel = new User($conn);

// Fetch separated user groups
$admins   = $userModel->getUsersByRoleId(1);   // schooladmin
$teachers = $userModel->getUsersByRoleId(2);   // teacher
?>


<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="admin.php?page=dashboard" class="btn btn-sm btn-ledger-outline me-3">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <a href="admin.php?page=add-user-form" class="btn btn-sm btn-ledger-solid">
            <i class="fa-solid fa-plus"></i> Add User
        </a>
    </div>

    <ul class="nav nav-tabs ledger-tabs" id="userTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">
                Admins
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teachers" type="button" role="tab">
                Teachers
            </button>
        </li>
    </ul>

    <div class="tab-content pt-3">
        <!-- Admins -->
        <div class="tab-pane fade show active" id="admins" role="tabpanel">
            <?php renderUserTable($admins); ?>
        </div>

        <!-- Teachers -->
        <div class="tab-pane fade" id="teachers" role="tabpanel">
            <?php renderUserTable($teachers); ?>
        </div>
    </div>

    <!-- Hidden delete form -->
    <form id="deleteUserForm" method="POST" action="../controllers/user-controller.php" style="display: none;">
        <input type="hidden" name="delete_user" value="1">
        <input type="hidden" name="user_id" id="delete_user_id">
    </form>
</div>

<script>
document.querySelectorAll('.deleteUserBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const userId = this.getAttribute('data-id');

        if (confirm("Are you sure you want to delete this user?")) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('deleteUserForm').submit();
        }
    });
});
</script>

<?php
// Reusable function for user table rendering
function renderUserTable($users) {
    if ($users && $users->num_rows > 0): ?>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-hover align-middle mb-0 ledger-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width: 150px;">Actions</th>
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
                            <td><span class="badge bg-info"><?= htmlspecialchars($user['role_name']) ?></span></td>
                            <td>
                                <button 
                                    class="btn btn-sm btn-ledger-outline"
                                    onclick="location.href='admin.php?page=add-user-form&user_id=<?= $user['user_id'] ?>'">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <button 
                                    class="btn btn-sm btn-ledger-danger deleteUserBtn" 
                                    data-id="<?= $user['user_id'] ?>">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No users found in this category.</div>
    <?php endif;
}
?>