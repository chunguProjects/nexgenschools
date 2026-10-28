<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include_once('../config/db.php');
include_once('../model/user.php');

$teacherName = $_SESSION['user']['full_name'] ?? 'Teacher';
$navUserId = $_SESSION['user']['user_id'] ?? null;
$navPhotoUrl = null;

if ($navUserId) {
    $userModel = new User($conn);
    $navUser = $userModel->getUserById($navUserId);
    if (!empty($navUser['profile_photo'])) {
        $navPhotoUrl = '/nexgenschools/' . ltrim($navUser['profile_photo'], '/');
    }
}
?>

<nav class="ledger-header d-flex align-items-center px-3 py-2">
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-sm d-flex align-items-center justify-content-center me-3" id="sidebar-toggle" type="button">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Page Title -->
    <h5 class="mb-0 fw-semibold d-none d-md-inline page-title">Teacher Dashboard</h5>

    <!-- Spacer -->
    <div class="flex-grow-1"></div>

    <!-- User Dropdown -->
    <ul class="navbar-nav">
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if ($navPhotoUrl): ?>
                    <img src="<?= htmlspecialchars($navPhotoUrl) ?>" alt="Profile photo"
                         id="navProfilePhoto"
                         class="rounded-circle me-2 nav-profile-photo">
                <?php else: ?>
                    <i class="fa fa-user-circle me-2 fs-5 nav-profile-icon" id="navProfileIcon"></i>
                <?php endif; ?>
                <span class="d-none d-md-inline fw-semibold nav-user-name">
                    <?= htmlspecialchars($teacherName) ?>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm">
                <li><a class="dropdown-item" href="teacher.php?page=profile">👤 Profile &amp; Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../controllers/logout.php">🚪 Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>