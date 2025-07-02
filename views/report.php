<?php
ob_start(); // Start output buffering

require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');  // Include TCPDF library
include('../config/db.php');   // Database connection
include('../model/student.php'); // Student model
include('../model/class.php');   // Class model
include('../model/grades.php');  // Grades model
include('../model/exam.php');    // Exam model
include('../model/subject.php'); // Subject model

// Get POST data
$class_id = $_POST['class_id'] ?? null;
$term = $_POST['term'] ?? null;

if (!$class_id || !$term) {
    die('Class ID and Term are required');
}

// Fetch class details
$classModel = new ClassGroup($conn);
$classDetails = $classModel->getClassById($class_id);

// Fetch students in the selected class
$studentModel = new Student($conn);
$students = $studentModel->getStudentsByClass($class_id);

// Fetch exam details based on the term
$examModel = new Exams($conn);
$examDetails = $examModel->getExamByTerm($term);

// Fetch grades for the class and term
$gradesModel = new Grades($conn);
$grades = $gradesModel->getGradesByClassTerm($class_id, $term);

// Fetch subjects for the class
$subjectModel = new Subjects($conn);
$subjects = $subjectModel->getSubjectsByClass($class_id);

// Get class details
$gradingLevel = $classModel->determineGradingLevel($classDetails['name']);

// Get grading criteria from database
$gradingQuery = "SELECT * FROM grading_criteria WHERE level = ? ORDER BY min_marks DESC";
$gradingStmt = $conn->prepare($gradingQuery);
$gradingStmt->bind_param("s", $gradingLevel);
$gradingStmt->execute();
$gradingResult = $gradingStmt->get_result();
$gradingCriteria = $gradingResult->fetch_all(MYSQLI_ASSOC);

// Create a new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator('School System');
$pdf->SetTitle('School Report');

// ... (previous code remains the same)

