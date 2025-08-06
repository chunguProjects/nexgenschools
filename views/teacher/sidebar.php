<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

$schoolId = $_SESSION['user']['school_id'] ?? null;

if ($schoolId) {
    $stmt = $conn->prepare("SELECT name FROM schools WHERE school_id = ?");
    $stmt->bind_param("i", $schoolId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $schoolName = $row['name'];
    }
}

$currentPage = $_GET['page'] ?? 'dashboard';

require_once('../model/GeneralSettingsModel.php');
$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings($schoolId);

$logoPath = $settings['logo_path'] ?? null;
$logoUrl = '/nexgenschools/' . ltrim($logoPath, '/');
$logoFullPath = $logoPath ? __DIR__ . '/../' . $logoPath : null;
?>

<aside id="sidebar" class="js-sidebar bg-white border-end shadow-sm">
  <div class="d-flex flex-column h-100">

    <!-- School Header -->
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
      <a href="teacher.php?page=dashboard" class="text-decoration-none text-primary fs-5 fw-semibold d-flex align-items-center">
        <?php if ($logoPath && file_exists($logoFullPath)): ?>
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo" style="max-height: 40px; max-width: 40px; object-fit: contain; margin-right: 8px;">
        <?php else: ?>
        <i class="fa-solid fa-school me-2"></i>
        <?php endif; ?>
        <span><?= htmlspecialchars($schoolName) ?></span>
      </a>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav list-unstyled flex-grow-1 mt-3 mb-0 px-2">

      <!-- Dashboard -->
      <li>
        <a href="teacher.php?page=dashboard" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'dashboard') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-house me-2"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <!-- My Classes -->
      <li>
        <a href="teacher.php?page=classes" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'classes') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-users me-2"></i>
          <span>My Classes</span>
        </a>
      </li>

      <!-- Enter Grades -->
      <li>
        <a href="teacher.php?page=enter-grades" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'enter-grades') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-pen-nib me-2"></i>
          <span>Enter Grades</span>
        </a>
      </li>

      <!-- Settings -->
      <li>
        <a href="teacher.php?page=settings" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'settings') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-gear me-2"></i>
          <span>Settings</span>
        </a>
      </li>

      <!-- Logout -->
      <li>
        <a href="../auth/logout.php" class="d-flex align-items-center px-3 py-2 text-decoration-none text-danger">
          <i class="fa-solid fa-sign-out-alt me-2"></i>
          <span>Logout</span>
        </a>
      </li>

    </ul>
  </div>
  <div id="sidebar-overlay"></div>
</aside>
