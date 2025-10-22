<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
include('../model/class.php');
include('../model/exam.php');

$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    echo "<div class='alert alert-danger'>Missing exam context.</div>";
    exit;
}

// ✅ Get exam and its academic year
$examModel = new Exams($conn);
$exam = $examModel->getExamById($exam_id);

if (!$exam) {
    echo "<div class='alert alert-danger'>Exam not found.</div>";
    exit;
}

$academicYearId = $exam['academic_year_id'];

// ✅ Get classes only for this academic year
$classModel = new ClassGroup($conn);
$classes = $classModel->getClassesByYear($academicYearId);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="admin.php?page=exam" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h4 class="mb-0">Add Grades</h4>
        <div></div>
    </div>

    <div class="mb-3">
        <label for="class_id" class="form-label">Select Class</label>
        <select name="class_id" id="class_id" class="form-select" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['class_id'] ?>">
                    <?= htmlspecialchars($class['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <form id="gradesForm" action="../controllers/grades-controller.php" method="POST">
        <input type="hidden" name="exam_id" value="<?= htmlspecialchars($exam_id) ?>">
        <div id="grade-entry-grid" class="table-responsive"></div>
    </form>
</div>

<script>
    const classSelect = document.getElementById('class_id');
    const gridContainer = document.getElementById('grade-entry-grid');
    const gradesForm = document.getElementById('gradesForm');
    const examId = <?= json_encode($exam_id) ?>;

    classSelect.addEventListener('change', function () {
        const classId = this.value;

        gridContainer.innerHTML = !classId
            ? '<div class="alert alert-warning">Please select a class.</div>'
            : '<div class="text-muted">Loading grade grid...</div>';

        if (!classId) return;

        fetch(`../views/render-grade-grid.php?class_id=${classId}&exam_id=${examId}`)
            .then(res => res.text())
            .then(html => gridContainer.innerHTML = html)
            .catch(err => {
                console.error(err);
                gridContainer.innerHTML = '<div class="alert alert-danger">Failed to load grade grid.</div>';
            });
    });

    gradesForm.addEventListener('submit', function (e) {
        const inputs = document.querySelectorAll('.grade-input');
        const hasFilled = [...inputs].some(input => input.value.trim() !== '');
        if (!hasFilled) {
            e.preventDefault();
            alert('No grades entered or modified.');
        }
    });
</script>
