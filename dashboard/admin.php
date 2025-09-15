<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: ../views/login.php");
    exit;
    
}
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NextGen Schools</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include('../views/sidebar.php'); ?>

        <div class="main">
            <!-- Header -->
            <?php include('../views/header.php'); ?>

            <!-- Main Content -->
            <main class="content">
                <?php
                $file = "../views/" . $currentPage . ".php";
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
    <script src="../js/script.js"></script>
    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <?php include('../views/modal-report.php'); ?>
</body>
</html>
