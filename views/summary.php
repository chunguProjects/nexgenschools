<?php

// ============================
// DUMMY DATA
// ============================

$academicYears = [
    ['name'=>'2025/2026'],
    ['name'=>'2026/2027']
];

$exams = [
    ['exam_name'=>'Mid Term'],
    ['exam_name'=>'End of Term']
];

$classes = [
    ['name'=>'Form 2A'],
    ['name'=>'Form 3A']
];


$subjects = [
    'English',
    'Mathematics',
    'Biology',
    'Chemistry',
    'Agriculture'
];


$students = [

[
'position'=>1,
'id'=>'A4',
'name'=>'John Banda',
'marks'=>[
78,90,84,71,80
],
'points'=>16,
'result'=>'Pass'
],


[
'position'=>2,
'id'=>'A13',
'name'=>'Mercy Phiri',
'marks'=>[
75,88,80,70,76
],
'points'=>20,
'result'=>'Pass'
],


[
'position'=>3,
'id'=>'A15',
'name'=>'Peter Mhone',
'marks'=>[
60,65,70,58,66
],
'points'=>29,
'result'=>'Pass'
]

];


?>



<div class="container-fluid">


<!-- FILTERS -->

<div class="card shadow-sm mb-4">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">
<i class="fa-solid fa-chart-line me-2"></i>
Results Summary
</h5>

</div>


<div class="card-body">


<form class="row g-3">


<div class="col-md-4">

<label class="form-label">
Academic Year
</label>

<select class="form-select">

<option>Select Academic Year</option>

<?php foreach($academicYears as $year): ?>

<option>
<?= $year['name']; ?>
</option>

<?php endforeach; ?>

</select>

</div>



<div class="col-md-4">

<label class="form-label">
Exam
</label>

<select class="form-select">

<option>Select Exam</option>

<?php foreach($exams as $exam): ?>

<option>
<?= $exam['exam_name']; ?>
</option>

<?php endforeach; ?>

</select>

</div>




<div class="col-md-4">

<label class="form-label">
Class
</label>

<select class="form-select">

<option>Select Class</option>


<?php foreach($classes as $class): ?>

<option>
<?= $class['name']; ?>
</option>

<?php endforeach; ?>


</select>

</div>



<div class="col-12 text-end">

<button class="btn btn-primary">

<i class="fa fa-search me-1"></i>

Generate Summary

</button>


</div>


</form>


</div>


</div>






<!-- RESULTS AREA -->


<div class="card shadow-sm">



<div class="card-header text-center">


<h4 class="mb-1">
TERM THREE MID-TERM RESULTS
</h4>


<h5 class="mb-0">
2025/26 FORM THREE
</h5>


</div>





<div class="card-body">



<!-- EXPORT BUTTONS -->


<div class="text-end mb-3">


<button class="btn btn-danger btn-sm me-2">

<i class="fa fa-file-pdf"></i>

Download PDF

</button>



<button class="btn btn-success btn-sm">

<i class="fa fa-file-excel"></i>

Export Excel

</button>


</div>





<!-- TABLE -->


<div class="table-responsive">


<table class="table table-bordered table-sm align-middle">


<thead class="table-primary">


<tr>


<th>POS</th>

<th>STUDENT ID</th>

<th>STUDENT NAME</th>



<?php foreach($subjects as $subject): ?>

<th>
<?= $subject ?>
</th>

<?php endforeach; ?>



<th>Pts</th>

<th>Res</th>


</tr>


</thead>





<tbody>


<?php foreach($students as $student): ?>


<tr>


<td>
<?= $student['position']; ?>
</td>


<td>
<?= $student['id']; ?>
</td>



<td>
<?= $student['name']; ?>
</td>




<?php foreach($student['marks'] as $mark): ?>

<td>
<?= $mark; ?>
</td>

<?php endforeach; ?>



<td>
<?= $student['points']; ?>
</td>



<td>

<?php if($student['result']=="Pass"): ?>

<span class="badge bg-success">
Pass
</span>

<?php else: ?>

<span class="badge bg-danger">
Fail
</span>

<?php endif; ?>


</td>


</tr>



<?php endforeach; ?>



</tbody>


</table>


</div>







<!-- ANALYSIS SECTION -->


<div class="mt-4">


<h5 class="text-center">
SUMMARY
</h5>



<table class="table table-bordered text-center">


<thead class="table-secondary">


<tr>

<th></th>

<th>BOYS</th>

<th>GIRLS</th>

<th>TOTAL</th>


</tr>


</thead>



<tbody>


<tr>

<td>
ENTERED
</td>

<td>
14
</td>

<td>
7
</td>

<td>
21
</td>


</tr>



<tr>


<td>
PASSED
</td>


<td>
8
</td>


<td>
2
</td>


<td>
10
</td>


</tr>



<tr>

<td>
FAILED
</td>


<td>
6
</td>


<td>
5
</td>


<td>
11
</td>


</tr>



<tr>

<td>
PASS %
</td>

<td colspan="3">

48%

</td>


</tr>



</tbody>



</table>



</div>




</div>


</div>



</div>