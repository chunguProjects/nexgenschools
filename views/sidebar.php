<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

$schoolName = "NextGen Schools";
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

function navItem($page, $label, $icon, $currentPage) {
    $active = $currentPage === $page ? 'bg-primary text-white fw-semibold' : 'text-dark';
    echo "
        <li>
            <a href=\"schooladmin.php?page=$page\" class=\"d-flex align-items-center px-3 py-2 text-decoration-none $active\">
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
      <a href="schooladmin.php?page=dashboard" class="text-decoration-none text-primary fs-5 fw-semibold d-flex align-items-center">
        <i class="fa-solid fa-graduation-cap me-2"></i>
        <span><?= htmlspecialchars($schoolName) ?></span>
      </a>
      <button id="sidebar-close" class="btn btn-sm btn-outline-secondary d-md-none" aria-label="Close sidebar">
        <i class="fa fa-times"></i>
      </button>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav list-unstyled flex-grow-1 mt-3 mb-0 px-2">

      <!-- Dashboard -->
      <li>
        <a href="schooladmin.php?page=dashboard" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'dashboard') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-house me-2"></i>
          <span>Dashboard</span>
        </a>
      </li>

         <!-- Users -->
      <li>
        <a href="schooladmin.php?page=user" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'user') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-users-gear me-2"></i>
          <span>Users & Roles</span>
        </a>
      </li>

      <!-- Academic Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark <?= in_array($currentPage, ['class', 'subject', 'exam']) ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#academicMenu" aria-expanded="<?= in_array($currentPage, ['class', 'subject', 'exam']) ? 'true' : 'false' ?>">
          <i class="fa-solid fa-school me-2"></i>
          <span>Academic</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= in_array($currentPage, ['class', 'subject', 'exam']) ? 'show' : '' ?>" id="academicMenu">
          <a href="schooladmin.php?page=class" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage === 'class') ? 'text-primary fw-semibold' : 'text-dark'; ?>">🏫 Classes</a>
          <a href="schooladmin.php?page=subject" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage === 'subject') ? 'text-primary fw-semibold' : 'text-dark'; ?>">📚 Subjects</a>
          <a href="schooladmin.php?page=exam" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage === 'exam') ? 'text-primary fw-semibold' : 'text-dark'; ?>">📝 Exams</a>
        </div>
      </li>

      <!-- Grading Dropdown -->
      <li>
        <a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark <?= ($currentPage === 'report') ? '' : 'collapsed' ?>"
           data-bs-toggle="collapse" href="#gradingMenu" aria-expanded="<?= ($currentPage === 'report') ? 'true' : 'false' ?>">
          <i class="fa-solid fa-chart-bar me-2"></i>
          <span>Grading</span>
          <i class="fa-solid fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse ps-3 <?= ($currentPage === 'report') ? 'show' : '' ?>" id="gradingMenu">
          <a href="#" class="d-block py-1 px-3 text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#generateReportModal">
            📊 Class Reports
          </a>
          <a href="schooladmin.php?page=report" class="d-block py-1 px-3 text-decoration-none <?= ($currentPage === 'report') ? 'text-primary fw-semibold' : 'text-dark'; ?>">
            👥 Student Reports
          </a>
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
          <a href="schooladmin.php?page=announcements" class="d-block py-1 px-3 text-decoration-none text-dark">📢 Announcements</a>
          <a href="schooladmin.php?page=messages" class="d-block py-1 px-3 text-decoration-none text-dark">💬 Messaging</a>
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
          <a href="schooladmin.php?page=fees" class="d-block py-1 px-3 text-decoration-none text-dark">💰 Fee Setup</a>
          <a href="schooladmin.php?page=invoices" class="d-block py-1 px-3 text-decoration-none text-dark">📄 Invoices</a>
          <a href="schooladmin.php?page=payments" class="d-block py-1 px-3 text-decoration-none text-dark">💳 Payments</a>
        </div>
      </li>

      <!-- Settings -->
      <li>
        <a href="schooladmin.php?page=settings" class="d-flex align-items-center px-3 py-2 text-decoration-none <?= ($currentPage === 'settings') ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
          <i class="fa-solid fa-gear me-2"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>
  </div>
  <!-- Overlay for mobile -->
  <div id="sidebar-overlay"></div>
</aside>