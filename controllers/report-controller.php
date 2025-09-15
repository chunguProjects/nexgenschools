<?php 
ob_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('../service/report-service.php'); // adjust path if needed
require_once('../utils/grades-utils.php');     // grade helper functions
include('../config/db.php');

// Get POST or GET data
$classId = $_POST['class_id'] ?? $_GET['class_id'] ?? null;
$term    = $_POST['term'] ?? $_GET['term'] ?? null;
$examId  = $_POST['exam_id'] ?? $_GET['exam_id'] ?? null;

if (!$classId || !$term || !$examId) {
    die('Missing required parameters: class ID, term, or exam ID.');
}

// Initialize service (single-tenant, no schoolId needed)
$reportService = new StudentReportService($conn);

// Get HTML reports for each student
$htmlReports = $reportService->generateReports($classId, $term, $examId);

// Initialize PDF
$pdf = new TCPDF();
$pdf->SetCreator('School System');
$pdf->SetAuthor('School System');
$pdf->SetTitle('Class Report');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

// Add each student's report as a page
foreach ($htmlReports as $html) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);
    $pdf->writeHTML($html, true, false, true, false, '');
}

// Output PDF to browser
ob_end_clean();
$pdf->Output('class_report.pdf', 'I');
