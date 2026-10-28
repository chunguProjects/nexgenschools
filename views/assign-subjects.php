<?php
require_once('../config/db.php');
require_once('../model/classSubject.php');
require_once('../model/user.php');
require_once('../model/classSubjectTeacher.php');
require_once('../model/academic_year.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$classId = intval($_GET['class_id'] ?? 0);

$ayModel = new AcademicYear($conn);
$currentYear = $ayModel->getCurrentYear();
$currentYearId = intval($currentYear['academic_year_id'] ?? 0);

$classSubjModel = new ClassSubject($conn);
$cstModel = new ClassSubjectTeacher($conn);
$userModel = new User($conn);


// Get class info
$stmt = $conn->prepare("
    SELECT academic_year_id, name 
    FROM classes 
    WHERE class_id=? 
    LIMIT 1
");

$stmt->bind_param("i",$classId);
$stmt->execute();

$classInfo = $stmt->get_result()->fetch_assoc();


$academicYearId = intval($classInfo['academic_year_id'] ?? 0);
$academicYearName = $classInfo['name'] ?? 'N/A';


$isEditable = ($academicYearId === $currentYearId);


// Data
$allSubjects = $classSubjModel->getAllSubjects();

$classSubjects = 
$classSubjModel->getByClass($classId,$academicYearId);


$classSubjectsIds = array_column(
    $classSubjects,
    'subject_id'
);


$teachers = $userModel->getUsersByRoleId(2);


$existingAssignments=[];

foreach(
    $cstModel->getByClass($classId,$academicYearId) 
    as $assign
){

    $existingAssignments[
        $assign['class_subject_id']
    ]=$assign['teacher_id'];

}

?>


<div class="container-fluid py-3">


<!-- PAGE HEADER -->
<div class="lg-page-header">

    <a href="admin.php?page=class"
       class="lg-btn-back">

        <i class="fa fa-arrow-left"></i>
        Back

    </a>


    <h4 class="lg-page-title mb-0">
        Manage Class Subjects & Teachers
    </h4>


    <div></div>

</div>



<!-- FLASH -->
<?php if(!empty($_SESSION['success'])): ?>

<div class="alert alert-success">

<?= $_SESSION['success']; unset($_SESSION['success']); ?>

</div>

<?php endif; ?>


<?php if(!empty($_SESSION['error'])): ?>

<div class="alert alert-danger">

<?= $_SESSION['error']; unset($_SESSION['error']); ?>

</div>

<?php endif; ?>



<!-- WARNING -->

<?php if(!$isEditable): ?>

<div class="alert alert-warning">

<strong>Note:</strong>

Academic year 
<?= htmlspecialchars($academicYearName) ?>

is not active.

Editing disabled.

</div>

<?php endif; ?>





<!-- TABS -->

<ul class="nav nav-tabs ledger-tabs">

<li class="nav-item">

<button class="nav-link active"
data-bs-toggle="tab"
data-bs-target="#subjects-tab-pane">

Assign Subjects

</button>

</li>


<li class="nav-item">

<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#teachers-tab-pane">

Assign Teachers

</button>

</li>


</ul>





<div class="tab-content mt-3">



<!-- SUBJECT TAB -->

<div class="tab-pane fade show active"
id="subjects-tab-pane">


<div class="card">

<div class="card-header lg-card-header">

<h5 class="mb-0">
Subjects
</h5>

</div>


<div class="card-body">


<form method="POST"
action="../controllers/class-subject-controller.php">


<input type="hidden"
name="class_id"
value="<?= $classId ?>">


<input type="hidden"
name="academic_year_id"
value="<?= $academicYearId ?>">



<table class="table ledger-table">


<thead>

<tr>

<th>Subject</th>

<th class="text-center">
Assign
</th>

</tr>

</thead>



<tbody>


<?php foreach($allSubjects as $sub): ?>

<tr>


<td>

<?= htmlspecialchars($sub['subject_name']) ?>

</td>


<td class="text-center">


<input type="checkbox"

name="subjects[]"

value="<?= $sub['subject_id'] ?>"

<?= in_array(
$sub['subject_id'],
$classSubjectsIds
) ? 'checked':'' ?>

<?= !$isEditable?'disabled':'' ?>

>


</td>


</tr>


<?php endforeach; ?>


</tbody>


</table>



<?php if($isEditable): ?>

<button class="lg-btn-add"
type="submit"
name="save_class_subjects">

Save Subjects

</button>


<?php else: ?>

<p class="text-muted">
Cannot edit inactive academic year.
</p>


<?php endif; ?>


</form>


</div>

</div>


</div>





<!-- TEACHER TAB -->

<div class="tab-pane fade"
id="teachers-tab-pane">



<div class="card">

<div class="card-header lg-card-header">

<h5 class="mb-0">
Teacher Assignment
</h5>

</div>



<div class="card-body">


<form method="POST"
action="../controllers/class-subject-teacher-controller.php">


<input type="hidden"
name="class_id"
value="<?= $classId ?>">


<input type="hidden"
name="academic_year_id"
value="<?= $academicYearId ?>">



<table class="table ledger-table">


<thead>

<tr>

<th>
Subject
</th>


<th>
Teacher
</th>


</tr>


</thead>



<tbody>


<?php foreach($classSubjects as $cs): ?>


<tr>


<td>

<?= htmlspecialchars($cs['subject_name']) ?>

</td>


<td>


<select

name="assignments[<?= $cs['class_subject_id'] ?>]"

class="form-select"

<?= !$isEditable?'disabled':'' ?>

>


<option value="">
-- Select Teacher --
</option>



<?php foreach($teachers as $t): ?>


<option

value="<?= $t['user_id'] ?>"

<?= 
(
isset($existingAssignments[$cs['class_subject_id']])
&&
$existingAssignments[$cs['class_subject_id']]
==
$t['user_id']
)

?'selected':''
?>


>

<?= htmlspecialchars($t['full_name']) ?>


</option>


<?php endforeach; ?>


</select>


</td>


</tr>


<?php endforeach; ?>


</tbody>


</table>



<?php if($isEditable): ?>

<button class="lg-btn-add"
type="submit"
name="save_assignments">

Save Assignments

</button>


<?php else: ?>


<p class="text-muted">
Cannot assign teachers for inactive academic year.
</p>


<?php endif; ?>


</form>


</div>


</div>


</div>



</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>