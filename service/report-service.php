<?php
require_once('../utils/grades-utils.php');
require_once('../model/GeneralSettingsModel.php'); // general settings
require_once('../model/ReportSettingsModel.php');

class StudentReportService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function generateReports($classId, $term, $examId) {
    include_once('../model/student.php');
    include_once('../model/class.php');
    include_once('../model/grades.php');
    include_once('../model/subject.php');
    include_once('../model/exam.php');
    include_once('../model/school.php');

    $studentModel = new Student($this->conn);
    $classModel   = new ClassGroup($this->conn);
    $gradesModel  = new Grades($this->conn);
    $subjectModel = new Subjects($this->conn);
    $examModel    = new Exams($this->conn);
    $schoolModel  = new School($this->conn);

    $students = $studentModel->getStudentsByClass($classId);
    $subjects = $subjectModel->getSubjectsByClass($classId);
    $grades   = $gradesModel->getGradesByClassTermExam($classId, $term, $examId);

    $class = $classModel->getClassById($classId);
    $className = $class['name'] ?? 'N/A';
    $gradingCriteria = $this->getGradingCriteria($className);

    // ✅ Fetch exam and academic year info
    $examInfo = $examModel->getExamById($examId);
    $examName = $examInfo['exam_name'] ?? '';
    $academicYearId = $examInfo['academic_year_id'] ?? null;

    $academicYear = null;
    if ($academicYearId) {
        $stmt = $this->conn->prepare("SELECT name FROM academic_years WHERE academic_year_id = ? LIMIT 1");
        $stmt->bind_param("i", $academicYearId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $academicYear = $res['name'] ?? null;
    }

    // 🆕 Get general settings
    $generalSettingsModel = new GeneralSettingsModel($this->conn);
    $reportSettingsModel  = new ReportSettingsModel($this->conn);

    $generalSettings = $generalSettingsModel->getSettings(); // single row
    $reportSettings  = $reportSettingsModel->getSettings();

    $schoolName = $generalSettings['school_name'] ?? 'SCHOOL NAME';
    $logoPath   = $generalSettings['logo_path'] ?? null;

    $settings = array_merge($generalSettings, $reportSettings);

    $htmlArray = [];

    foreach ($students as $student) {
        $student['academic_year'] = $academicYear;
        $student['school_name']   = $schoolName;
        $student['class_name']    = $className;
        $student['settings']      = $settings;
        $student['level']         = $this->determineLevelFromClassName($className);
        $student['exam_name']     = $examName;

        ob_start();
        include('../views/report.php');
        $htmlArray[] = ob_get_clean();
    }

    return $htmlArray;
}


    private function getGradingCriteria($className) {
        $level = $this->determineLevelFromClassName($className);
        if (!$level) return [];

        $stmt = $this->conn->prepare("SELECT * FROM grading_criteria WHERE level = ? ORDER BY min_marks DESC");
        $stmt->bind_param("s", $level);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function determineLevelFromClassName($className) {
        $wordToDigit = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4
        ];

        $className = strtolower($className);

        foreach ($wordToDigit as $word => $digit) {
            if (strpos($className, "form $word") !== false || strpos($className, $word) !== false) {
                if ($digit === 1 || $digit === 2) return 'JCE';
                if ($digit === 3 || $digit === 4) return 'MSCE';
            }
        }

        if (preg_match('/form\s*([1-4])/', $className, $matches) || preg_match('/\b([1-4])\b/', $className, $matches)) {
            $form = (int)$matches[1];
            if ($form === 1 || $form === 2) return 'JCE';
            if ($form === 3 || $form === 4) return 'MSCE';
        }

        return null;
    }
}
