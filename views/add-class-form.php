<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/class.php');

$message = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;

unset($_SESSION['success'], $_SESSION['error']);


// Editing
$isEdit = false;

$classId = $_GET['class_id'] ?? null;

$classData = [
    'name'              => '',
    'description'       => '',
    'form_teacher_id'   => '',
    'academic_year_id'  => ''
];


$classModel = new ClassGroup($conn);


if ($classId) {

    $isEdit = true;

    $class = $classModel->getClassById($classId);


    if ($class) {

        $classData = [
            'name'              => $class['name'],
            'description'       => $class['description'],
            'form_teacher_id'   => $class['form_teacher_id'],
            'academic_year_id'  => $class['academic_year_id']
        ];

    } else {

        $error = "Class not found.";
        $isEdit = false;

    }
}



// Teachers
$teachers = $conn->query("
    SELECT u.user_id, u.full_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.role_id
    WHERE r.name = 'teacher'
");


// Academic years
$academicYears = $conn->query("
    SELECT academic_year_id, name 
    FROM academic_years 
    ORDER BY start_date DESC
");

?>


<div class="container-fluid py-3">


    <!-- PAGE HEADER -->
    <div class="lg-page-header">


        <a href="admin.php?page=class"
           class="lg-btn-back text-decoration-none">

            <i class="fa fa-arrow-left"></i>
            Back

        </a>



        <h4 class="lg-page-title mb-0">

            <?= $isEdit ? 'Edit Class' : 'Add New Class' ?>

        </h4>



        <div></div>


    </div>




    <!-- FLASH MESSAGE -->

    <?php if ($message): ?>

        <div class="alert alert-success" id="flash-message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php elseif ($error): ?>

        <div class="alert alert-danger" id="flash-message">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>





    <!-- FORM CARD -->

    <div class="lg-form-card">


        <h4>
            <?= $isEdit ? "Update Class Details" : "Class Information" ?>
        </h4>


        <div class="lg-form-divider"></div>





        <form action="../controllers/class-controller.php" method="POST">


            <?php if ($isEdit): ?>

                <input type="hidden"
                       name="class_id"
                       value="<?= htmlspecialchars($classId) ?>">

            <?php endif; ?>




            <div class="row">


                <!-- Class Name -->

                <div class="col-md-6 mb-3">

                    <label for="name">
                        Class Name
                    </label>


                    <input type="text"
                           class="form-control"
                           id="name"
                           name="name"
                           placeholder="e.g. Form 1A"
                           value="<?= htmlspecialchars($classData['name']) ?>"
                           required>

                </div>




                <!-- Description -->

                <div class="col-md-6 mb-3">


                    <label for="description">
                        Description
                    </label>


                    <input type="text"
                           class="form-control"
                           id="description"
                           name="description"
                           placeholder="e.g. Science Stream"
                           value="<?= htmlspecialchars($classData['description']) ?>"
                           required>


                </div>


            </div>





            <div class="row">


                <!-- Academic Year -->

                <div class="col-md-6 mb-3">


                    <label for="academic_year_id">
                        Academic Year
                    </label>


                    <select class="form-select"
                            id="academic_year_id"
                            name="academic_year_id"
                            required>


                        <option value="" disabled>
                            Select Academic Year
                        </option>



                        <?php if ($academicYears->num_rows > 0): ?>


                            <?php while ($year = $academicYears->fetch_assoc()): ?>


                                <option value="<?= $year['academic_year_id'] ?>"
                                    
                                    <?= ($year['academic_year_id'] == $classData['academic_year_id']) 
                                        ? 'selected' 
                                        : '' ?>>

                                    <?= htmlspecialchars($year['name']) ?>

                                </option>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <option disabled>
                                No academic years found
                            </option>


                        <?php endif; ?>


                    </select>


                </div>






                <!-- Class Teacher -->

                <div class="col-md-6 mb-3">


                    <label for="teacher_id">
                        Class Teacher
                    </label>


                    <select class="form-select"
                            id="teacher_id"
                            name="teacher_id"
                            required>


                        <option value="" disabled>
                            Select Teacher
                        </option>



                        <?php if ($teachers->num_rows > 0): ?>


                            <?php while ($teacher = $teachers->fetch_assoc()): ?>


                                <option value="<?= $teacher['user_id'] ?>"
                                    
                                    <?= ($teacher['user_id'] == $classData['form_teacher_id']) 
                                        ? 'selected' 
                                        : '' ?>>

                                    <?= htmlspecialchars($teacher['full_name']) ?>

                                </option>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <option disabled>
                                No teachers found
                            </option>


                        <?php endif; ?>


                    </select>


                </div>


            </div>






            <!-- BUTTONS -->

            <div class="d-flex gap-2 mt-3">


                <button type="submit"
                        name="<?= $isEdit ? 'update_class' : 'add_class' ?>"
                        class="lg-btn-add">


                    <i class="fa fa-save"></i>

                    <?= $isEdit ? 'Update Class' : 'Save Class' ?>


                </button>





                <a href="admin.php?page=class"
                   class="lg-btn-back text-decoration-none">


                    Cancel


                </a>



            </div>



        </form>


    </div>


</div>



<script>

const flash = document.getElementById('flash-message');

if(flash){

    setTimeout(()=>{

        flash.style.transition="opacity .5s";
        flash.style.opacity="0";

        setTimeout(()=>flash.remove(),500);

    },3000);

}

</script>