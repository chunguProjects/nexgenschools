<?php
include('../config/db.php');
include('../model/student.php');
include('../model/classSubject.php');
include('../model/grades.php');
include('../model/exam.php');
include('../model/class.php');

session_start();

$teacherId = $_SESSION['user']['user_id'] ?? null;
if (!$teacherId) {
    exit("<div class='alert alert-danger'>Unauthorized access.</div>");
}

// Get params
$class_id = intval($_GET['class_id'] ?? 0);
$exam_id = intval($_GET['exam_id'] ?? 0);

if (!$class_id || !$exam_id) {
    exit("<div class='alert alert-danger'>Missing required parameters.</div>");
}

// Load exam
$examModel = new Exams($conn);
$exam = $examModel->getExamById($exam_id);

if (!$exam) {
    exit("<div class='alert alert-danger'>Exam not found.</div>");
}

$academic_year_id = $exam['academic_year_id'];

// Load CLASS MODEL (for teacher filtering)
$classModel = new ClassGroup($conn);

// 🔐 IMPORTANT: ONLY subjects assigned to teacher
$subjects = $classModel->getTeacherSubjectsByClass(
    $teacherId,
    $class_id,
    $academic_year_id
);

if (empty($subjects)) {
    exit("<div class='alert alert-warning'>No subjects assigned to you in this class.</div>");
}

// Load students
$studentModel = new Student($conn);
$students = $studentModel->getStudentsByClass($class_id, $academic_year_id);

if (empty($students)) {
    exit("<div class='alert alert-warning'>No students found in this class.</div>");
}

// Load existing grades
$gradesModel = new Grades($conn);
$existingGrades = $gradesModel->getGradesMap($exam_id, $class_id);
?>

<div class="grade-sheet-wrap table-responsive" data-col-count="<?= count($subjects) ?>">
    <table class="table table-bordered table-sm grade-sheet">
        <thead>
            <tr>
                <th class="gs-name-col">Student Name</th>
                <?php foreach ($subjects as $subject): ?>
                    <th><?= htmlspecialchars($subject['subject_code'] ?? $subject['subject_name'] ?? '') ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td class="gs-name-col">
                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                    </td>

                    <?php foreach ($subjects as $subject):
                        $currentGrade =
                            $existingGrades[$student['student_id']][$subject['subject_id']] ?? '';
                    ?>
                        <td class="gs-cell">
                            <input
                                type="number"
                                name="grades[<?= $student['student_id'] ?>][<?= $subject['subject_id'] ?>]"
                                class="form-control form-control-sm grade-input"
                                min="0" max="100"
                                value="<?= htmlspecialchars($currentGrade) ?>"
                                placeholder="0"
                                data-student-id="<?= $student['student_id'] ?>"
                                data-subject-id="<?= $subject['subject_id'] ?>"
                                autocomplete="off"
                            >
                            <span class="gs-status" aria-hidden="true"></span>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Hidden Inputs -->
<input type="hidden" name="class_id" id="grid_class_id" value="<?= htmlspecialchars($class_id) ?>">
<input type="hidden" name="exam_id" id="grid_exam_id" value="<?= htmlspecialchars($exam_id) ?>">

<!--
    No save button here on purpose — it belongs on the parent form page as a
    floating button (like the admin add-grades-form.php), otherwise you'll
    end up with two save buttons again once the parent page has its own.
-->

<style>
    /* Spreadsheet look */
    .grade-sheet-wrap {
        max-height: 65vh;
        overflow: auto;
        border: 1px solid #d3d3d3;
    }
    table.grade-sheet {
        width: auto;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    table.grade-sheet thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f1f3f5;
        border-bottom: 2px solid #c7c7c7;
        font-size: 13px;
        text-align: center;
        white-space: nowrap;
    }
    table.grade-sheet .gs-name-col {
        position: sticky;
        left: 0;
        z-index: 1;
        background: #fff;
        text-align: left;
        white-space: nowrap;
        min-width: 180px;
    }
    table.grade-sheet thead th.gs-name-col {
        z-index: 3;
    }
    table.grade-sheet td, table.grade-sheet th {
        border: 1px solid #dfe1e3;
    }
    td.gs-cell {
        padding: 0;
        position: relative;
        width: 78px;
    }
    td.gs-cell input.grade-input {
        border: none;
        border-radius: 0;
        text-align: center;
        height: 34px;
        width: 100%;
        box-shadow: none;
    }
    /* remove number spinner arrows for a cleaner spreadsheet feel */
    td.gs-cell input.grade-input::-webkit-outer-spin-button,
    td.gs-cell input.grade-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    td.gs-cell input.grade-input[type=number] {
        -moz-appearance: textfield;
    }
    td.gs-cell input.grade-input:focus {
        outline: 2px solid #1a73e8;
        outline-offset: -2px;
        z-index: 1;
        position: relative;
    }
    /* autosave status dot, top-right corner of the cell */
    .gs-status {
        position: absolute;
        top: 3px;
        right: 4px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: transparent;
        transition: background-color .2s ease;
    }
    .gs-status.gs-saving { background: #f0ad4e; }
    .gs-status.gs-saved   { background: #2e9e5b; }
    .gs-status.gs-error   { background: #d9534f; }
    td.gs-cell input.grade-input.gs-cell-error { background: #fdecea; }
</style>