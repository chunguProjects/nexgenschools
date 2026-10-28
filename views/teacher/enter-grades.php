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
    <div class="lg-page-header">
        <a href="teacher.php?page=classes" class="lg-btn-back">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h4 class="lg-page-title mb-0">Enter Grades for <?= htmlspecialchars($class['name']) ?></h4>
        <div></div>
    </div>

    <!-- Success / error message: fixed to the viewport so it's visible
         the instant Save is clicked, no matter where you've scrolled to -->
    <div id="formAlert" class="gs-toast-wrap"></div>

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
        <div id="grade-entry-grid" class="table-responsive" style="padding-bottom: 90px;"></div>

        <!-- Only save button — the partial (render-grade-grid-teacher.php)
             doesn't render its own, on purpose. -->
        <button type="submit" id="saveGradesBtn" class="btn btn-ledger-solid btn-lg shadow">
            <i class="fa fa-save"></i> Save Grades
        </button>
    </form>
</div>

<script>
    const examSelect = document.getElementById('exam_id');
    const gridContainer = document.getElementById('grade-entry-grid');
    const gradesForm = document.getElementById('gradesForm');
    const formAlert = document.getElementById('formAlert');
    const classId = <?= json_encode($classId) ?>;

    // Tracks whether any grade has been typed since the last successful save,
    // so we can warn before an accidental refresh/navigation wipes it out.
    let hasUnsavedChanges = false;

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

        fetch(`../views/render-grade-grid-teacher.php?class_id=${classId}&exam_id=${examId}`)
            .then(res => res.text())
            .then(html => {
                gridContainer.innerHTML = html;
                // <script> tags injected via innerHTML never execute, so the
                // grid's behavior (autosave + keyboard nav) is wired up here
                // instead, every time fresh grid HTML lands in the DOM.
                initGradeGrid();
            })
            .catch(err => {
                console.error(err);
                gridContainer.innerHTML =
                    '<div class="alert alert-danger">Failed to load grade grid.</div>';
            });
    }

    // Exam changed
    examSelect.addEventListener('change', function () {
        hasUnsavedChanges = false;
        loadGradeGrid();
    });

    // Delegated listener: catches typing in .grade-input fields even though
    // they're loaded into the grid after this script has already run.
    gridContainer.addEventListener('input', function (e) {
        if (e.target.classList.contains('grade-input')) {
            hasUnsavedChanges = true;
        }
    });

    window.addEventListener('beforeunload', function (e) {
        if (!hasUnsavedChanges) return;
        e.preventDefault();
        e.returnValue = ''; // required for the browser's native confirmation dialog to show
    });

    // Shows a dismissible Bootstrap alert as a fixed toast, then fades it out on its own.
    function showFormAlert(type, message, autoHideMs = 3500) {
        formAlert.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const alertNode = formAlert.querySelector('.alert');
        if (autoHideMs && alertNode) {
            setTimeout(() => {
                alertNode.classList.remove('show');
                setTimeout(() => { alertNode.remove(); }, 200);
            }, autoHideMs);
        }
    }

    // --- Grade grid behavior: autosave per cell + Excel-style navigation ---

    function gridStatusEl(input) {
        return input.parentElement.querySelector('.gs-status');
    }

    function setGridCellStatus(input, state) {
        const el = gridStatusEl(input);
        if (!el) return;
        el.classList.remove('gs-saving', 'gs-saved', 'gs-error');
        input.classList.remove('gs-cell-error');
        if (state) el.classList.add(state);
        if (state === 'gs-error') input.classList.add('gs-cell-error');
        if (state === 'gs-saved') {
            setTimeout(() => el.classList.remove('gs-saved'), 1500);
        }
    }

    function autosaveCell(input) {
        const mark = input.value.trim();
        setGridCellStatus(input, 'gs-saving');

        const body = new URLSearchParams({
            exam_id: examSelect.value,
            class_id: classId,
            student_id: input.dataset.studentId,
            subject_id: input.dataset.subjectId,
            mark: mark
        });

        fetch('../controllers/autosave-grade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(res => res.json().catch(() => ({ success: false, message: 'Bad server response.' })))
            .then(data => {
                setGridCellStatus(input, data.success ? 'gs-saved' : 'gs-error');
                if (data.success) hasUnsavedChanges = false;
                if (!data.success) console.warn('Autosave failed:', data.message);
            })
            .catch(err => {
                console.error(err);
                setGridCellStatus(input, 'gs-error');
            });
    }

    function initGradeGrid() {
        const wrap = gridContainer.querySelector('.grade-sheet-wrap');
        if (!wrap) return;

        const inputs = Array.from(wrap.querySelectorAll('.grade-input'));
        const colCount = parseInt(wrap.dataset.colCount || '0', 10);

        // Save as soon as a cell is left after being changed — this is what
        // protects entries if the power/connection drops mid-session, since
        // marks are written cell by cell instead of only at the very end.
        inputs.forEach(input => {
            input.addEventListener('change', () => autosaveCell(input));
        });

        // Excel-style keyboard navigation between cells.
        inputs.forEach((input, idx) => {
            input.addEventListener('keydown', (e) => {
                let targetIdx = null;

                switch (e.key) {
                    case 'ArrowRight':
                        targetIdx = idx + 1;
                        break;
                    case 'ArrowLeft':
                        targetIdx = idx - 1;
                        break;
                    case 'ArrowDown':
                    case 'Enter':
                        targetIdx = idx + colCount;
                        break;
                    case 'ArrowUp':
                        targetIdx = idx - colCount;
                        break;
                    default:
                        return; // let the browser handle everything else (typing, Tab, etc.)
                }

                // Always stop the browser's default action for these keys —
                // otherwise Enter submits the form, and Up/Down nudge the
                // number input's value, even when there's no target cell.
                e.preventDefault();

                if (inputs[targetIdx]) {
                    inputs[targetIdx].focus();
                    inputs[targetIdx].select();
                }
            });
        });
    }

    // Save grades (full-form fallback save, on top of per-cell autosave)
    gradesForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const inputs = document.querySelectorAll('.grade-input');
        const hasFilled = [...inputs].some(input => input.value.trim() !== '');

        if (!hasFilled) {
            showFormAlert('warning', 'No grades entered or modified.');
            return;
        }

        const submitBtn = gradesForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saving...';
        }

        const formData = new FormData(gradesForm);
        formData.append('exam_id', examSelect.value);

        fetch(gradesForm.action, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json().catch(() => {
                throw new Error('Unexpected server response.');
            }))
            .then(data => {
                if (data.success) {
                    hasUnsavedChanges = false;
                    showFormAlert('success', data.message || 'Grades saved successfully.');
                    loadGradeGrid(); // refresh grid with saved values
                } else {
                    showFormAlert('danger', data.message || 'Failed to save grades.');
                }
            })
            .catch(err => {
                console.error(err);
                showFormAlert('danger', 'Something went wrong while saving grades. Please try again.');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
    });
</script>