<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/class.php');
include('../model/academic_year.php');

// Create models
$classModel = new ClassGroup($conn);
$ayModel    = new AcademicYear($conn);

// Fetch academic years
$allYears = $ayModel->getAll();
$academicYears = [];
foreach ($allYears as $ay) {
    $academicYears[$ay['academic_year_id']] = $ay['name'];
}

// Fetch classes
$classes = $classModel->getAllClasses();

// Group classes by academic year
$classesByYear = [];
foreach ($classes as $class) {
    $yearId = $class['academic_year_id'] ?? 0;
    if (!isset($classesByYear[$yearId])) {
        $classesByYear[$yearId] = [];
    }
    $classesByYear[$yearId][] = $class;
}
?>

<div class="container-fluid py-3">

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="admin.php?page=dashboard" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        <a href="admin.php?page=add-class-form" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Class
        </a>
    </div>

    <!-- Classes Grouped by Academic Year -->
    <?php if (!empty($classesByYear)): ?>
        <?php foreach ($classesByYear as $yearId => $yearClasses): ?>
            <div class="mb-4">
                <h5 class="fw-bold mb-2">
                    Academic Year: <?= htmlspecialchars($academicYears[$yearId] ?? 'N/A') ?>
                </h5>

                <div class="card shadow-sm">
                    <div class="card-body table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class Name</th>
                                    <th>Description</th>
                                    <th>Form Teacher</th>
                                    <th>Students</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($yearClasses as $class): 
                                    $classId     = (int)$class['class_id'];
                                    $className   = htmlspecialchars($class['class_name']);
                                    $description = htmlspecialchars($class['description']);
                                    $teacherName = htmlspecialchars($class['form_teacher_name'] ?? 'Unassigned');

                                    $currentCount = $classModel->countStudentsInClass($classId, $yearId);
                                ?>
                                <tr>
                                    <td><?= $counter ?></td>
                                    <td>
                                        <a href="admin.php?page=class-detail&class_id=<?= $classId ?>" 
                                           class="text-decoration-none text-primary fw-semibold">
                                            <?= $className ?>
                                        </a>
                                    </td>
                                    <td><?= $description ?></td>
                                    <td><?= $teacherName ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $currentCount ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="admin.php?page=assign-subjects&class_id=<?= $classId ?>" 
                                               class="btn btn-sm btn-info me-1" 
                                               title="Assign Subjects & Teachers">
                                                <i class="fa fa-chalkboard-teacher"></i>
                                            </a>
                                            <a href="admin.php?page=add-student-form&class_id=<?= $classId ?>" 
                                               class="btn btn-sm btn-success me-1" 
                                               title="Add Students">
                                                <i class="fa fa-user-plus"></i>
                                            </a>
                                            <a href="admin.php?page=add-class-form&class_id=<?= $classId ?>" 
                                               class="btn btn-sm btn-primary me-1" 
                                               title="Edit Class">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger deleteClassBtn" 
                                                    data-id="<?= $classId ?>" 
                                                    title="Delete Class">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php $counter++; endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p class="text-center text-muted">No classes found.</p>
    <?php endif; ?>

</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" action="../controllers/class-controller.php" style="display:none;">
    <input type="hidden" name="class_id" id="deleteClassId">
    <input type="hidden" name="delete_class" value="1">
</form>

<!-- Delete Confirmation Script -->
<script>
document.querySelectorAll('.deleteClassBtn').forEach(button => {
    button.addEventListener('click', function () {
        const classId = this.dataset.id;

        if (confirm('Are you sure you want to delete this class and all its related data? This action cannot be undone.')) {
            document.getElementById('deleteClassId').value = classId;
            document.getElementById('deleteForm').submit();
        }
    });
});
</script>