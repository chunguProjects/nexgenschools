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

// Fetch grading criteria based on the class level
$classLevel = intval(substr($classDetails['name'], 0, 1));
$gradingLevel = in_array($classLevel, [1, 2]) ? 'Junior' : 'Senior';

$gradingCriteriaQuery = "SELECT grade, min_marks, max_marks FROM grading_criteria WHERE level = ?";
$gradingStmt = $conn->prepare($gradingCriteriaQuery);
$gradingStmt->bind_param("s", $gradingLevel);
$gradingStmt->execute();
$gradingResult = $gradingStmt->get_result();
$gradingCriteria = $gradingResult->fetch_all(MYSQLI_ASSOC);

// Create a new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator('School System');
$pdf->SetTitle('School Report');

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
    <div class="report-container">
        <div class="header">
            <h1>Next Gen School</h1>
            <p>Address: 123 School Lane, Cityville</p>
            <p>Contact: +123 456 7890 | Email: info@nextgenschool.com</p>
        </div>
        <div class="student-info">
            <p><strong>Class:</strong> ' . $classDetails['name'] . '</p>
            <p><strong>Exam:</strong> ' . $examDetails['exam_name'] . ' - ' . $term . '</p>
            <p><strong>Student Name:</strong> ' . $student['first_name'] . ' ' . $student['last_name'] . '</p>
            <p><strong>Position in Class:</strong> 3/30</p>
            <p><strong>Status:</strong> ' . $passFailStatus . '</p>
        </div>
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Grade</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>';


   // Initialize total marks
$totalMarks = 0;

// Loop through subjects and grades
foreach ($subjects as $subject) {
    $subjectId = $subject['subject_id'];

    // Safely get the grade for the student and subject, or default to "N/A"
    $studentGrade = $grades[$student['student_id']][$subjectId] ?? null;

    if ($studentGrade) {
        $marks = $studentGrade['marks'] ?? 0;
        $grade = $studentGrade['grade'] ?? 'N/A';
        $totalMarks += $marks; // Add to total marks
    } else {
        $marks = 'N/A'; 
        $grade = 'N/A';
    }

    $html .= "<tr>
                <td>{$subject['subject_name']}</td>
                <td>$marks</td>
                <td>$grade</td>
                <td>{$subject['teacher_name']}</td>
              </tr>";
}

// Determine the overall grade based on total marks
$overallGrade = getOverallGrade($totalMarks, $gradingCriteria);

// Add the Aggregate row for total marks and overall grade
$html .= "<tr>
            <td><strong>Aggregate</strong></td>
            <td><strong>$totalMarks</strong></td>
            <td></td>
          </tr>";

    $html .= '</tbody>
        </table>
        <div class="remarks-signature">
    <div class="remarks">
        <strong>Remarks:</strong> ' . $passFailStatus . '
    </div>
    <div class="signature">
        <strong>Head Teacher Signature:</strong> _______________
    </div>
    </div>
        <div class="grading-criteria">
            <h3>Grading Criteria (' . $gradingLevel . ' Level)</h3>
            <table class="grading-table">
                <thead>
                    <tr>';
    
    // Add headers for grades (A, B, C, D, E, F) once
    foreach ($gradingCriteria as $criteria) {
        $html .= "<th>{$criteria['grade']}</th>";
    }

    $html .= '</tr>
                </thead>
                <tbody>
                    <tr>';
    
    // Add marks range (min to max) in a **single row**
    foreach ($gradingCriteria as $criteria) {
        $html .= "<td>{$criteria['min_marks']} - {$criteria['max_marks']}</td>";
    }

    $html .= '</tr>
                </tbody>
            </table>
        </div>
    </div>';

    // Add inline CSS to the content for proper styling
    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .report-container { border: 2px solid #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .student-info { border: 2px solid #000; padding: 10px; margin-bottom: 20px; }
        .grades-table { width: 100%; border-collapse: collapse; }
        .grades-table th, .grades-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .remarks { margin: 20px 0; }
        .grading-criteria { border: 2px solid #000; padding: 10px; margin-top: 20px; }
        .grading-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grading-table th, .grading-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .remarks-signature {display: flex;justify-content: space-between; align-items: center;}
        .remarks { width: 45%;}
        .signature {width: 45%; text-align: right;}
        </style>' . $html;

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
?>
