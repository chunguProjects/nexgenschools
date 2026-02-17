<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: ../views/login.php");
    exit;
}

include('../config/db.php');
require_once('../model/GeneralSettingsModel.php');

$model = new GeneralSettingsModel($conn);
$settings = $model->getSettings(); 
$schoolName = $settings['school_name'] ?? 'NextGen Schools';

$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($schoolName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" /> 
    <link rel="stylesheet" href="../css/style.css"> 
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="wrapper">

    <?php include('../views/sidebar.php'); ?>

    <div class="main">

        <?php include('../views/header.php'); ?>

        <main class="content p-3">

            <!-- ✅ FLASH MESSAGES HERE -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php
            $file = "../views/" . $currentPage . ".php";
            if (file_exists($file)) {
                include($file);
            } else {
                echo "<div class='alert alert-danger'>Page not found</div>";
            }
            ?>

        </main>

        <?php include('../views/footer.php'); ?>

    </div>
</div>

<script src="../js/script.js"></script> 
<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<?php include('../views/modal-report.php'); ?>

</body>
</html>
