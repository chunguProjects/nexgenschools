<?php
include('../config/db.php');
include('../model/student.php');
include('../model/classSubject.php');
include('../model/grades.php');
include('../model/exam.php');

session_start();

// Get and validate parameters
$class_id = intval($_GET['class_id'] ?? 0);
$exam_id = intval($_GET['exam_id'] ?? 0);

if (!$class_id || !$exam_id ) {
    echo "<div class='alert alert-danger'>Missing required parameters.</div>";
    exit;
}

// Load exam to get academic year
$examModel = new Exams($conn);
$exam = $examModel->getExamById($exam_id);

if (!$exam) {
    echo "<div class='alert alert-danger'>Exam not found.</div>";
    exit;
}
$academic_year_id = $exam['academic_year_id'];

// Load only assigned subjects for this class & academic year
$classSubjModel = new ClassSubject($conn);
$subjects = $classSubjModel->getByClass($class_id, $academic_year_id);

if (empty($subjects)) {
    echo "<div class='alert alert-warning'>No subjects assigned to this class.</div>";
    exit;
}

// Load students in this class & academic year
$studentModel = new Student($conn);
$students = $studentModel->getStudentsByClass($class_id, $academic_year_id);

if (empty($students)) {
    echo "<div class='alert alert-warning'>No students found in this class.</div>";
    exit;
}

// Load existing grades
$gradesModel = new Grades($conn);
$existingGrades = $gradesModel->getGradesMap($exam_id, $class_id); // [student_id][subject_id] => marks
?>

<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Student Name</th>
                <?php foreach ($subjects as $subject): ?>
                    <th><?= htmlspecialchars($subject['subject_name']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        <input type="hidden" name="student_ids[]" value="<?= $student['student_id'] ?>">
                    </td>
                    <?php foreach ($subjects as $subject):
                        $currentGrade = $existingGrades[$student['student_id']][$subject['subject_id']] ?? '';
                    ?>
                        <td>
                            <input 
                                type="number"
                                name="grades[<?= $student['student_id'] ?>][<?= $subject['subject_id'] ?>]"
                                class="form-control form-control-sm grade-input"
                                min="0" max="100"
                                value="<?= htmlspecialchars($currentGrade) ?>"
                                placeholder="0"
                            >
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Hidden Inputs -->
<input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
<input type="hidden" name="exam_id" value="<?= htmlspecialchars($exam_id) ?>">

<!-- Save Button (always enabled) -->
<button type="submit" id="save-grades-btn" class="btn btn-success mt-3">Save Grades</button>

