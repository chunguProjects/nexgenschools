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

<?php
// Flash messages
if (isset($_SESSION['flash_message'])) {
    $msg = $_SESSION['flash_message'];
    $alertType = $msg['type'] === 'error' ? 'danger' : $msg['type'];
    echo "<div class='alert alert-{$alertType}'>{$msg['text']}</div>";
    unset($_SESSION['flash_message']);
}
?>

<!-- Header + Add -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="admin.php?page=dashboard" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>
    <a href="admin.php?page=add-exam-form" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Exam
    </a>
</div>

<?php if (!empty($examsByYear)): ?>
    <?php foreach ($examsByYear as $yearId => $yearExams): 
        $isActiveYear = ($yearId === $currentYearId);
        $yearName = htmlspecialchars($yearExams[0]['academic_year_name'] ?? 'N/A');
    ?>
        <div class="mb-4">
            <h5 class="fw-bold mb-2">
                Academic Year: <?= $yearName ?>
                <?php if ($isActiveYear): ?>
                    <span class="badge bg-success ms-2">Active</span>
                <?php endif; ?>
            </h5>

            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Exam Name</th>
                                <th>Term</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yearExams as $exam): ?>
                                <tr>
                                    <td><?= (int)$exam['id'] ?></td>
                                    <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                                    <td><?= htmlspecialchars($exam['term']) ?></td>
                                    <td>
                                        <a href="admin.php?page=add-exam-form&exam_id=<?= (int)$exam['id'] ?>" 
                                           class="btn btn-sm btn-primary me-1">Edit</a>

                                        <button class="btn btn-sm btn-danger deleteExamBtn me-1" 
                                                data-id="<?= (int)$exam['id'] ?>">Delete</button>

                                        <a href="admin.php?page=add-grades-form&exam_id=<?= (int)$exam['id'] ?>" 
                                           class="btn btn-sm btn-success <?= $isActiveYear ? '' : 'disabled' ?>" 
                                           <?= $isActiveYear ? '' : 'title="Cannot enter grades for non-active academic year"' ?>>
                                           Add Grades
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-center text-muted">No exams found.</p>
<?php endif; ?>

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
                } else {
                    return response.text();
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