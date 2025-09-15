<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/class.php');
include('../model/student.php');

$classModel   = new ClassGroup($conn);
$studentModel = new Student($conn);

$classes = $classModel->getAllClasses();

// Get selected class from dropdown
$selectedClassId = $_GET['class_id'] ?? null;
$students = [];

if ($selectedClassId) {
    $students = $studentModel->getStudentsByClass($selectedClassId, getCurrentAcademicYear());
}
?>

<div class="container">
    <h3>Promote Students</h3>

    <!-- Select Current Class -->
    <form method="GET" class="mb-3">
        <label for="class_id" class="form-label">Select Current Class:</label>
        <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- Choose Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['class_id']; ?>" 
                    <?= $selectedClassId == $class['class_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($class['class_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($students)): ?>
    <!-- Students Table -->
    <form method="POST" action="../controllers/student-controller.php">
        <input type="hidden" name="promote" value="1">
        <input type="hidden" name="current_class_id" value="<?= $selectedClassId; ?>">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Student Name</th>
                    <th>Date of Birth</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><input type="checkbox" name="students[]" value="<?= $student['student_id']; ?>"></td>
                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                    <td><?= htmlspecialchars($student['date_of_birth']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Select Next Class -->
        <div class="mb-3">
            <label for="next_class_id" class="form-label">Next Class:</label>
            <select name="next_class_id" id="next_class_id" class="form-select" required>
                <option value="">-- Choose Class --</option>
                <?php foreach ($classes as $class): ?>
                    <?php if ($selectedClassId != $class['class_id']): ?>
                    <option value="<?= $class['class_id']; ?>">
                        <?= htmlspecialchars($class['class_name']); ?>
                    </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Promote Selected</button>
    </form>
    <?php elseif ($selectedClassId): ?>
        <div class="alert alert-warning">No students found in this class for the current academic year.</div>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('input[name="students[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
