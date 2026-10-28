<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/exam.php');
include('../model/academic_year.php');

$examModel = new Exams($conn);
$ayModel   = new AcademicYear($conn);

// Get all exams
$exams = $examModel->getAllExams();

// Get active academic year
$currentYear = $ayModel->getCurrentYear();
$currentYearId = intval($currentYear['academic_year_id'] ?? 0);

// Group exams by academic year
$examsByYear = [];
foreach ($exams as $exam) {
    $yearId = intval($exam['academic_year_id'] ?? 0);
    if (!isset($examsByYear[$yearId])) {
        $examsByYear[$yearId] = [];
    }
    $examsByYear[$yearId][] = $exam;
}
?>

<div class="container-fluid py-3">

    <!-- Page Header -->
    <div class="lg-page-header">

        <a href="admin.php?page=dashboard" class="lg-btn-back">
            <i class="fa fa-arrow-left"></i>
            Back
        </a>

        <a href="admin.php?page=add-exam-form" class="lg-btn-add">
            <i class="fa-solid fa-plus"></i>
            Add Exam
        </a>

    </div>

    <!-- Feedback Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Exam Lists, grouped by academic year -->
    <?php if (!empty($examsByYear)): ?>
        <?php foreach ($examsByYear as $yearId => $yearExams):
            $isActiveYear = ($yearId === $currentYearId);
            $yearName = htmlspecialchars($yearExams[0]['academic_year_name'] ?? 'N/A');
        ?>
            <div class="card mb-4">

                <div class="lg-card-header d-flex align-items-center">
                    <h5 class="card-title mb-0">
                        Academic Year: <?= $yearName ?>
                    </h5>
                    <?php if ($isActiveYear): ?>
                        <span class="badge ms-2" style="background:var(--gold); color:var(--ink);">Active</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <table class="table table-hover lg-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Exam Name</th>
                                <th>Term</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!empty($yearExams)): ?>
                            <?php foreach ($yearExams as $exam): ?>
                                <tr>
                                    <td><?= (int)$exam['id'] ?></td>
                                    <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                                    <td><?= htmlspecialchars($exam['term']) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="admin.php?page=add-exam-form&exam_id=<?= (int)$exam['id'] ?>"
                                               class="btn btn-sm lg-btn-edit">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            <button class="btn btn-sm lg-btn-delete deleteExamBtn"
                                                    data-id="<?= (int)$exam['id'] ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>

                                            <a href="admin.php?page=add-grades-form&exam_id=<?= (int)$exam['id'] ?>"
                                               class="btn btn-sm btn-ledger-solid <?= $isActiveYear ? '' : 'disabled' ?>"
                                               <?= $isActiveYear ? '' : 'title="Cannot enter grades for non-active academic year"' ?>>
                                               Add Grades
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No exams found for this academic year.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="card">
            <div class="lg-card-header">
                <h5 class="card-title mb-0">Exam List</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover lg-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Exam Name</th>
                            <th>Term</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No exams found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Delete Exam Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".deleteExamBtn").forEach(button => {
        button.addEventListener("click", function () {
            const examId = this.dataset.id;
            if (!confirm("Are you sure you want to delete this exam?")) return;

            fetch("../controllers/exam-controller.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `delete_exam=1&exam_id=${examId}`
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                }
            })
            .catch(error => {
                alert("Failed to delete exam. Please try again.");
                console.error("Error:", error);
            });
        });
    });
});
</script>