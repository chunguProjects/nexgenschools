<?php
ob_start();
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/../service/report-service.php'; // adjust path if needed
require_once __DIR__ . '/../utils/grades-utils.php';     // grade helper functions
include __DIR__ . '/../config/db.php';

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

// Resolve the logo once — same school logo is reused on every student's
// page in this class report, so no need to look it up per iteration.
// Drawn directly onto the PDF canvas via $pdf->Image() rather than through
// an <img> tag in the HTML, since writeHTML()'s image handling doesn't
// reliably resolve filesystem paths.
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

// Initialize PDF
$pdf = new TCPDF();
$pdf->SetCreator('School System');
$pdf->SetAuthor('School System');
$pdf->SetTitle('Class Report');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

// Center the logo horizontally: (full page width - logo width) / 2.
$logoX = ($pdf->getPageWidth() - $logoWidth) / 2;

// Add each student's report as a page
foreach ($htmlReports as $html) {
    $pdf->AddPage();
    if ($hasLogo) {
        $pdf->Image($logoFullPath, $logoX, $logoY, $logoWidth, $logoHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
    }
    $pdf->SetFont('helvetica', '', 11);
    $pdf->writeHTML($html, true, false, true, false, '');
}

// Output PDF to browser
ob_end_clean();
$pdf->Output('class_report.pdf', 'I');