<?php
// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/class.php');
include('../model/academic_year.php');

// Create models
$classModel = new ClassGroup($conn);
$ayModel = new AcademicYear($conn);

// Fetch classes
$classes = $classModel->getAllClasses();
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

    <!-- Class List -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Classes</h5>
            <a href="../controllers/export-parent-credentials.php" class="btn btn-sm btn-success">
                <i class="fa fa-download"></i> Download Parent Credentials (CSV)
            </a>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Class Name</th>
                        <th>Description</th>
                        <th>Class Teacher</th>
                        <th>Academic Year</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($classes)) {
                        $counter = 1;
                        foreach ($classes as $class) {
                            $academicYearId = $class['academic_year_id'] ?? 0;

                            $currentCount = $classModel->countStudentsInClass($class['class_id'], $academicYearId, true);
                            $totalCount   = $classModel->countStudentsInClass($class['class_id'], $academicYearId, false);

                            $studentCountDisplay = "<span class='badge bg-secondary'>{$totalCount}";
                            $className = htmlspecialchars($class['class_name']);
                            $description = htmlspecialchars($class['description']);
                            $teacherName = htmlspecialchars($class['teacher_name']);
                            $classId = (int)$class['class_id'];

                            // Get academic year
                            $academicYearName = '';
                            if (!empty($class['academic_year_id'])) {
                                $ay = $ayModel->getById($class['academic_year_id']);
                                $academicYearName = $ay ? htmlspecialchars($ay['name']) : 'N/A';
                            }

                            echo "
                                <tr>
                                    <td>{$counter}</td>
                                    <td>
                                        <a href='admin.php?page=class-detail&class_id={$classId}' class='text-decoration-none text-primary fw-semibold'>
                                            {$className}
                                        </a>
                                    </td>
                                    <td>{$description}</td>
                                    <td>{$teacherName}</td>
                                    <td>{$academicYearName}</td>
                                    <td>{$studentCountDisplay}</td>
                                    <td>
                                        <a href='admin.php?page=add-student-form&class_id={$classId}' class='btn btn-sm btn-success me-1' title='Add Students'>
                                            <i class='fa fa-user-plus'></i>
                                        </a>
                                        <a href='admin.php?page=add-class-form&class_id={$classId}' class='btn btn-sm btn-primary me-1' title='Edit Class'>
                                            <i class='fa fa-edit'></i>
                                        </a>
                                        <button class='btn btn-sm btn-danger deleteClassBtn' data-id='{$classId}' title='Delete Class'>
                                            <i class='fa fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No classes found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
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
