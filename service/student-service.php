<?php
require_once('../model/student.php');
require_once('../model/class.php');
require_once('../model/grades.php');
require_once('../model/subject.php');
require_once('../model/exam.php');
require_once('../model/GeneralSettingsModel.php');

class StudentSelfReportService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Get a student's report for a specific exam + term
     */
    public function getStudentReport($studentId, $term, $examId) {
        $studentModel = new Student($this->conn);
        $classModel   = new ClassGroup($this->conn);
        $gradesModel  = new Grades($this->conn);
        $subjectModel = new Subjects($this->conn);
        $examModel    = new Exams($this->conn);
        $settingsModel = new GeneralSettingsModel($this->conn);

        // Student info
        $student = $studentModel->getStudentById($studentId);
        if (!$student) return null;

        $classId   = $student['class_id'];
        $class     = $classModel->getClassById($classId);
        $subjects  = $subjectModel->getSubjectsByClass($classId);

        // Exam info
        $exam = $examModel->getExamById($examId);

        // Grades
        $grades = $gradesModel->getGradesByClassTermExam($studentId, $term, $examId);

        // Settings (school name, logo, etc.)
        $settings = $settingsModel->getSettings();

        return [
            'student'   => $student,
            'class'     => $class,
            'subjects'  => $subjects,
            'exam'      => $exam,
            'term'      => $term,
            'grades'    => $grades,
            'settings'  => $settings
        ];
    }
}
