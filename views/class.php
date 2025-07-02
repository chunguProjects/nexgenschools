<?php
// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/class.php');

// Ensure user is logged in and has a school_id
$schoolId = $_SESSION['user']['school_id'] ?? null;
if (!$schoolId) {
    echo "<div class='alert alert-danger'>Unauthorized access. School not identified.</div>";
    exit;
}

// Create class model and fetch classes for the current school
$classModel = new ClassGroup($conn);
$classes = $classModel->getAllClassesBySchool($schoolId);
?>
<div class="container-fluid py-3">
    <!-- Add Class Button -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Back Button -->
    <a href="schooladmin.php?page=dashboard" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <!-- Add Class Button -->
    <a href="schooladmin.php?page=add-class-form" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Class
    </a>
</div>


    <!-- Class List -->
    <div class="card shadow-sm">
        <div class="card-header">
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
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($classes)) {
                        $counter = 1;
                        foreach ($classes as $class) {
                            $studentCount = $classModel->countStudentsInClass($class['class_id']);
                            echo "
                                <tr>
                                    <td>{$counter}</td>
                                    <td>
                                        <a href='schooladmin.php?page=class-detail&class_id={$class['class_id']}' class='text-decoration-none text-primary fw-semibold'>
                                            {$class['class_name']}
                                        </a>
                                    </td>
                                    <td>{$class['description']}</td>
                                    <td>{$class['teacher_name']}</td>
                                    <td><span class='badge bg-secondary'>{$studentCount}</span></td>
                                    <td>
                                        <a href='schooladmin.php?page=add-student-form&class_id={$class['class_id']}' class='btn btn-sm btn-success'>
                                            <i class='fa fa-user-plus'></i> Add Students
                                        </a>
                                        <button class='btn btn-sm btn-primary editClassBtn' data-id='{$class['class_id']}'>
                                            <i class='fa fa-edit'></i>
                                        </button>
                                        <button class='btn btn-sm btn-danger deleteClassBtn' data-id='{$class['class_id']}'>
                                            <i class='fa fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No classes found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
