<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/class.php');
include('../model/academic_year.php');


$classModel = new ClassGroup($conn);
$ayModel    = new AcademicYear($conn);


// Academic years
$allYears = $ayModel->getAll();

$academicYears = [];

foreach ($allYears as $ay) {

    $academicYears[$ay['academic_year_id']] = $ay['name'];

}



// Classes
$classes = $classModel->getAllClasses();


// Group by academic year

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


    <!-- PAGE HEADER -->

    <div class="lg-page-header">


        <a href="admin.php?page=dashboard"
           class="lg-btn-back text-decoration-none">

            <i class="fa fa-arrow-left"></i>
            Back

        </a>




        <h4 class="lg-page-title mb-0">

            Classes

        </h4>




        <a href="admin.php?page=add-class-form"
           class="lg-btn-add text-decoration-none">

            <i class="fa fa-plus"></i>
            Add Class

        </a>


    </div>






    <!-- CLASS LIST -->


    <?php if (!empty($classesByYear)): ?>


        <?php foreach ($classesByYear as $yearId => $yearClasses): ?>



            <div class="mb-4">



                <h5 class="lg-page-title mb-3">

                    Academic Year:
                    <?= htmlspecialchars($academicYears[$yearId] ?? 'N/A') ?>

                </h5>





                <div class="card">


                    <div class="lg-card-header">


                        <h5 class="mb-0">
                            Class List
                        </h5>


                    </div>





                    <div class="card-body table-responsive">



                        <table class="table lg-table align-middle">


                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>
                                        Class Name
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Form Teacher
                                    </th>

                                    <th>
                                        Students
                                    </th>

                                    <th>
                                        Actions
                                    </th>


                                </tr>


                            </thead>



                            <tbody>


                            <?php $counter = 1; ?>


                            <?php foreach ($yearClasses as $class): ?>


                                <?php

                                $classId = (int)$class['class_id'];

                                $studentCount = 
                                    $classModel->countStudentsInClass(
                                        $classId,
                                        $yearId
                                    );

                                ?>


                                <tr>


                                    <td>
                                        <?= $counter ?>
                                    </td>




                                    <td>


                                        <a href="admin.php?page=class-detail&class_id=<?= $classId ?>"
                                           class="text-decoration-none fw-semibold"
                                           style="color:var(--green-dark);">


                                            <?= htmlspecialchars($class['class_name']) ?>


                                        </a>


                                    </td>





                                    <td>

                                        <?= htmlspecialchars($class['description']) ?>

                                    </td>





                                    <td>

                                        <?= htmlspecialchars(
                                            $class['form_teacher_name'] ?? 'Unassigned'
                                        ) ?>


                                    </td>





                                    <td>


                                        <span class="badge"
                                              style="
                                              background:var(--green-dark);
                                              color:white;">
                                            
                                            <?= $studentCount ?>

                                        </span>


                                    </td>






                                    <td>


                                        <div class="d-flex gap-1">



                                            <!-- Assign Subjects -->

                                            <a href="admin.php?page=assign-subjects&class_id=<?= $classId ?>"
                                               class="btn btn-sm lg-btn-outline"
                                               title="Assign Subjects">

                                                <i class="fa fa-chalkboard-teacher"></i>

                                            </a>




                                            <!-- Add Students -->

                                            <a href="admin.php?page=add-student-form&class_id=<?= $classId ?>"
                                               class="btn btn-sm lg-btn-outline"
                                               title="Add Students">


                                                <i class="fa fa-user-plus"></i>


                                            </a>





                                            <!-- Edit -->

                                            <a href="admin.php?page=add-class-form&class_id=<?= $classId ?>"
                                               class="btn btn-sm lg-btn-edit"
                                               title="Edit Class">


                                                <i class="fa fa-edit"></i>


                                            </a>






                                            <!-- Delete -->

                                            <button class="btn btn-sm lg-btn-delete deleteClassBtn"
                                                    data-id="<?= $classId ?>"
                                                    title="Delete Class">


                                                <i class="fa fa-trash"></i>


                                            </button>



                                        </div>


                                    </td>



                                </tr>


                            <?php 
                                $counter++;
                                endforeach; 
                            ?>



                            </tbody>


                        </table>


                    </div>


                </div>


            </div>




        <?php endforeach; ?>



    <?php else: ?>


        <div class="text-center text-muted">

            No classes found.

        </div>


    <?php endif; ?>



</div>





<!-- DELETE FORM -->

<form id="deleteForm"
      method="POST"
      action="../controllers/class-controller.php"
      style="display:none;">


    <input type="hidden"
           name="class_id"
           id="deleteClassId">


    <input type="hidden"
           name="delete_class"
           value="1">


</form>





<script>


document.querySelectorAll('.deleteClassBtn')
.forEach(button => {


    button.addEventListener('click', function(){


        const classId = this.dataset.id;



        if(confirm(
            'Are you sure you want to delete this class and all related data?'
        )){


            document.getElementById('deleteClassId').value = classId;


            document.getElementById('deleteForm').submit();


        }


    });


});



</script>