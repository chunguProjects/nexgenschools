<?php
require_once('../utils/grades-utils.php');
require_once('../model/GeneralSettingsModel.php'); // include settings model here
require_once('../model/ReportSettingsModel.php');

class StudentReportService {
    private $conn, $schoolId;

    public function __construct($conn, $schoolId) {
        $this->conn = $conn;
        $this->schoolId = $schoolId;
    }

    public function generateReports($classId, $term, $examId) {
        include_once('../model/student.php');
        include_once('../model/class.php');
        include_once('../model/grades.php');
        include_once('../model/subject.php');
        include_once('../model/exam.php');
        include_once('../model/school.php');

        $studentModel = new Student($this->conn);
        $classModel = new ClassGroup($this->conn);
        $gradesModel = new Grades($this->conn);
        $subjectModel = new Subjects($this->conn);
        $examModel = new Exams($this->conn);
        $schoolModel = new School($this->conn);

        $students = $studentModel->getStudentsByClass($classId);
        $subjects = $subjectModel->getSubjectsByClass($classId);
        $grades = $gradesModel->getGradesByClassTermExam($classId, $term, $examId);

        $class = $classModel->getClassById($classId);
        $className = $class['name'] ?? 'N/A';
        $gradingCriteria = $this->getGradingCriteria($class['name']);
        $academicYear = $examModel->getAcademicYearByClassAndTerm($classId, $term);

        $schoolInfo = $schoolModel->getSchoolById($this->schoolId);
        $schoolName = $schoolInfo['school_name'] ?? 'SCHOOL NAME';

        $examInfo = $examModel->getExamById($examId);
        $examName = $examInfo['exam_name'] ?? '';
        // 🆕 Get logo path for school
        $generalSettingsModel = new GeneralSettingsModel($this->conn);
        $reportSettingsModel = new ReportSettingsModel($this->conn);

        $generalSettings = $generalSettingsModel->getSettings($this->schoolId);
        $reportSettings = $reportSettingsModel->getSettings($this->schoolId);

        $logoPath = $generalSettings['logo_path'] ?? null;

        // Merge general and report settings into one array
        $settings = array_merge($generalSettings, $reportSettings);


        $htmlArray = [];

        foreach ($students as $student) {
            $student['academic_year'] = $academicYear;
            $student['school_name'] = $schoolName;
            $student['class_name'] = $className; // ✅ Inject class name here
            $student['settings'] = $settings;
            $student['level'] = $this->determineLevelFromClassName($student['class_name']);
            $student['exam_name'] = $examName; // 🆕 Pass to report


            ob_start();
            include('../views/report.php');
            $htmlArray[] = ob_get_clean();
        }

        return $htmlArray;
    }

    private function getGradingCriteria($className) {
        $level = $this->determineLevelFromClassName($className);

        if (!$level) return [];

        $stmt = $this->conn->prepare("SELECT * FROM grading_criteria WHERE school_id = ? AND level = ? ORDER BY min_marks DESC");
        $stmt->bind_param("is", $this->schoolId, $level);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function determineLevelFromClassName($className) {
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
