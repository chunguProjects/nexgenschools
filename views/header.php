<nav class="navbar navbar-expand shadow-sm px-3 py-2 bg-white border-bottom" style="height: 60px;">
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-sm d-flex align-items-center justify-content-center me-3" id="sidebar-toggle" type="button" style="width: 40px; height: 40px; background-color: #0d6efd; border: none; border-radius: 50%;">
        <i class="fa fa-bars text-white"></i>
    </button>

    <!-- Page Title (Optional) -->
    <h5 class="mb-0 fw-semibold d-none d-md-inline">Admin Dashboard</h5>

    <!-- Spacer -->
    <div class="flex-grow-1"></div>

    <!-- User Dropdown -->
    <ul class="navbar-nav">
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-user-circle me-2 text-primary fs-5"></i>
                <span class="d-none d-md-inline text-dark fw-semibold">
                    <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'User') ?>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm">
                <li><a class="dropdown-item" href="#">👤 Profile</a></li>
                <li><a class="dropdown-item" href="#">⚙️ Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../controllers/logout.php">🚪 Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>