// Loop through each student and generate their report
foreach ($students as $student) {
    // Determine if the student passed or failed
    $passFailStatus = getPassFailStatus($student['student_id'], $grades, $subjects);
    
    // Add a page for each student
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // HTML content for each student
    $html = '
    <style>
    body { font-size: 12px; }
    .report-container {
        border: 2px solid #000;
        padding: 10px 0; /* Remove side padding to align tables with edge */
        width: 670px;
    }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .header table { width: 100%; }
    .school-name { font-size: 18px; font-weight: bold; }
    .student-info { padding-bottom: 10px; }
    .student-info table { width: 100%; border-collapse: collapse; }
  
    .grades-table th, .grades-table td {
        border: 1px solid #000;
        padding: 2px;
        text-align: left;
    }
    .sno { width: 7%; font-weight: bold;}
    .subject { width: 27%; text-align: left !important; padding-left: 5px;font-weight: bold; }
    .marks { width: 8%; font-weight: bold;}
    .grade { width: 8%; font-weight: bold;}
    .remarks { width: 20%; padding-top: 10px; font-weight: bold;}
    .teacher { width: 30%; font-weight: bold; }
    .signature, .form-remarks, .grading-criteria {
        margin-left: 0;
        margin-right: 0;
        padding-left: 0;
        padding-right: 0;
    }
    .signature table,
    .form-remarks table,
    .grading-criteria table {
        width: 100%;
        border-collapse: collapse;
    }
</style>

    <div class="report-container">
        <div class="header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 20%; vertical-align: middle; text-align: center;">
                        <img src="' . realpath(__DIR__ . '/../images/logo.jpg') . '" style="width: 100px; height: 100px;">
                    </td>
                    <td style="width: 60%; text-align: center; vertical-align: middle;">
                        <p style="font-size: 18px; font-weight: bold; margin: 0;">CHUNGU FOUNDATION PRIVATE SECONDARY SCHOOL</p>
                        <p style="margin: 0;">P.O. Box 123, Limbe</p>
                        <p style="font-weight: bold; margin: 5px 0 0 0;">SCHOOL PROGRESS REPORT</p>
                    </td>
                    <td style="width: 20%;"></td>
                </tr>
            </table>
        </div>;

        <div class="student-info">
            <table style="border: none; width: 100%;">
                <tr>
                    <td style="border: none; text-align: left;"><strong>Student Name:</strong> ' . $student['first_name'] . ' ' . $student['last_name'] . '</td>
                    
                    <td style="border: none; text-align: center;" colspan="2"><strong>Academic Year:</strong> ' . date('Y') . '</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: left;"><strong>Term:</strong> ' . $term . '</td>
                     
                    <td style="border: none; text-align: center;" colspan="2"><strong>No of Students:</strong> ' . count($students) . '</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: left;"><strong>Class: </strong> ' . $classDetails['name'] . '</td>
                    <td style="border: none; text-align: center;"><strong>Sex: </strong> ' . $student['gender'] . '</td>
                    <td style="border: none; text-align: center;" colspan="2"><strong>Position in Class</strong>30</td>
                </tr>
            </table>
        </div>
    <table class="grades-table" style="width: 100%; border-collapse: collapse; margin-bottom:20px;">
            <tr>
                <th class="sno">S.No</th>
                <th class="subject">Subject</th>
                <th class="marks">Marks</th>
                <th class="grade">Grade</th>
                <th class="remarks">Remarks</th>
                <th class="teacher">Teacher</th>
            </tr>';

$subjectCount = 1; 
$totalMarks = 0;

for ($i = 0; $i < 15; $i++) {
    $remark = ''; // Reset remark
    $teacherName = ''; // Reset teacher name

    if (isset($subjects[$i])) {
        $subject = $subjects[$i];
        $subjectId = $subject['subject_id'];
        $subjectName = $subject['subject_name'];

        $studentGrade = $grades[$student['student_id']][$subjectId] ?? null;

        if ($studentGrade) {
            $marks = $studentGrade['marks'] ?? 0;
            $totalMarks += $marks;

            $gradeRemark = getGradeRemarkByMarks($marks, $gradingCriteria);
            $grade = $gradeRemark['grade'];
            $remark = $gradeRemark['remark'];

            $teacherName = $subject['teacher_name']; // Only assign when marks exist
        } else {
            $marks = '-';
            $grade = '-';
        }

    } else {
        // Empty row if no subject
        $subjectName = '';
        $marks = '';
        $grade = '';
        $remark = '';
        $teacherName = '';
    }

    $html .= "<tr>
                <td class='sno'>$subjectCount</td>
                <td class='subject'>$subjectName</td>
                <td class='marks'>$marks</td>
                <td class='grade'>$grade</td>
                <td class='remarks'>$remark</td>
                <td class='teacher'>$teacherName</td>
              </tr>";

    $subjectCount++;
}


// Total/Aggregate row
$html .= "<tr>
            <td class='marks'></td>
            <td colspan='2' style='text-align: right;'><strong>Aggregate</strong></td>
            <td><strong>$totalMarks</strong></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
   </table>
        <br><br>";


$html .= '
<table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
    <tr>
        <td colspan="2" style="border: 1px solid #000; padding: 5px; width: 20%; vertical-align: top;"><strong>Form Teacher Remarks</strong></td>
        <td colspan="2" style="border: 1px solid #000; padding: 5px; vertical-align: top; width: 80%;">is good student</td>
    </tr>
    <tr>
        <td colspan="2" style="border: 1px solid #000; padding: 5px; width: 20%; vertical-align: top;"><strong>Head Teacher Remarks</strong></td>
        <td colspan="2" style="border: 1px solid #000; padding: 5px; vertical-align: top; width: 80%;">has done well</td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px; width: 25%;"><strong>Closing Date</strong></td>
        <td style="border: 1px solid #000; padding: 5px; width: 25%;">20 June, 2025</td>
        <td style="border: 1px solid #000; padding: 5px; width: 25%;"><strong>Head Teacher\'s Name</strong></td>
        <td style="border: 1px solid #000; padding: 5px; width: 25%;"></td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;"><strong>Opening Date</strong></td>
        <td style="border: 1px solid #000; padding: 5px;">16 September, 2025</td>
        <td style="border: 1px solid #000; padding: 5px;"><strong>Head Teacher\'s Signature</strong></td>
        <td style="border: 1px solid #000; padding: 5px;"></td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px; vertical-align: top;"><strong>Grading Criteria</strong></td>
        <td colspan="3" style="border: 1px solid #000; padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>';

foreach ($gradingCriteria as $index => $criteria) {
    $isLast = $index === array_key_last($gradingCriteria);
    $html .= '
                    <td style="border-right: ' . ($isLast ? 'none' : '1px solid #000') . ';  
                                padding: 3px; 
                                font-size: 10px; 
                                text-align: center;">
                        ' . htmlspecialchars($criteria['grade']) . ' (' . 
                            htmlspecialchars($criteria['min_marks']) . '-' . 
                            htmlspecialchars($criteria['max_marks']) . ')
                    </td>';
}

$html .= '
                </tr>
            </table>
        </td>
    </tr>
</table>';


// Get current margins
$left = $pdf->getMargins()['left'];
$top = $pdf->getMargins()['top'];
$right = $pdf->getPageWidth() - $pdf->getMargins()['right'];
$bottom = $pdf->getPageHeight() - $pdf->getMargins()['bottom'];

// Draw a rectangle (x, y, width, height)
$pdf->Rect($left, $top, $right - $left, $bottom - $top, 'D');


    // Write student-specific HTML to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');
}

// Output the PDF to the browser
ob_end_clean(); // Clear any previous output to avoid conflicts
$pdf->Output('school_report.pdf', 'I');

// Helper function to check pass/fail status
function getPassFailStatus($student_id, $grades, $subjects)
{
    $passedSubjects = 0;
    $englishPassed = false;

    // Loop through subjects and grades for the student
    foreach ($subjects as $subject) {
        $subjectId = $subject['subject_id'];
        $studentGrade = $grades[$student_id][$subjectId] ?? null;

        // If the student has a grade, check if it's a passing grade
        if ($studentGrade) {
            $marks = $studentGrade['marks'];
            $grade = $studentGrade['grade'];

    
            // Check if the subject is English and if the student passed
            if (strtolower($subject['subject_name']) == 'english' && isPassingGrade($marks)) {
                $englishPassed = true;
            }

            // Increment passed subjects if the student passed
            if (isPassingGrade($marks)) {
                $passedSubjects++;
            }
        }
    }


    // Return pass/fail status
    if ($passedSubjects >= 6 && $englishPassed) {
        return 'Passed';
    } else {
        return 'Failed';
    }
}

// Helper function to check if a grade is passing (e.g., grade C or higher)
function isPassingGrade($marks)
{
    return $marks >= 40; // Assuming passing marks are 50 and above
}



// Function to determine the overall grade
function getOverallGrade($totalMarks, $gradingCriteria) {
    foreach ($gradingCriteria as $criteria) {
        if ($totalMarks >= $criteria['min_marks'] && $totalMarks <= $criteria['max_marks']) {
            return $criteria['grade'];
        }
    }
    return 'N/A'; // Default if no match is found
}
function getGradeRemarkByMarks($marks, $gradingCriteria) {
    foreach ($gradingCriteria as $criteria) {
        if ($marks >= $criteria['min_marks'] && $marks <= $criteria['max_marks']) {
            return [
                'grade' => $criteria['grade'],
                'remark' => $criteria['description']
            ];
        }
    }
    return ['grade' => '-', 'remark' => ''];  // default if no match
}
?>
