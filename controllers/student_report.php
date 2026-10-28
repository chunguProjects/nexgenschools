<?php 
ob_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('../service/report-service.php');
require_once('../utils/grades-utils.php');
include('../config/db.php');

$classId   = $_POST['class_id'] ?? $_GET['class_id'] ?? null;
$term      = $_POST['term'] ?? $_GET['term'] ?? null;
$examId    = $_POST['exam_id'] ?? $_GET['exam_id'] ?? null;
$studentId = $_POST['student_id'] ?? $_GET['student_id'] ?? null;

if (!$classId || !$term || !$examId || !$studentId) {
    die('Missing required parameters: class ID, term, exam ID, or student ID.');
}

$reportService = new StudentReportService($conn);
$html = $reportService->generateSingleReport($classId, $term, $examId, $studentId);

if (!$html) {
    die('Student not found in this class/exam.');
}

$pdf = new TCPDF();
$pdf->SetCreator('School System');
$pdf->SetAuthor('School System');
$pdf->SetTitle('Student Report');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);
$pdf->writeHTML($html, true, false, true, false, '');

ob_end_clean();
$pdf->Output('student_report.pdf', 'I');