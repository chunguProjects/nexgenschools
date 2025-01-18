<aside id="sidebar" class="js-sidebar bg-dark text-light">
    <!-- Sidebar Container -->
    <div class="h-100">
        <!-- Sidebar Logo -->
        <div class="sidebar-logo py-3 px-3">
            <a href="index.php?page=dashboard" class="text-decoration-none text-light fs-5 fw-bold">
                <i class="fa-solid fa-graduation-cap me-2"></i> NextGen Schools
            </a>
        </div>

        <ul class="sidebar-nav list-unstyled mt-3">
            <!-- Dashboard -->
            <li class="sidebar-item">
                <a href="index.php?page=dashboard" class="sidebar-link px-3 py-2 d-block <?php echo ($currentPage == 'dashboard') ? 'active text-warning fw-bold' : 'text-light'; ?>">
                    <i class="fa-solid fa-house me-2"></i> Dashboard
                </a>
            </li>
            <hr class="my-1 text-primary mx-auto w-75">

            <!-- User Management -->
            <li class="sidebar-item">
                <a href="index.php?page=user" class="sidebar-link px-3 py-2 d-block <?php echo ($currentPage == 'user') ? 'active text-warning fw-bold' : 'text-light'; ?>">
                    <i class="fa-solid fa-user me-2"></i> User Management
                </a>
            </li>
            <hr class="my-1 text-primary mx-auto w-75">

            <!-- Class Management -->
            <li class="sidebar-item">
                <a href="index.php?page=class" class="sidebar-link px-3 py-2 d-block <?php echo ($currentPage == 'class') ? 'active text-warning fw-bold' : 'text-light'; ?>">
                    <i class="fa-solid fa-chalkboard me-2"></i> Class Management
                </a>
            </li>
            <hr class="my-1 text-primary mx-auto w-75">

            <!-- Subject Management -->
            <li class="sidebar-item">
                <a href="index.php?page=subject" class="sidebar-link px-3 py-2 d-block <?php echo ($currentPage == 'subject') ? 'active text-warning fw-bold' : 'text-light'; ?>">
                    <i class="fa-solid fa-book me-2"></i> Subject Management
                </a>
            </li>
            <hr class="my-1 text-primary mx-auto w-75">

            <!-- Exam Management -->
            <li class="sidebar-item">
                <a href="index.php?page=exam" class="sidebar-link px-3 py-2 d-block <?php echo ($currentPage == 'exam') ? 'active text-warning fw-bold' : 'text-light'; ?>">
                    <i class="fa-solid fa-file-alt me-2"></i> Exam Management
                </a>
            </li>
            <hr class="my-1 text-primary mx-auto w-75">

            <!-- Generate Reports (Dropdown) -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link px-3 py-2 d-block collapsed text-light" data-bs-toggle="collapse" data-bs-target="#reportsMenu" aria-expanded="false">
                    <i class="fa-solid fa-chart-bar me-2"></i> Generate Reports
                </a>
                <ul id="reportsMenu" class="sidebar-dropdown collapse list-unstyled ms-3">
                    <li class="sidebar-item">
                        <a href="index.php?page=report_classes" class="sidebar-link text-light <?php echo ($currentPage == 'report_classes') ? 'active text-warning fw-bold' : ''; ?>">
                            Classes
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="index.php?page=report_teachers" class="sidebar-link text-light <?php echo ($currentPage == 'report_teachers') ? 'active text-warning fw-bold' : ''; ?>">
                            Teachers
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>
