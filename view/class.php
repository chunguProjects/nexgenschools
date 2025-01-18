<?php
//include model database connection file
include('../config/db.php');
include('../model/class.php');

//Create class object
$classModel = new ClassGroup($conn);

//fetch all the classes
$classes = $classModel->getAllClasses();
?>
<div class="container-fluid py-3">
    <!--add class button-->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Class Management</h4>
        <a href="index.php?page=add-class-form" class="btn btn-warning">
            <i class="fa-solid fa-plus"></i> Add Class
        </a>
    </div>

    <!-- class list -->
     <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Class List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="">
                    <tr>
                        <th>ID</th>
                        <th>Classs Name</th>
                        <th>Descrisption</th>
                        <th>Class Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                
                // Check if classes are available
                if (!empty($classes)) {
                    foreach ($classes as $class) {
                        echo "<tr>
                            <td>{$class['class_id']}</td>
                            <td>{$class['class_name']}</td>
                            <td>{$class['description']}</td>
                            <td>{$class['teacher_name']}</td>
                            <td>
                                <button class='btn btn-sm btn-primary editUserBtn' data-id='{$class['class_id']}'>Edit</button>
                                <button class='btn btn-sm btn-danger deleteUserBtn' data-id='{$class['class_id']}'>Delete</button>
                                <a href='index.php?page=add-student-form&class_id={$class['class_id']}' class='btn btn-sm btn-success'>Add Students</a>

                                </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No classes found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
     </div>
</div>