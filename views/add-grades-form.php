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

    <!-- Success / error message: fixed to the viewport so it's visible
         the instant Save is clicked, no matter where you've scrolled to -->
    <div id="formAlert" class="gs-toast-wrap"></div>

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
        <div id="grade-entry-grid" class="table-responsive" style="padding-bottom: 90px;"></div>

        <!--
            This is the ONLY save button for the form. If render-grade-grid.php
            also renders its own submit/save button for the grid, remove it
            there so we don't end up with two save buttons again.
        -->
        <button type="submit" id="saveGradesBtn" class="btn btn-primary btn-lg shadow">
            <i class="fa fa-save"></i> Save Grades
        </button>
    </form>
</div>

<style>
    /* Floating save button: stays fixed in the viewport so it's reachable
       no matter how long the student list is or how far you've scrolled. */
    #saveGradesBtn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1050;
        border-radius: 50px;
        padding: 12px 26px;
    }
    /* Toast: fixed to the top of the viewport, always visible on click,
       regardless of scroll position or how long the page is. */
    .gs-toast-wrap {
        position: fixed;
        top: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1060;
        min-width: 320px;
        max-width: 90vw;
    }
    .gs-toast-wrap .alert {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
</style>

<script>
    const classSelect = document.getElementById('class_id');
    const gridContainer = document.getElementById('grade-entry-grid');
    const gradesForm = document.getElementById('gradesForm');
    const formAlert = document.getElementById('formAlert');
    const examId = <?= json_encode($exam_id) ?>;

    // Tracks whether any grade has been typed since the last successful save,
    // so we can warn before an accidental refresh/navigation wipes it out.
    let hasUnsavedChanges = false;

    classSelect.addEventListener('change', function () {
        const classId = this.value;

        gridContainer.innerHTML = !classId
            ? '<div class="alert alert-warning">Please select a class.</div>'
            : '<div class="text-muted">Loading grade grid...</div>';

        hasUnsavedChanges = false;

        if (!classId) return;

        fetch(`../views/render-grade-grid.php?class_id=${classId}&exam_id=${examId}`)
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
                gridContainer.innerHTML = '<div class="alert alert-danger">Failed to load grade grid.</div>';
            });
    });

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
            exam_id: examId,
            class_id: classSelect.value,
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
                // number input's value, even when there's no target cell
                // (e.g. the last row).
                e.preventDefault();

                if (inputs[targetIdx]) {
                    inputs[targetIdx].focus();
                    inputs[targetIdx].select();
                }
            });
        });
    }

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

    // Shows a dismissible Bootstrap alert above the form, then fades it out on its own.
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
                // wait for the Bootstrap fade transition before removing from DOM
                setTimeout(() => { alertNode.remove(); }, 200);
            }, autoHideMs);
        }
    }

    gradesForm.addEventListener('submit', function (e) {
        // Always stop the native submit — we never want the browser to
        // navigate to the controller's URL. Everything happens over fetch.
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

        fetch(gradesForm.action, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json().catch(() => {
                // Controller didn't return valid JSON (e.g. a PHP error/notice
                // leaked into the output) — treat that as a failure too.
                throw new Error('Unexpected server response.');
            }))
            .then(data => {
                if (data.success) {
                    hasUnsavedChanges = false;
                    showFormAlert('success', data.message || 'Grades saved successfully.');
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