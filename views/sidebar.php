<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings(); // single-tenant

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath   = $settings['logo_path'] ?? null;
$logoFullPath = $logoPath ? __DIR__ . '/../' . $logoPath : null;
$logoUrl = $logoPath ? BASE_URL . '/' . ltrim($logoPath, '/') : null;

function navItem($page, $label, $icon, $currentPage) {
    $active = $currentPage === $page ? 'lg-nav-active' : 'lg-nav-link';
    echo "
        <li>
            <a href=\"admin.php?page=$page\" class=\"d-flex align-items-center px-3 py-2 text-decoration-none $active\">
                <i class=\"$icon me-2\"></i>
                <span>$label</span>
            </a>
        </li>
    ";
}
?>


<aside id="sidebar" class="js-sidebar">
  <div class="d-flex flex-column h-100">

    <!-- School Header -->
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
      <a href="admin.php?page=dashboard" class="text-decoration-none fs-5 fw-semibold d-flex align-items-center">
        <?php if ($logoPath && file_exists($logoFullPath)): ?>
          <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo">
        <?php else: ?>
          <i class="fa-solid fa-school me-2"></i>
        <?php endif; ?>
        <span><?= htmlspecialchars($schoolName) ?></span>
      </a>
      
       <button id="sidebar-close" class="btn btn-sm btn-outline-secondary d-md-none">
        <i class="fa fa-times"></i>
    </button>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav list-unstyled flex-grow-1 mt-3 mb-0 px-2" id="sidebarNav">

      <!-- Dashboard -->
      <?php navItem('dashboard', 'Dashboard', 'fa-solid fa-house', $currentPage ?? ''); ?>

      <!-- Users & Roles -->
      <?php navItem('user', 'Users & Roles', 'fa-solid fa-users-gear', $currentPage ?? ''); ?>

      <!-- Academic Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none <?= in_array($currentPage ?? '', ['class', 'subject', 'exam']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#academicMenu" aria-expanded="<?= in_array($currentPage ?? '', ['class', 'subject', 'exam']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-school me-2"></i>
          <span>Academic</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage ?? '', ['academic_years', 'class', 'subject', 'exam']) ? 'show' : '' ?>" id="academicMenu" data-bs-parent="#sidebarNav">
          <a href="admin.php?page=academic_years" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'academic_years' ? 'fw-semibold' : ''; ?>">📚 Academic Year</a>  
          <a href="admin.php?page=subject" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'subject' ? 'fw-semibold' : ''; ?>">📚 Subjects</a>  
          <a href="admin.php?page=class" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'class' ? 'fw-semibold' : ''; ?>">🏫 Classes</a>
          <a href="admin.php?page=exam" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'exam' ? 'fw-semibold' : ''; ?>">📝 Exams</a>
        </div>
      </li>

      <!-- Grading Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none <?= in_array($currentPage ?? '', ['report', 'grading']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#gradingMenu" aria-expanded="<?= in_array($currentPage ?? '', ['report', 'grading']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-chart-bar me-2"></i>
          <span>Grading</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage ?? '', ['grading', 'summary', 'report']) ? 'show' : '' ?>" id="gradingMenu" data-bs-parent="#sidebarNav">

    <a href="admin.php?page=grading"
       class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'grading' ? 'fw-semibold' : ''; ?>">
        🏷️ Grading Criteria
    </a>

    <a href="#"
       class="d-block py-1 px-3 text-decoration-none"
       data-bs-toggle="modal"
       data-bs-target="#generateReportModal">
        📊 Class Reports
    </a>

    <a href="admin.php?page=summary"
       class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'summary' ? 'fw-semibold' : ''; ?>">
        📈 Results Summary
    </a>

    <a href="#" class="d-block py-1 px-3 text-decoration-none"data-bs-toggle="modal" data-bs-target="#studentReportModal">
    👤 Student Reports
</a>

</div>
      </li>

      <!-- Settings -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none <?= in_array($currentPage ?? '', ['settings-general', 'settings-report', 'settings-communication']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#settingsMenu" aria-expanded="<?= in_array($currentPage ?? '', ['settings-general', 'settings-report', 'settings-communication']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-gear me-2"></i>
          <span>Settings</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage ?? '', ['settings-general', 'settings-report', 'settings-communication']) ? 'show' : '' ?>" id="settingsMenu" data-bs-parent="#sidebarNav">
          <a href="admin.php?page=settings-general" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'settings-general' ? 'fw-semibold' : ''; ?>">⚙️ General Settings</a>
          <a href="admin.php?page=settings-report" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'settings-report' ? 'fw-semibold' : ''; ?>">📄 Report Settings</a>
        </div>
      </li>

    </ul>
  </div>
</aside>
<!-- Overlay for mobile — must be a SIBLING of #sidebar, not nested inside it,
     for the CSS rule `#sidebar.show + #sidebar-overlay` to match. -->
<div id="sidebar-overlay"></div>