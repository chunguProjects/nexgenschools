<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../config/db.php');
include('../model/student.php');
include('../model/class.php');

$classId = $_GET['class_id'] ?? null;

if (!$classId) {
    echo "<div class='alert alert-danger'>Invalid class ID.</div>";
    exit;
}

$classModel = new ClassGroup($conn);
$studentModel = new Student($conn);

$classInfo = $classModel->getClassById($classId);

if (!$classInfo) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    exit;
}

$students = $studentModel->getStudentsByClass($classId);
?>

<div class="container mt-3">
    <div class="d-flex align-items-center mb-4">
        <a href="admin.php?page=class" class="btn btn-outline-secondary me-3">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h5 class="mb-0">Class: <?= htmlspecialchars($classInfo['name']) ?></h5>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Students in this class</span>
            <a href="admin.php?page=add-student-form&class_id=<?= $classId ?>" class="btn btn-primary">
                <i class="fa fa-users-cog"></i> Manage Students
            </a>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Contact Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): 
                        $counter = 1;
                        foreach ($students as $student): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                <td><?= htmlspecialchars($student['gender']) ?></td>
                                <td><?= htmlspecialchars($student['date_of_birth']) ?></td>
                                <td><?= htmlspecialchars($student['contact_number']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No students enrolled in this class.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
