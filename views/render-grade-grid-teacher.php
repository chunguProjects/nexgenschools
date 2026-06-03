<?php
include('../config/db.php');
include('../model/student.php');
include('../model/ClassSubject.php');
include('../model/Grades.php');
include('../model/Exam.php');
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
                    </td>

                    <?php foreach ($subjects as $subject):
                        $currentGrade =
                            $existingGrades[$student['student_id']][$subject['subject_id']] ?? '';
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

<!-- Save Button -->
<button type="submit" class="btn btn-success mt-3">
    Save Grades
</button>