<?php
//import subject model and db connection
include('../config/db.php');
include('../model/subject.php');

//create an instance of the subject

$subjectModel = new Subjects($conn);

//fetch all subjects
$subjects = $subjectModel->getAllSubjects();
?>

<div class="container-fluid py-3">
    <!--add class button-->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Subject Management</h4>
        <a href="index.php?page=add-subject-form" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Subject
        </a>
    </div>

    <!-- class list -->
     <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Subject List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="">
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Subject Teacher</th>
                        <th>Actions</th>

                    </tr>
                </thead>
                <tbody>
                <?php 
                if (!empty($subjects)) {
                    foreach ($subjects as $subject) {
                        echo "<tr>
                            <td>{$subject['subject_id']}</td>
                            <td>{$subject['subject_name']}</td>
                            <td>{$subject['subject_code']}</td>
                            <td>{$subject['teacher_name']}</td>
                            <td>
                                <button class='btn btn-sm btn-primary editSubjectBtn' data-id='{$subject['subject_id']}'>Edit</button>
                                <button class='btn btn-sm btn-danger deleteSubjectBtn' data-id='{$subject['subject_id']}'>Delete</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No Subjects Found</td></tr>";
                }
                ?>
             
            
              
                </tbody>
            </table>
        </div>
     </div>
</div>