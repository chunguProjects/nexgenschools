<?php
// Include your DB config and autoload or required files
require_once('../config/db.php');
require_once('../service/report-service.php');
require_once('../utils/grades-utils.php');
include('../model/subject.php');

// Hardcode IDs and term for testing (replace with real values from your DB)
$schoolId = 1;      // Your actual school ID
$classId = 4;       // Your actual class ID
// $term = 'Term 3';   // Actual term



// $service = new StudentReportService($conn, $schoolId);

// // Generate reports (array of HTML strings for all students in class)
// $reports = $service->generateReports($classId, $term);

// // Render all reports one by one
// foreach ($reports as $htmlReport) {
//     echo $htmlReport;
//     echo "<hr style='margin:40px 0;'>";
// }
$subjectModel = new Subjects($conn);
$classSubjects = $subjectModel->getSubjectsByClass($classId);
echo "<pre>";
print_r($classSubjects);
echo "</pre>";
?>