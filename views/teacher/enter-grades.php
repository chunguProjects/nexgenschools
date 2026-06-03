<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
include('../model/class.php');
include('../model/exam.php');
require_once('../model/GeneralSettingsModel.php');

// Teacher session check
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'teacher') {
    header("Location: ../views/login.php");
    exit;
}

$teacherId = $_SESSION['user']['user_id'] ?? null;

// --- Get active academic year ---
$settingsModel = new GeneralSettingsModel($conn);
$activeYear = $settingsModel->getActiveAcademicYear();
if (!$activeYear) {
    echo "<div class='alert alert-warning'>Active academic year not found.</div>";
    exit;
}
$academicYearId = $activeYear['academic_year_id'];

// --- Get class from URL ---
$classId = $_GET['class_id'] ?? null;
if (!$classId) {
    echo "<div class='alert alert-danger'>No class selected.</div>";
    exit;
}

// --- Verify that the teacher is assigned to this class ---
$classModel = new ClassGroup($conn);
$teacherClasses = $classModel->getTeacherClasses($teacherId, $academicYearId);
$allowedClassIds = array_column($teacherClasses, 'class_id');
if (!in_array($classId, $allowedClassIds)) {
    echo "<div class='alert alert-danger'>You are not assigned to this class.</div>";
    exit;
}

$class = $classModel->getClassById($classId);

// --- Get exams for this academic year ---
$examModel = new Exams($conn);
$exams = $examModel->getExamsByYear($academicYearId); 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="teacher.php?page=classes" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h4 class="mb-0">Enter Grades for <?= htmlspecialchars($class['name']) ?></h4>
        <div></div>
    </div>

    <div class="mb-3">
        <label for="exam_id" class="form-label">Select Exam</label>
        <select name="exam_id" id="exam_id" class="form-select" required>
            <option value="">-- Select Exam --</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= $exam['id'] ?>">
                    <?= htmlspecialchars($exam['exam_name']) ?> (<?= htmlspecialchars($exam['term']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <form id="gradesForm" action="../controllers/grades-controller.php" method="POST">
        <input type="hidden" name="class_id" value="<?= htmlspecialchars($classId) ?>">
        <input type="hidden" name="teacher_id" value="<?= $teacherId ?>">
        <div id="grade-entry-grid" class="table-responsive"></div>
    </form>
</div>
<script>
const examSelect = document.getElementById('exam_id');
const gridContainer = document.getElementById('grade-entry-grid');
const gradesForm = document.getElementById('gradesForm');

// Success/Error message area
const messageBox = document.createElement('div');
messageBox.id = 'gradeMessage';
gradesForm.parentNode.insertBefore(messageBox, gradesForm);

// Load grade grid
function loadGradeGrid() {
    const examId = examSelect.value;

    if (!examId) {
        gridContainer.innerHTML =
            '<div class="alert alert-warning">Please select an exam.</div>';
        return;
    }

    gridContainer.innerHTML =
        '<div class="text-muted">Loading grade grid...</div>';

    fetch(`../views/render-grade-grid-teacher.php?class_id=<?= $classId ?>&exam_id=${examId}`)
        .then(res => res.text())
        .then(html => {
            gridContainer.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            gridContainer.innerHTML =
                '<div class="alert alert-danger">Failed to load grade grid.</div>';
        });
}

// Exam changed
examSelect.addEventListener('change', loadGradeGrid);

// Save grades
gradesForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const inputs = document.querySelectorAll('.grade-input');
    const hasFilled = [...inputs].some(input => input.value.trim() !== '');

    if (!hasFilled) {
        alert('No grades entered or modified.');
        return;
    }

    const formData = new FormData(gradesForm);

    fetch(gradesForm.action, {
        method: 'POST',
        body: formData
    })
    .then(async res => {
        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Failed to save grades.');
        }

        return data;
    })
    .then(data => {

        // Show success message
        messageBox.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show">
                ${data.message || 'Grades saved successfully.'}
            </div>
        `;

        // Reload grade grid with saved values
        loadGradeGrid();

        // Auto-hide message after 3 seconds
        setTimeout(() => {
            const alert = messageBox.querySelector('.alert');

            if (alert) {
                alert.classList.remove('show');

                setTimeout(() => {
                    messageBox.innerHTML = '';
                }, 500);
            }
        }, 3000);

    })
    .catch(err => {

        messageBox.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show">
                ${err.message}
            </div>
        `;

        setTimeout(() => {
            messageBox.innerHTML = '';
        }, 4000);

        console.error(err);
    });
});
</script>