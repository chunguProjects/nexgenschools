<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/../service/report-service.php';
require_once __DIR__ . '/../utils/grades-utils.php';
include __DIR__ . '/../config/db.php';

$classId   = $_POST['class_id'] ?? $_GET['class_id'] ?? null;
$term      = $_POST['term'] ?? $_GET['term'] ?? null;
$examId    = $_POST['exam_id'] ?? $_GET['exam_id'] ?? null;
$studentId = $_POST['student_id'] ?? $_GET['student_id'] ?? null;

if (!$classId || !$term || !$examId || !$studentId) {
    die('Missing required parameters: class ID, term, exam ID, or student ID.');
}

$reportService = new StudentReportService($conn);
$html = $reportService->generateSingleReport($classId, $term, $examId, $studentId);

if ($html === null) {
    die('This student was not found in the selected class for this term/exam.');
}

// Resolve the logo path once, same way report.php does
require_once __DIR__ . '/../model/GeneralSettingsModel.php';
$generalSettingsModel = new GeneralSettingsModel($conn);
$generalSettings = $generalSettingsModel->getSettings();
$logoPath = $generalSettings['logo_path'] ?? null;
$logoFullPath = $logoPath ? __DIR__ . '/../' . ltrim($logoPath, '/') : null;
$hasLogo = $logoFullPath && file_exists($logoFullPath);

// Logo dimensions (mm) — kept as variables so the centering math below
// and the draw call always agree, even if you resize the logo later.
$logoWidth = 22;
$logoHeight = 22;
$logoY = 10;

$pdf = new TCPDF();
$pdf->SetCreator('School System');
$pdf->SetAuthor('School System');
$pdf->SetTitle('Student Report');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

// Center the logo horizontally: (full page width - logo width) / 2.
$logoX = ($pdf->getPageWidth() - $logoWidth) / 2;

$pdf->AddPage();

// Draw the logo directly onto the PDF canvas, centered horizontally.
// Width/height in mm — TCPDF scales the source image to fit, keeping
// aspect ratio when only one of w/h is given.
if ($hasLogo) {
    $pdf->Image($logoFullPath, $logoX, $logoY, $logoWidth, $logoHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
}

$pdf->SetFont('helvetica', '', 11);
$pdf->writeHTML($html, true, false, true, false, '');

ob_end_clean();
$pdf->Output('student_report.pdf', 'I');