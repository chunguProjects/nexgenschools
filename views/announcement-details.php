<?php

include('../config/db.php');
require_once('../model/AnnouncementModel.php');

require_once('../model/GeneralSettingsModel.php');

$settingsModel = new GeneralSettingsModel($conn);
$settings = $settingsModel->getSettings();

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath   = $settings['logo_path'] ?? null;
$logoUrl = ($logoPath && file_exists(__DIR__ . '/../' . $logoPath))
    ? '../'.$logoPath
    : null;

$model = new AnnouncementModel($conn);

$id = $_GET['id'] ?? 0;

$announcement = $model->getById((int)$id);

if(!$announcement){
    echo "Announcement not found";
    exit;

}

?>


<!DOCTYPE html>

<html>

<head>

<title>
<?=htmlspecialchars($announcement['title'])?>
</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">

</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark lp-navbar fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php">
            <?php 
            $logoUrl = ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)) 
                       ? '../' . $logoPath 
                       : null;?>
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" 
                     alt="<?= htmlspecialchars($schoolName) ?> Logo"
                     class="me-2 rounded-circle"
                     style="max-height:34px; max-width:34px; object-fit:contain;">
            <?php else: ?>
                <i class="fa-solid fa-school me-2"></i>
            <?php endif; ?>
            <?= htmlspecialchars($schoolName) ?> </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span> </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="about.php">About</a> </li>
                <li class="nav-item"><a class="nav-link" href="../index.php#why-us">Why Choose Us</a></li>
                <li class="nav-item"><a class="nav-link" href="admissions.php">Admissions</a></li>
                <li class="nav-item"><a class="nav-link active" href="view-announcements.php">
                <i class="fa-solid fa-bullhorn me-1"></i>Announcements</a></li>
            </ul>
            <a class="lp-btn-ghost ms-lg-3 mt-2 mt-lg-0 d-inline-block text-center"href="student-portal.php">
                <i class="fa-solid fa-file-lines me-1"></i> View Results</a>
            <a class="lp-btn-solid ms-lg-2 mt-2 mt-lg-0 d-inline-block text-center"
               href="login.php"><i class="fa-solid fa-user-lock me-1"></i>
                Staff Login
            </a>
        </div>

    </div>
</nav>

<div class="container" style="margin-top:90px;">


<a href="view-announcements.php"
class="btn btn-outline-secondary mb-3">

← Back

</a>


<div class="card shadow-sm">

<div class="card-body">


<h2>
<?=htmlspecialchars($announcement['title'])?>
</h2>


<p class="text-muted">

Posted:
<?=date('d M Y',strtotime($announcement['created_at']))?>

</p>


<hr>


<p style="white-space:pre-line">

<?=htmlspecialchars($announcement['body'])?>

</p>


</div>

</div>


</div>


</body>

</html>