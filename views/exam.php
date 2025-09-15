<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/exam.php');

$examModel = new Exams($conn);

// Get all exams
$exams = $examModel->getAllExams();
?>

<div class="container-fluid py-3">

<?php
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

<!-- Exams Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="card-title mb-0">Exam List</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Term</th>
                    <th>Academic Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($exams)) : ?>
                    <?php foreach ($exams as $exam) : ?>
                        <tr>
                            <td><?= (int)$exam['id'] ?></td>
                            <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                            <td><?= htmlspecialchars($exam['term']) ?></td>
                            <td><?= htmlspecialchars($exam['academic_year_name'] ?? 'N/A') ?></td>
                            <td>
                                <a href="admin.php?page=add-exam-form&exam_id=<?= (int)$exam['id'] ?>" class="btn btn-sm btn-primary me-1">Edit</a>
                                <button class="btn btn-sm btn-danger deleteExamBtn me-1" data-id="<?= (int)$exam['id'] ?>">Delete</button>
                                <a href="admin.php?page=add-grades-form&exam_id=<?= (int)$exam['id'] ?>" class="btn btn-sm btn-success">Add Grades</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center">No Exams Found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".deleteExamBtn");

    deleteButtons.forEach(button => {
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
