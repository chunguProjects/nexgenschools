<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'Student') {
    header("Location: ../views/student-portal.php");
    exit;
}
require_once __DIR__ . '/../includes/session-check.php';


$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($_SESSION['user']['full_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Styles -->
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <?php include('../views/student/sidebar.php'); ?>

        <div class="main flex-grow-1 d-flex flex-column min-vh-100">
            <!-- Header -->
            <?php include('../views/student/header.php'); ?>

            <!-- Main Content -->
            <main class="content flex-grow-1 px-3 py-4">
                <?php
                $file = "../views/student/" . $currentPage . ".php";
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
    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
