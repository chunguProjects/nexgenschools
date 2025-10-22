<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

// Load school settings
$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings(); 

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath   = $settings['logo_path'] ?? null;

// Resolve absolute filesystem path for file_exists()
$logoFullPath = $logoPath ? $_SERVER['DOCUMENT_ROOT'] . '/nexgenschools/' . ltrim($logoPath, '/') : null;
// Resolve URL for the browser
$logoUrl = $logoPath ? '/nexgenschools/' . ltrim($logoPath, '/') : null;

$currentPage = $_GET['page'] ?? 'dashboard';

// Helper to render a nav item
function navItem($page, $label, $icon, $currentPage) {
    $active = $currentPage === $page ? 'bg-primary text-white fw-semibold' : 'text-dark';
    echo "
        <li>
            <a href=\"student.php?page=$page\" class=\"d-flex align-items-center px-3 py-2 text-decoration-none $active\">
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
      <a href="student.php?page=dashboard" class="text-decoration-none text-primary fs-5 fw-semibold d-flex align-items-center">
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

      <?php navItem('dashboard', 'Dashboard', 'fa-solid fa-house', $currentPage); ?>
      <?php navItem('profile', 'Profile', 'fa-solid fa-user', $currentPage); ?>
      <?php navItem('settings', 'Settings', 'fa-solid fa-gear', $currentPage); ?>
      <?php navItem('logout', 'Logout', 'fa-solid fa-right-from-bracket', $currentPage); ?>

    </ul>
  </div>
  <div id="sidebar-overlay" class="sidebar-overlay"></div>
</aside>
