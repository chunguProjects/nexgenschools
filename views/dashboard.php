<?php
include('../config/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= ACTIVE ACADEMIC YEAR ================= */
$activeYear = $conn->query("
    SELECT academic_year_id, name 
    FROM academic_years 
    WHERE is_current = 1 
    LIMIT 1
")->fetch_assoc();

/* ================= STATS ================= */

// Students
$totalStudents = $conn->query("
    SELECT COUNT(*) as total 
    FROM students 
    WHERE is_deleted = 0
")->fetch_assoc()['total'];

// Teachers
$totalTeachers = $conn->query("
    SELECT COUNT(*) as total 
    FROM users 
    WHERE role_id = 2
")->fetch_assoc()['total'];

// Classes (current year)
$totalClasses = $conn->query("
    SELECT COUNT(*) as total 
    FROM classes 
    WHERE academic_year_id = '".($activeYear['academic_year_id'] ?? 0)."'
")->fetch_assoc()['total'];

/* ================= TEACHER WORKLOAD ================= */
$work = $conn->query("
    SELECT u.full_name, COUNT(cst.class_subject_id) as total_subjects
    FROM users u
    LEFT JOIN class_subject_teacher cst 
        ON u.user_id = cst.teacher_id
    WHERE u.role_id = 2
    GROUP BY u.user_id, u.full_name
    ORDER BY total_subjects DESC
");

$tNames = [];
$tLoads = [];

while ($r = $work->fetch_assoc()) {
    $tNames[] = $r['full_name'];
    $tLoads[] = $r['total_subjects'];
}

/* ================= NOTIFICATIONS ================= */
$unassignedStudents = $conn->query("
    SELECT COUNT(*) as total 
    FROM students 
    WHERE class_id IS NULL AND is_deleted = 0
")->fetch_assoc()['total'];

$unassignedTeachers = $conn->query("
    SELECT COUNT(*) as total 
    FROM users u
    WHERE role_id = 2
    AND NOT EXISTS (
        SELECT 1 FROM class_subject_teacher cst 
        WHERE cst.teacher_id = u.user_id
    )
")->fetch_assoc()['total'];
?>

<!-- ================= DASHBOARD UI ================= -->
<main class="content px-3 py-3">

<div class="container-fluid">

<!-- ACTIVE YEAR -->
<div class="alert alert-primary">
    Active Academic Year: 
    <strong><?= $activeYear['name'] ?? 'Not Set' ?></strong>
</div>

<!-- ================= STATS ================= -->
<div class="row mb-4">

<div class="col-md-4">
<div class="card shadow-sm">
<div class="card-body text-center">
<h5>Students</h5>
<h2><?= $totalStudents ?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm">
<div class="card-body text-center">
<h5>Teachers</h5>
<h2><?= $totalTeachers ?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm">
<div class="card-body text-center">
<h5>Classes</h5>
<h2><?= $totalClasses ?></h2>
</div>
</div>
</div>

</div>

<!-- ================= TEACHER WORKLOAD + ALERTS ================= -->
<div class="row mb-4">

<!-- Teacher Workload -->
<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-header">Teacher Workload</div>
<div class="card-body">
<canvas id="teacherChart"></canvas>
</div>
</div>
</div>

<!-- Alerts -->
<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-header">System Alerts</div>
<div class="card-body">

<ul class="list-group">

<li class="list-group-item text-warning">
Unassigned Students: <?= $unassignedStudents ?>
</li>

<li class="list-group-item text-danger">
Teachers without subjects: <?= $unassignedTeachers ?>
</li>

<li class="list-group-item text-info">
System Status: OK
</li>

</ul>

</div>
</div>
</div>

</div>

</div>
</main>

<!-- ================= CHART JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Teacher Workload Chart
new Chart(document.getElementById('teacherChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($tNames) ?>,
        datasets: [{
            label: 'Subjects',
            data: <?= json_encode($tLoads) ?>
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 30
                }
            },
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>