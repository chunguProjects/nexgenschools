<?php
include('../config/db.php');
include('../model/student.php');
include('../model/subject.php');
include('../model/grades.php');

session_start();

// Get and validate parameters
$class_id = $_GET['class_id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;
$school_id = $_SESSION['user']['school_id'] ?? null;

if (!$class_id || !$exam_id || !$school_id) {
    echo "<div class='alert alert-danger'>Missing required parameters.</div>";
    exit;
}

// Load students and subjects
$studentModel = new Student($conn);
$students = $studentModel->getStudentsByClass($class_id);

$subjectModel = new Subjects($conn);
$subjects = $subjectModel->getAllSubjects($school_id);

// Load existing grades
$gradesModel = new Grades($conn);
$existingGrades = $gradesModel->getGradesMap($school_id, $exam_id, $class_id); // returns [student_id][subject_id] => marks

if (empty($students) || empty($subjects)) {
    echo "<div class='alert alert-warning'>No students or subjects found for this class.</div>";
    exit;
}
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
                                data-original-value="<?= htmlspecialchars($currentGrade) ?>"
                                oninput="detectGradeChange()"
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
<button type="submit" id="save-grades-btn" class="btn btn-primary mt-3">Save Grades</button>

<script>
function detectGradeChange() {
    const inputs = document.querySelectorAll('.grade-input');
    const saveButton = document.getElementById('save-grades-btn');
    let hasChanges = false;

    inputs.forEach(input => {
        const original = input.dataset.originalValue;
        if (input.value !== original) {
            hasChanges = true;
        }
    });

    if (hasChanges) {
        saveButton.disabled = false;
        saveButton.classList.remove('btn-secondary');
        saveButton.classList.add('btn-success');
    } else {
        saveButton.disabled = true;
        saveButton.classList.remove('btn-success');
        saveButton.classList.add('btn-secondary');
    }
}
</script>
