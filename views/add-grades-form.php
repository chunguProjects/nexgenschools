<?php
// Start session and check access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
include('../model/class.php');
include('../model/exam.php');

$schoolId = $_SESSION['user']['school_id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;

if (!$schoolId || !$exam_id) {
    echo "<div class='alert alert-danger'>Missing school or exam context.</div>";
    exit;
}

// Load classes for the school
$classModel = new ClassGroup($conn);
$classes = $classModel->getAllClassesBySchool($schoolId);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="schooladmin.php?page=exam" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h4 class="mb-0">Add Grades</h4>
        <div></div> <!-- Empty div to keep layout balanced -->
    </div>

    <!-- Class Selection -->
    <div class="mb-3">
        <label for="class_id" class="form-label">Select Class</label>
        <select name="class_id" id="class_id" class="form-select" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Grade Entry Grid (Rendered Dynamically) -->
    <form id="gradesForm" action="../controllers/grades-controller.php" method="POST">
        <input type="hidden" name="exam_id" value="<?= htmlspecialchars($exam_id) ?>">
        <div id="grade-entry-grid" class="table-responsive"></div>
    </form>
</div>

<!-- JavaScript -->
<script>
    const classSelect = document.getElementById('class_id');
    const gridContainer = document.getElementById('grade-entry-grid');
    const gradesForm = document.getElementById('gradesForm');
    const examId = <?= json_encode($exam_id) ?>;

    classSelect.addEventListener('change', function () {
        const classId = this.value;

        if (!classId) {
            gridContainer.innerHTML = '<div class="alert alert-warning">Please select a class.</div>';
            return;
        }

        fetch(`../views/render-grade-grid.php?class_id=${classId}&exam_id=${examId}`)
            .then(res => res.text())
            .then(html => {
                gridContainer.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                gridContainer.innerHTML = '<div class="alert alert-danger">Failed to load grade grid.</div>';
            });
    });

    // Optional: Warn if submitting without changes
    gradesForm.addEventListener('submit', function (e) {
        const inputs = document.querySelectorAll('.grade-input');
        const hasFilled = [...inputs].some(input => input.value.trim() !== '');
        if (!hasFilled) {
            e.preventDefault();
            alert('No grades entered or modified.');
        }
    });
</script>
