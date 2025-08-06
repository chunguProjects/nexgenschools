<?php
include('../config/db.php');
include('../model/user.php');
include('../model/student.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$userModel = new User($conn);
$school_id = $_SESSION['user']['school_id'] ?? null;

// Fetch separated user groups
$admins = $userModel->getUsersByRoleId($school_id, 2);   // schooladmin
$teachers = $userModel->getUsersByRoleId($school_id, 3); // teacher
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="schooladmin.php?page=dashboard" class="btn btn-outline-secondary me-3">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <a href="schooladmin.php?page=add-user-form" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add User
        </a>
    </div>

    <ul class="nav nav-tabs" id="userTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">Admins</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teachers" type="button" role="tab">Teachers</button>
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
// Inline reusable table rendering function
function renderUserTable($users) {
    if ($users && $users->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Username</th>
                        <th>Email</th>
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
                            <td>
                                <button class="btn btn-sm btn-primary"onclick="location.href='schooladmin.php?page=add-user-form&user_id=<?= $user['user_id'] ?>'">Edit</button>
                                <button class="btn btn-sm btn-danger deleteUserBtn" data-id="<?= $user['user_id'] ?>">Delete</button>
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


