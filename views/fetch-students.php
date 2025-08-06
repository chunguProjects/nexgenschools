<?php
include('../config/db.php');
include('../model/student.php');
include('../model/subject.php');

$class_id = $_GET['class_id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;

if (!$class_id || !$exam_id) {
    echo "<div class='alert alert-danger'>Missing class or exam ID.</div>";
    exit;
}

// Get students and subjects for this class
$studentModel = new Student($conn);
$subjectModel = new Subjects($conn);

$students = $studentModel->getStudentsByClass($class_id);
$subjects = $subjectModel->getSubjectsByClass($class_id); // Assumes you have this method

if (empty($students)) {
    echo "<div class='alert alert-warning'>No students found in this class.</div>";
    exit;
}

if (empty($subjects)) {
    echo "<div class='alert alert-warning'>No subjects found for this class.</div>";
    exit;
}

?>

<form action="../controllers/grades-controller.php" method="POST">
    <input type="hidden" name="exam_id" value="<?= htmlspecialchars($exam_id) ?>">
    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">

    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-light sticky-top">
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
                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                        <?php foreach ($subjects as $subject): ?>
                            <td>
                                <input type="number" name="grades[<?= $student['student_id'] ?>][<?= $subject['subject_id'] ?>]" 
                                       min="0" max="100" class="form-control" placeholder="-" />
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <button type="submit" class="btn btn-success mt-3">Save All Grades</button>
</form>
