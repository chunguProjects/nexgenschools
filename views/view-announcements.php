<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/db.php');
require_once('../model/AnnouncementModel.php');
require_once('../model/GeneralSettingsModel.php');
/*
|--------------------------------------------------------------------------
| School Settings
|--------------------------------------------------------------------------
*/
$settingsModel = new GeneralSettingsModel($conn);
$settings = $settingsModel->getSettings();

$schoolName = $settings['school_name'] ?? 'NextGen Schools';
$logoPath     = $settings['logo_path'] ?? null;
$logoUrl      = ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)) ? '../' . $logoPath : null;

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$limit = 5;

$page = isset($_GET['page']) 
        ? max(1,(int)$_GET['page']) 
        : 1;
$offset = ($page - 1) * $limit;
/*
|--------------------------------------------------------------------------
| Announcements
|--------------------------------------------------------------------------
*/

$model = new AnnouncementModel($conn);
$announcements = $model->getPaginatedPublished($limit,$offset);
$total = $model->countPublished();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?=htmlspecialchars($schoolName)?></title>
<?php if (!empty($logoUrl)): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl) ?>">
    <?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="announcements-page">
<!-- NAVBAR -->
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



<!-- HEADER -->

<section class="pt-5 mt-5 pb-5 text-center">
<div class="container">

<h1 class="lp-section-title">

<i class="fa-solid fa-bullhorn me-2"></i>

Announcements

</h1>


<div class="lp-divider"></div>


<p class="text-muted">

Stay updated with the latest news, events and notices from our school.

</p>


</div>

</section>





<!-- ANNOUNCEMENTS -->

<section class="pb-5">

<div class="container">


<div class="row g-4">


<?php if(empty($announcements)): ?>


<div class="alert alert-info">

No announcements available.

</div>


<?php else: ?>


<?php foreach($announcements as $a): ?>


<div class="col-md-6">


<div class="card shadow-sm h-100 border-0">


<div class="card-body">


<p class="small text-muted mb-2">

<i class="fa fa-calendar me-1"></i>

<?=date(
'd M Y',
strtotime($a['created_at'])
)?>

</p>


<h4 class="fw-bold">

<?=htmlspecialchars($a['title'])?>

</h4>


<p class="text-muted">

<?=htmlspecialchars(
mb_strimwidth(
$a['body'],
0,
180,
'...'
)
)?>

</p>


<a href="announcement-details.php?id=<?=$a['announcement_id']?>"
class="btn btn-outline-success btn-sm">

Read More

</a>


</div>

</div>


</div>


<?php endforeach; ?>


<?php endif; ?>


</div>



<!-- PAGINATION -->

<?php if($totalPages > 1): ?>


<nav class="mt-5">

<ul class="pagination justify-content-center">


<?php for($i=1;$i<=$totalPages;$i++): ?>


<li class="page-item <?=($i==$page)?'active':''?>">


<a class="page-link"
href="?page=<?=$i?>">

<?=$i?>

</a>


</li>


<?php endfor; ?>


</ul>

</nav>


<?php endif; ?>


</div>

</section>




<?php include('general-footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>