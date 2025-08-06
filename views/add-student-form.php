<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
include('../model/student.php');
include('../model/class.php');

$classId = $_GET['class_id'] ?? null;
$schoolId = $_SESSION['user']['school_id'] ?? null;

if (!$classId || !$schoolId) {
    echo "<div class='alert alert-danger'>Class or school not specified.</div>";
    exit;
}

// Get class name
$classModel = new ClassGroup($conn);
$classData = $classModel->getClassById($classId);

// Load existing students to prefill form for update
$studentModel = new Student($conn);
$existingStudents = $studentModel->getStudentsByClass($classId);

?>

<div class="container mt-5">
    <div class="d-flex align-items-center mb-4">
        <a href="schooladmin.php?page=class" class="btn btn-outline-secondary me-3">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h4 class="mb-0">Manage Students for <span class="text-primary"><?= htmlspecialchars($classData['name']) ?></span></h4>
    </div>

    <form method="POST" action="../controllers/student-controller.php" class="mb-5">
        <input type="hidden" name="class_id" value="<?= $classId ?>" />
        <div class="table-responsive">
            <table class="table table-bordered" id="studentTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Parent Phone</th>
                        <th>Relationship</th>
                        <th>Delete?</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($existingStudents)): ?>
                        <?php foreach ($existingStudents as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <input type="hidden" name="student_id[]" value="<?= $student['student_id'] ?>">
                                <td><input type="text" name="first_name[]" class="form-control" required value="<?= htmlspecialchars($student['first_name']) ?>"></td>
                                <td><input type="text" name="last_name[]" class="form-control" required value="<?= htmlspecialchars($student['last_name']) ?>"></td>
                                <td>
                                    <select name="gender[]" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </td>
                                <td><input type="date" name="date_of_birth[]" class="form-control" value="<?= htmlspecialchars($student['date_of_birth']) ?>"></td>
                                <td><input type="text" name="parent_phone[]" class="form-control" required value="<?= htmlspecialchars($student['contact_number']) ?>"></td>
                            
                                <td>
                                    <input type="checkbox" name="delete_student[]" value="<?= $student['student_id'] ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td>1</td>
                            <input type="hidden" name="student_id[]" value="">
                            <td><input type="text" name="first_name[]" class="form-control" required></td>
                            <td><input type="text" name="last_name[]" class="form-control" required></td>
                            <td>
                                <select name="gender[]" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </td>
                            <td><input type="date" name="date_of_birth[]" class="form-control"></td>
                            <td><input type="text" name="parent_phone[]" class="form-control" required></td>
                            <td>
                                <select name="relationship[]" class="form-select">
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Guardian</option>
                                </select>
                            </td>
                            <td></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="button" id="addRow" class="btn btn-primary"><i class="fa fa-plus"></i> Add Row</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Students</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('addRow').addEventListener('click', function () {
        const table = document.getElementById('studentTable').getElementsByTagName('tbody')[0];
        const rowCount = table.rows.length;
        const newRow = table.insertRow();

        newRow.innerHTML = `
            <td>${rowCount + 1}</td>
            <input type="hidden" name="student_id[]" value="">
            <td><input type="text" name="first_name[]" class="form-control" required></td>
            <td><input type="text" name="last_name[]" class="form-control" required></td>
            <td>
                <select name="gender[]" class="form-select" required>
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </td>
            <td><input type="date" name="date_of_birth[]" class="form-control"></td>
            <td><input type="text" name="parent_phone[]" class="form-control" required></td>
            <td>
                <select name="relationship[]" class="form-select">
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Guardian">Guardian</option>
                </select>
            </td>
            <td></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
        `;

        newRow.querySelector('.remove-row').addEventListener('click', () => {
            newRow.remove();
            updateRowNumbers();
        });

        updateRowNumbers();
    });

    document.querySelectorAll('.remove-row').forEach(button => {
        button.addEventListener('click', function () {
            const row = button.closest('tr');
            row.remove();
            updateRowNumbers();
        });
    });

    function updateRowNumbers() {
        const rows = document.querySelectorAll("#studentTable tbody tr");
        rows.forEach((row, index) => {
            row.cells[0].innerText = index + 1;
        });
    }
</script>
