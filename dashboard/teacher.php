<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'teacher') {
    header("Location: ../views/login.php");
    exit;
}

$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Panel - NextGen Schools</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Styles -->
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
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
            <main class="content flex-grow-1 px-3 py-4">
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
            <?php include('../views/teacher/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/script.js"></script>
    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
