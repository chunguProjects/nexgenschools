<?php
require_once __DIR__ . '/../utils/grades-utils.php';
require_once __DIR__ . '/../model/GeneralSettingsModel.php'; // general settings
require_once __DIR__ . '/../model/ReportSettingsModel.php';

class StudentReportService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function generateReports($classId, $term, $examId) {
        $context = $this->buildClassContext($classId, $term, $examId);

        $htmlArray = [];
        foreach ($context['students'] as $student) {
            $htmlArray[] = $this->renderStudentReport($student, $context);
        }

        return $htmlArray;
    }

    /**
     * Same ranking/context computation as generateReports(), but renders
     * and returns the HTML for exactly one student instead of the whole
     * class. Ranking still needs every student's marks to compute
     * position correctly — this just avoids rendering everyone else's
     * report.php just to throw it away.
     *
     * Returns null if the student isn't found in this class's roster.
     */
    public function generateSingleReport($classId, $term, $examId, $studentId) {
        $context = $this->buildClassContext($classId, $term, $examId);

        foreach ($context['students'] as $student) {
            if ((string)$student['student_id'] === (string)$studentId) {
                return $this->renderStudentReport($student, $context);
            }
        }

        return null; // student not in this class's roster
    }

    /**
     * Everything generateReports() used to do BEFORE the per-student
     * render loop: load students/subjects/grades, compute class ranking,
     * and gather settings. Shared by both generateReports() and
     * generateSingleReport() so the ranking logic only lives in one place.
     */
    private function buildClassContext($classId, $term, $examId) {
        include_once __DIR__ . '/../model/student.php';
        include_once __DIR__ . '/../model/class.php';
        include_once __DIR__ . '/../model/grades.php';
        include_once __DIR__ . '/../model/subject.php';
        include_once __DIR__ . '/../model/exam.php';
        include_once __DIR__ . '/../model/school.php';

        $studentModel = new Student($this->conn);
        $classModel   = new ClassGroup($this->conn);
        $gradesModel  = new Grades($this->conn);
        $subjectModel = new Subjects($this->conn);
        $examModel    = new Exams($this->conn);

        $students = $studentModel->getStudentsByClass($classId);

        // ✅ Fetch exam and academic year FIRST
        $examInfo = $examModel->getExamById($examId);
        $examName = $examInfo['exam_name'] ?? '';
        $academicYearId = $examInfo['academic_year_id'] ?? null;

        // ✅ NOW subjects will work correctly
        $subjects = $subjectModel->getSubjectsByClass($classId, $academicYearId);

        $grades   = $gradesModel->getGradesByClassTermExam($classId, $term, $examId);

        $class = $classModel->getClassById($classId);
        $className = $class['name'] ?? 'N/A';
        $gradingCriteria = $this->getGradingCriteria($className);
        $classLevel = $this->determineLevelFromClassName($className);

        // ---------------------------------------------------------------
        // Rank the whole class, then merge each student's position back
        // into $students, so every individual report card can look up its
        // own student's position from the same roster it already has.
        //
        // JCE ranks by total marks (higher is better). MSCE ranks by total
        // points — sum of the best 6 subjects' grade values, lower is
        // better. Ties share a position (competition ranking). Students
        // with no marks recorded sort last, unranked (position = null).
        // ---------------------------------------------------------------
        $isJCE = ($classLevel === 'JCE');
        $rankMetrics = [];

        foreach ($students as $s) {
            $sid = $s['student_id'];
            $hasAnyMark = false;
            $totalMarksAll = 0;
            $pointsPool = [];

            foreach ($subjects as $subject) {
                $subId = $subject['subject_id'];
                $g = $grades[$sid][$subId] ?? null;
                if ($g && isset($g['marks']) && is_numeric($g['marks'])) {
                    $hasAnyMark = true;
                    $totalMarksAll += (int) $g['marks'];
                    $gradeInfo = getGradeRemarkByMarks((int) $g['marks'], $gradingCriteria);
                    if (is_numeric($gradeInfo['grade'])) {
                        $pointsPool[] = (int) $gradeInfo['grade'];
                    }
                }
            }

            sort($pointsPool);
            $totalPoints = array_sum(array_slice($pointsPool, 0, 6));

            $rankMetrics[$sid] = [
                'has_marks' => $hasAnyMark,
                'total_marks' => $totalMarksAll,
                'total_points' => $totalPoints,
            ];
        }

        $order = array_keys($rankMetrics);
        usort($order, function ($a, $b) use ($rankMetrics, $isJCE) {
            $ma = $rankMetrics[$a];
            $mb = $rankMetrics[$b];
            if ($ma['has_marks'] !== $mb['has_marks']) return $mb['has_marks'] <=> $ma['has_marks'];
            return $isJCE
                ? $mb['total_marks'] <=> $ma['total_marks']
                : $ma['total_points'] <=> $mb['total_points'];
        });

        $position = 0;
        $lastMetric = null;
        $rank = 0;
        foreach ($order as $sid) {
            $m = $rankMetrics[$sid];
            if (!$m['has_marks']) { $rankMetrics[$sid]['position'] = null; continue; }
            $metric = $isJCE ? $m['total_marks'] : $m['total_points'];
            $rank++;
            if ($metric !== $lastMetric) { $position = $rank; $lastMetric = $metric; }
            $rankMetrics[$sid]['position'] = $position;
        }

        foreach ($students as &$s) {
            $sid = $s['student_id'];
            $s['position'] = $rankMetrics[$sid]['position'] ?? null;
            $s['total_marks'] = $rankMetrics[$sid]['total_marks'] ?? 0;
            $s['total_points'] = $rankMetrics[$sid]['total_points'] ?? 0;
        }
        unset($s);
        // ---------------------------------------------------------------

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

        return [
            'students'        => $students,
            'subjects'        => $subjects,
            'grades'          => $grades,
            'term'            => $term,
            'gradingCriteria' => $gradingCriteria,
            'academicYear'    => $academicYear,
            'schoolName'      => $schoolName,
            'className'       => $className,
            'settings'        => $settings,
            'level'           => $classLevel,
            'examName'        => $examName,
        ];
    }

    /**
     * Renders report.php for one student row, given the shared class
     * context built by buildClassContext(). Pulled out so both
     * generateReports() and generateSingleReport() render identically.
     */
    private function renderStudentReport($student, $context) {
        $student['academic_year'] = $context['academicYear'];
        $student['school_name']   = $context['schoolName'];
        $student['class_name']    = $context['className'];
        $student['settings']      = $context['settings'];
        $student['level']         = $context['level'];
        $student['exam_name']     = $context['examName'];

        $subjects        = $context['subjects'];
        $grades          = $context['grades'];
        $term            = $context['term'];
        $gradingCriteria = $context['gradingCriteria'];
        $students        = $context['students']; // full roster, for class-size context in report.php

        /**
         * Variables available to report.php
         *
         * @var array $student
         * @var array $subjects
         * @var array $grades
         * @var string $term
         * @var array $gradingCriteria
         * @var array $students  Full class roster, each row carrying a
         *                       computed 'position' — used by report.php
         *                       to show this student's rank and class size.
         */

        ob_start();
        include __DIR__ . '/../views/report.php';
        return ob_get_clean();
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