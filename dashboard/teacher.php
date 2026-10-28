<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'teacher') {
    header("Location: ../views/login.php");
    exit;
}
include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');
require_once __DIR__ . '/../includes/session-check.php';

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings();

$schoolName = $settings['school_name'] ?? 'School name';


$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$logoPath = $settings['logo_path'] ?? null;

$logoUrl = null;

if ($logoPath) {
    // physical file path
    $fullPath = dirname(__DIR__) . '/' . $logoPath;

    if (file_exists($fullPath)) {
        $logoUrl = '../' . $logoPath; // URL from admin folder to project root
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=htmlspecialchars($schoolName)?></title>
    <?php if (!empty($logoUrl)): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <?php include('../views/teacher/sidebar.php'); ?>

        <div class="main flex-grow-1 d-flex flex-column min-vh-100">
            <!-- Header -->
            <?php include('../views/teacher/header.php'); ?>

            <!-- Main Content -->
            <main class="content flex-grow-1">                
                <?php
                $file = "../views/teacher/" . $currentPage . ".php";
                if (file_exists($file)) {
                    include($file);
                } else {
                    echo "<div class='alert alert-danger mt-3'>Page not found</div>";
                }
                ?>
            </main>

            <!-- Footer -->
            <?php include('../views/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>
