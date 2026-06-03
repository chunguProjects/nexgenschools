<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/db.php');
require_once('../model/class.php');
require_once('../model/GeneralSettingsModel.php');

$teacherId = $_SESSION['user']['user_id'] ?? null;
if (!$teacherId) {
    header("Location: ../views/login.php");
    exit;
}

// Get active academic year
$settingsModel = new GeneralSettingsModel($conn);
$activeYear = $settingsModel->getActiveAcademicYear(); // Should return ['academic_year_id', 'name', ...]

if (!$activeYear) {
    echo "<div class='alert alert-warning'>Active academic year not found.</div>";
    exit;
}

$academicYearId = $activeYear['academic_year_id'];

// Get teacher's classes
$classModel = new ClassGroup($conn);
$classes = $classModel->getTeacherClasses($teacherId, $academicYearId);
?>

<div class="container py-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-users me-2 text-primary"></i>My Classes</h4>
    <p class="text-muted">These are the classes you’re assigned to teach in the academic year <strong><?= htmlspecialchars($activeYear['name']) ?></strong>.</p>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Class Name</th>
                        <th>Description</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($classes)): ?>
                        <?php foreach ($classes as $class): ?>
                            <?php 
                                $studentCount = $classModel->countStudentsInClass($class['class_id'], $academicYearId); 
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($class['name']) ?></td>
                                <td><?= htmlspecialchars($class['description']) ?></td>
                                <td><?= $studentCount ?></td>
                                <td>
                                    <!-- <a href="teacher.php?page=view-students&class_id=<?= $class['class_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        View Students
                                    </a> -->
                                    <a href="teacher.php?page=enter-grades&class_id=<?= $class['class_id'] ?>" 
                                       class="btn btn-sm btn-outline-success">
                                        Enter Grades
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                You have no classes assigned for this academic year.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
