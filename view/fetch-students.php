<?php
// Include database and models
include('../config/db.php');
include('../model/student.php');

// Get Class ID, Exam ID, and Subject ID from request
$class_id = $_GET['class_id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;

if (!$class_id || !$exam_id || !$subject_id) {
    echo "<div class='alert alert-danger'>Invalid request. Class ID, Exam ID, or Subject ID is missing.</div>";
    exit;
}

$studentModel = new Student($conn);
$students = $studentModel->getStudentsByClass($class_id);

if (!empty($students)) {
    echo "<form action='../controllers/grades-controller.php' method='POST'>
            <input type='hidden' name='exam_id' value='" . htmlspecialchars($exam_id) . "'>
            <input type='hidden' name='class_id' value='" . htmlspecialchars($class_id) . "'>
            <input type='hidden' name='subject_id' value='" . htmlspecialchars($subject_id) . "'>
            <table class='table table-bordered mt-4'>
                <thead class='table-light'>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>";
    foreach ($students as $index => $student) {
        echo "<tr>
                <td>" . ($index + 1) . "</td>
                <td>
                    " . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . "
                    <input type='hidden' name='student_ids[]' value='" . htmlspecialchars($student['student_id']) . "'>
                </td>
                <td>
                    <input type='text' name='grades[]' class='form-control' required>
                </td>
              </tr>";
    }
    echo "</tbody>
            </table>
            <button type='submit' class='btn btn-success mt-3'>Save Grades</button>
          </form>";
} else {
    echo "<div class='alert alert-warning mt-3'>No students found for the selected class.</div>";
}
?>
