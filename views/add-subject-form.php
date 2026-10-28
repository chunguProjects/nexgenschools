<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
include('../model/subject.php');

if (!isset($_SESSION['user'])) {
    die("Unauthorized access.");
}

$subjectId = $_GET['subject_id'] ?? null;

$subjectData = [
    'subject_name' => '',
    'subject_code' => '',
];

$isEdit = false;

if ($subjectId) {

    $isEdit = true;

    $subjectModel = new Subjects($conn);
    $subject = $subjectModel->getSubjectById((int)$subjectId);

    if (!$subject) {
        die("Subject not found.");
    }

    $subjectData = [
        'subject_name' => $subject['subject_name'],
        'subject_code' => $subject['subject_code'],
    ];
}


$message = $_GET['message'] ?? null;
$error   = $_GET['error'] ?? null;

?>


<div class="container-fluid py-3">


    <!-- PAGE HEADER -->
    <div class="lg-page-header">

        <a href="admin.php?page=subject" 
           class="lg-btn-back text-decoration-none">

            <i class="fa fa-arrow-left"></i>
            Back

        </a>


        <h4 class="lg-page-title mb-0">

            <?= $isEdit ? "Edit Subject" : "Add New Subject" ?>

        </h4>


        <div></div>

    </div>



    <!-- ALERTS -->
    <?php if ($message): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php elseif ($error): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>




    <!-- FORM CARD -->
    <div class="lg-form-card">


        <h4>
            <?= $isEdit ? "Update Subject Details" : "Subject Information" ?>
        </h4>


        <div class="lg-form-divider"></div>



        <form action="../controllers/subject-controller.php" method="POST">


            <?php if ($isEdit): ?>

                <input type="hidden" name="update_subject" value="1">

                <input type="hidden" 
                       name="subject_id" 
                       value="<?= htmlspecialchars($subjectId) ?>">



            <?php else: ?>

                <input type="hidden" name="add_subject" value="1">

            <?php endif; ?>





            <div class="row">


                <div class="col-md-6 mb-3">


                    <label for="subject_name">
                        Subject Name
                    </label>


                    <input type="text"
                           class="form-control"
                           id="subject_name"
                           name="subject_name"
                           value="<?= htmlspecialchars($subjectData['subject_name']) ?>"
                           required>


                </div>



                <div class="col-md-6 mb-3">


                    <label for="subject_code">
                        Subject Code
                    </label>


                    <input type="text"
                           class="form-control"
                           id="subject_code"
                           name="subject_code"
                           value="<?= htmlspecialchars($subjectData['subject_code']) ?>">


                </div>


            </div>




            <div class="d-flex gap-2 mt-3">


                <button type="submit" 
                        class="lg-btn-add">

                    <i class="fa fa-save"></i>

                    <?= $isEdit ? "Update Subject" : "Save Subject" ?>

                </button>



                <a href="admin.php?page=subject"
                   class="lg-btn-back text-decoration-none">

                    Cancel

                </a>


            </div>



        </form>


    </div>


</div>