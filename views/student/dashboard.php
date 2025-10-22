<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure student access
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'Student') {
    header("Location: ../views/student-portal.php");
    exit;
}

require_once('../config/db.php');
require_once('../model/grades.php');
include(__DIR__ . '/../../utils/grades-utils.php'); // grading + remark functions

$gradesModel = new Grades($conn);

// Logged-in student info
$studentId = $_SESSION['user']['student_id'];
$classId   = $_SESSION['user']['class_id'];
$className = $_SESSION['user']['class_name'];
$level     = $_SESSION['user']['level'] ?? '';

// Fetch academic years
$academicYears = $conn->query("SELECT academic_year_id, name FROM academic_years ORDER BY name DESC")->fetch_all(MYSQLI_ASSOC);

// Filters
$selectedYear   = $_GET['year'] ?? null;
$selectedTerm   = $_GET['term'] ?? null;
$selectedExamId = $_GET['exam_id'] ?? null;

// Fetch terms
$terms = $conn->query("SELECT DISTINCT term FROM exams")->fetch_all(MYSQLI_ASSOC);

// Fetch exams
$exams = [];
if ($selectedYear && $selectedTerm) {
    $stmt = $conn->prepare("SELECT id, exam_name FROM exams WHERE academic_year_id = ? AND term = ?");
    $stmt->bind_param("is", $selectedYear, $selectedTerm);
    $stmt->execute();
    $exams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch subjects with teacher info
$subjects = [];
$subjectStmt = $conn->prepare("
    SELECT s.subject_id, s.subject_name, u.full_name AS teacher_name
    FROM subjects s
    INNER JOIN class_subjects cs ON s.subject_id = cs.subject_id
    LEFT JOIN users u ON s.teacher_id = u.user_id
    WHERE cs.class_id = ?
");
$subjectStmt->bind_param("i", $classId);
$subjectStmt->execute();
$subjectResult = $subjectStmt->get_result();
while ($row = $subjectResult->fetch_assoc()) {
    $subjects[$row['subject_id']] = [
        'name' => $row['subject_name'],
        'teacher' => $row['teacher_name'] ?? 'N/A'
    ];
}

// Fetch grades
$gradesMap = [];
if ($selectedExamId) {
    $gradesMap = $gradesModel->getGradesMap($selectedExamId, $classId);
}

// Helper for grade + remark
$calculateGradeWithRemark = fn($marks) => $gradesModel->calculateGradeWithRemark($marks, $className);

// Optional: fetch school settings
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
$logoPath = $settings['logo_path'] ?? null;
$logoUrl = $logoPath ? '/nexgenschools/' . ltrim($logoPath, '/') : null;
$logoFullPath = $logoPath ? __DIR__ . '/../' . ltrim($logoPath, '/') : null;

// Grading criteria
$gradingCriteria = $conn->query("SELECT * FROM grading_criteria ORDER BY min_marks DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Results</title>
<link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-size: 12px; }
.report-container { border: 2px solid #000; padding: 10px; width: 100%; max-width: 700px; margin: auto; }
.header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px; }
.header-logo img { width: 50px; height: 50px; object-fit: contain; margin-bottom: 5px; }
.school-name { font-size: 16px; font-weight: bold; margin: 2px 0; }
.school-meta { font-size: 11px; margin: 1px 0; }
.report-title { font-weight: bold; font-size: 13px; margin: 3px 0 5px 0; text-transform: uppercase; }
.grades-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.grades-table th, .grades-table td { border: 1px solid #000; padding: 4px; text-align: left; }
.grades-table th { background-color: #f5f5f5; }
</style>
</head>
<body class="bg-light">

<div class="container my-4">

<!-- Filters -->
<div class="card mb-3 shadow-sm">
<div class="card-body">
<form method="GET" action="">
<div class="row g-2 align-items-end">
<div class="col-md-3">
<label for="year" class="form-label">Academic Year</label>
<select name="year" id="year" class="form-select" onchange="this.form.submit()">
<option value="">-- Select Year --</option>
<?php foreach($academicYears as $year): ?>
<option value="<?= $year['academic_year_id'] ?>" <?= ($selectedYear == $year['academic_year_id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($year['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label for="term" class="form-label">Term</label>
<select name="term" id="term" class="form-select" onchange="this.form.submit()">
<option value="">-- Select Term --</option>
<?php foreach($terms as $term): ?>
<option value="<?= htmlspecialchars($term['term']) ?>" <?= ($selectedTerm == $term['term']) ? 'selected' : '' ?>>
<?= htmlspecialchars($term['term']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label for="exam_id" class="form-label">Exam</label>
<select name="exam_id" id="exam_id" class="form-select" onchange="this.form.submit()">
<option value="">-- Select Exam --</option>
<?php foreach($exams as $exam): ?>
<option value="<?= $exam['id'] ?>" <?= ($selectedExamId == $exam['id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($exam['exam_name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3 d-grid">
<button type="submit" class="btn btn-primary">View Results</button>
</div>
</div>
</form>
</div>
</div>

<!-- Report Container -->
<div class="report-container shadow-sm bg-white p-3">

<!-- Header -->
<div class="header">
<?php if ($logoFullPath && file_exists($logoFullPath)): ?>
<img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo">
<?php endif; ?>
<p class="school-name"><?= htmlspecialchars($settings['school_name'] ?? 'SCHOOL NAME') ?></p>
<p class="school-meta">
<?= htmlspecialchars($settings['address'] ?? '') ?><br>
Email: <?= htmlspecialchars($settings['contact_email'] ?? '') ?><br>
Phone: <?= htmlspecialchars($settings['contact_phone'] ?? '') ?>
</p>
<p class="report-title">School Progress Report</p>
</div>

<!-- Student Info -->
<table class="table table-borderless">
<tr>
<td><strong>Student Name:</strong> <?= htmlspecialchars($_SESSION['user']['full_name']) ?></td>
<td><strong>Class:</strong> <?= htmlspecialchars($className) ?></td>
<td><strong>Term:</strong> <?= htmlspecialchars($selectedTerm ?? '-') ?></td>
</tr>
<tr>
<td><strong>Exam:</strong> <?= ($selectedExamId && !empty($exams)) ? htmlspecialchars($exams[array_search($selectedExamId,array_column($exams,'id'))]['exam_name']) : '-' ?></td>
<td><strong>Academic Year:</strong> <?= ($selectedYear) ? htmlspecialchars(array_column($academicYears,'name','academic_year_id')[$selectedYear]) : '-' ?></td>
<td></td>
</tr>
</table>

<!-- Grades Table -->
<table class="grades-table">
<thead>
<tr>
<th>Subject</th>
<th>Score</th>
<th>Grade</th>
<th>Teacher Comments</th>
<th>Teacher</th>
</tr>
</thead>
<tbody>
<?php
$totalMarks = 0;
$gradedCount = 0;
foreach ($subjects as $subjectId => $subject):
$marks = $gradesMap[$studentId][$subjectId] ?? null;
if ($marks !== null) {
    $gradeData = $calculateGradeWithRemark($marks);
    $grade = $gradeData['grade'];
    $remark = $gradeData['remark'];
    $totalMarks += $marks;
    $gradedCount++;
} else {
    $marks = $grade = $remark = '-';
}
?>
<tr>
<td><?= htmlspecialchars($subject['name']) ?></td>
<td><?= htmlspecialchars($marks) ?></td>
<td><?= htmlspecialchars($grade) ?></td>
<td><?= htmlspecialchars($remark) ?></td>
<td><?= htmlspecialchars($subject['teacher']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
