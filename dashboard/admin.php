<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: ../views/login.php");
    exit;
}

include(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../model/GeneralSettingsModel.php');

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" /> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
<div class="wrapper">

    <?php include(__DIR__ . '/../views/sidebar.php'); ?>

    <div class="main">

        <?php include(__DIR__ . '/../views/header.php'); ?>

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

        <?php include(__DIR__ . '/../views/footer.php'); ?>

    </div>
</div>

<script src="../js/script.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<?php include(__DIR__ . '/../views/modal-report.php'); ?>

</body>
</html>
