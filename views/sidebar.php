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
$logoUrl = $logoPath ? '/nexgenschools/' . ltrim($logoPath, '/') : null;

function navItem($page, $label, $icon, $currentPage) {
    $active = $currentPage === $page ? 'bg-primary text-white fw-semibold' : 'text-dark';
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
<aside id="sidebar" class="js-sidebar bg-white border-end shadow-sm">
  <div class="d-flex flex-column h-100">

    <!-- School Header -->
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
      <a href="admin.php?page=dashboard" class="text-decoration-none text-primary fs-5 fw-semibold d-flex align-items-center">
        <?php if ($logoPath && file_exists($logoFullPath)): ?>
          <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo" style="max-height: 40px; max-width: 40px; object-fit: contain; margin-right: 8px;">
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
    <ul class="sidebar-nav list-unstyled flex-grow-1 mt-3 mb-0 px-2">

      <!-- Dashboard -->
      <?php navItem('dashboard', 'Dashboard', 'fa-solid fa-house', $currentPage ?? ''); ?>

      <!-- Users & Roles -->
      <?php navItem('user', 'Users & Roles', 'fa-solid fa-users-gear', $currentPage ?? ''); ?>

      <!-- Academic Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark <?= in_array($currentPage ?? '', ['class', 'subject', 'exam']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#academicMenu" aria-expanded="<?= in_array($currentPage ?? '', ['class', 'subject', 'exam']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-school me-2"></i>
          <span>Academic</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage ?? '', ['academic_years', 'class', 'subject', 'exam']) ? 'show' : '' ?>" id="academicMenu">
          <a href="admin.php?page=academic_years" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'academic_years' ? 'text-primary fw-semibold' : 'text-dark'; ?>">📚 Academic Year</a>  
          <a href="admin.php?page=subject" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'subject' ? 'text-primary fw-semibold' : 'text-dark'; ?>">📚 Subjects</a>  
          <a href="admin.php?page=class" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'class' ? 'text-primary fw-semibold' : 'text-dark'; ?>">🏫 Classes</a>
          <a href="admin.php?page=exam" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'exam' ? 'text-primary fw-semibold' : 'text-dark'; ?>">📝 Exams</a>
          <a href="admin.php?page=promotion" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'promotion' ? 'text-primary fw-semibold' : 'text-dark'; ?>">📝 promote studrnts</a>

        </div>
      </li>

      <!-- Grading Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark <?= in_array($currentPage ?? '', ['report', 'grading']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#gradingMenu" aria-expanded="<?= in_array($currentPage ?? '', ['report', 'grading']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-chart-bar me-2"></i>
          <span>Grading</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage ?? '', ['report', 'grading']) ? 'show' : '' ?>" id="gradingMenu">
          <a href="admin.php?page=grading" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'grading' ? 'text-primary fw-semibold' : 'text-dark'; ?>">🏷️ Grading Criteria</a>
          <a href="#" class="d-block py-1 px-3 text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#generateReportModal">📊 Class Reports</a>
          <a href="" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'report' ? 'text-primary fw-semibold' : 'text-dark'; ?>">👥 Student Reports</a>
        </div>
      </li>

      <!-- Communication Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark collapsed"
           data-bs-toggle="collapse" href="#communicationMenu" aria-expanded="false">
          <i class="fa-solid fa-bullhorn me-2"></i>
          <span>Communication</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3" id="communicationMenu">
          <a href="admin.php?page=announcements" class="d-block py-1 px-3 text-decoration-none text-dark">📢 Announcements</a>
          <a href="admin.php?page=messages" class="d-block py-1 px-3 text-decoration-none text-dark">💬 Messaging</a>
        </div>
      </li>

      <!-- Finance Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark collapsed"
           data-bs-toggle="collapse" href="#financeMenu" aria-expanded="false">
          <i class="fa-solid fa-sack-dollar me-2"></i>
          <span>Finance</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3" id="financeMenu">
          <a href="admin.php?page=fees" class="d-block py-1 px-3 text-decoration-none text-dark">💰 Fee Setup</a>
          <a href="admin.php?page=invoices" class="d-block py-1 px-3 text-decoration-none text-dark">📄 Invoices</a>
          <a href="admin.php?page=payments" class="d-block py-1 px-3 text-decoration-none text-dark">💳 Payments</a>
        </div>
      </li>

      <!-- Settings -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark <?= in_array($currentPage ?? '', ['settings-general', 'settings-report', 'settings-communication']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#settingsMenu" aria-expanded="<?= in_array($currentPage ?? '', ['settings-general', 'settings-report', 'settings-communication']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-gear me-2"></i>
          <span>Settings</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage ?? '', ['settings-general', 'settings-report', 'settings-communication']) ? 'show' : '' ?>" id="settingsMenu">
          <a href="admin.php?page=settings-general" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'settings-general' ? 'text-primary fw-semibold' : 'text-dark'; ?>">⚙️ General Settings</a>
          <a href="admin.php?page=settings-report" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'settings-report' ? 'text-primary fw-semibold' : 'text-dark'; ?>">📄 Report Settings</a>
          <a href="admin.php?page=settings-communication" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage ?? '') === 'settings-communication' ? 'text-primary fw-semibold' : 'text-dark'; ?>">📢 Communication Settings</a>
        </div>
      </li>

    </ul>
  </div>
  <!-- Overlay for mobile -->
  <div id="sidebar-overlay"></div>
</aside>
