<?php
// Include database and necessary models
include('../config/db.php');
include('../model/class.php');
include('../model/subject.php');

// Fetch the exam ID from the URL
$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    die("Exam ID is required.");
}

// Fetch classes and subjects
$classModel = new ClassGroup($conn);
$classes = $classModel->getAllClasses();

$subjectModel = new Subjects($conn);
$subjects = $subjectModel->getAllSubjects();
?>

<div class="container mt-5">
    <h4 class="mb-4">Add Grades</h4>

    <!-- Form to Select Class and Subject -->
    <div class="row mb-3">
        <!-- Select Class -->
        <div class="col-md-6">
            <label for="class_id" class="form-label">Select Class</label>
            <select name="class_id" id="class_id" class="form-select" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $class) : ?>
                    <option value="<?php echo $class['class_id']; ?>">
                        <?php echo htmlspecialchars($class['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Select Subject -->
        <div class="col-md-6">
            <label for="subject_id" class="form-label">Select Subject</label>
            <select name="subject_id" id="subject_id" class="form-select" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $subject) : ?>
                    <option value="<?php echo $subject['subject_id']; ?>">
                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Placeholder for Students -->
    <div id="student-list" class="mt-4"></div>
</div>

<script>
document.getElementById('class_id').addEventListener('change', function() {
    const classId = this.value;
    const examId = <?php echo json_encode($exam_id); ?>;
    const subjectId = document.getElementById('subject_id').value; // Get the selected subject ID

    if (classId && subjectId) {
        // Send AJAX request to fetch students
        fetch(`../view/fetch-students.php?class_id=${classId}&exam_id=${examId}&subject_id=${subjectId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('student-list').innerHTML = data;
            })
            .catch(error => console.error('Error fetching students:', error));
    } else {
        // Clear the student list if no class or subject is selected
        document.getElementById('student-list').innerHTML =
            '<div class="alert alert-warning">Please select both class and subject.</div>';
    }
});

</script>
