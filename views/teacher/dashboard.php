<?php
include('../config/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$teacherId = $_SESSION['user']['user_id'];

/* ================= STATS ================= */

// Total classes teacher is involved in
$totalClasses = $conn->query("
    SELECT COUNT(DISTINCT cs.class_id) as total
    FROM class_subject_teacher cst
    INNER JOIN class_subject cs ON cst.class_subject_id = cs.id
    WHERE cst.teacher_id = $teacherId
")->fetch_assoc()['total'] ?? 0;

// Total subjects teacher handles
$totalSubjects = $conn->query("
    SELECT COUNT(*) as total
    FROM class_subject_teacher
    WHERE teacher_id = $teacherId
")->fetch_assoc()['total'] ?? 0;

// Total students taught by teacher
$totalStudents = $conn->query("
    SELECT COUNT(DISTINCT se.student_id) as total
    FROM class_subject_teacher cst
    INNER JOIN class_subject cs ON cst.class_subject_id = cs.id
    INNER JOIN student_enrollments se ON cs.class_id = se.class_id
    INNER JOIN students s ON se.student_id = s.student_id
    WHERE cst.teacher_id = $teacherId
    AND se.status = 'active'
    AND s.is_deleted = 0
")->fetch_assoc()['total'] ?? 0;
?>

<!-- ================= DASHBOARD UI ================= -->
<div class="container py-4">

    <!-- Welcome -->
    <h2 class="fw-bold mb-2">
        Welcome, <?= htmlspecialchars($_SESSION['user']['full_name']) ?> 👋
    </h2>
    <p class="text-muted">
        Manage your classes, subjects, and student grades from here.
    </p>

    <!-- ================= STATS ================= -->
    <div class="row mt-4">

        <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
                <h6 class="text-muted">My Classes</h6>
                <h2><?= $totalClasses ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
                <h6 class="text-muted">Subjects</h6>
                <h2><?= $totalSubjects ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
                <h6 class="text-muted">Students</h6>
                <h2><?= $totalStudents ?></h2>
            </div>
        </div>

    </div>

    <!-- ================= ACTION CARDS ================= -->
    <!-- <div class="row mt-5">

        <!-- My Classes -->
        <!--<div class="col-md-4">
            <a href="teacher.php?page=my-classes" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 text-center p-4 hover-card">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>My Classes</h5>
                    <p class="text-muted">View all classes you're teaching.</p>
                </div>
            </a>
        </div>

        <!-- Enter Grades -->
        <!-- <div class="col-md-4">
            <a href="teacher.php?page=enter-grades" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 text-center p-4 hover-card">
                    <i class="fas fa-pen-nib fa-3x text-success mb-3"></i>
                    <h5>Enter Grades</h5>
                    <p class="text-muted">Submit grades for your students.</p>
                </div>
            </a>
        </div>

    </div> -->

</div>

<!-- ================= STYLES ================= -->
<style>
.hover-card {
    transition: 0.2s ease-in-out;
}
.hover-card:hover {
    transform: translateY(-5px);
}
</style>