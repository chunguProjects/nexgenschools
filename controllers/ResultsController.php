<?php

require_once __DIR__ . '/../config/db.php';       // gives us $conn (mysqli)
require_once __DIR__ . '/../model/ResultsModel.php';

// ASSUMPTION: classes.name embeds the form number (e.g. "Form 2A").
// Form 1-2 => JCE, Form 3-4 => MSCE. Replace this with a real `level`
// column on `classes` once you add one — this is a stand-in until then.
function inferLevelFromClassName(string $className): string
{
    if (preg_match('/(\d)/', $className, $m)) {
        return ((int) $m[1] >= 3) ? 'MSCE' : 'JCE';
    }
    return 'MSCE';
}

$dbError = null;
$academicYears = $exams = $classes = $subjects = $students = [];
$selectedYearId = $selectedExamId = $selectedClassId = null;
$selectedYearName = $selectedExamName = $selectedClassName = '';
$summary = ['boys_entered' => 0, 'girls_entered' => 0, 'boys_passed' => 0, 'girls_passed' => 0, 'boys_failed' => 0, 'girls_failed' => 0];
$totalEntered = $totalPassed = $totalFailed = $passRate = 0;
$schoolName = 'School Results Portal';

try {
    $model = new ResultsModel($conn);

    $schoolName = $model->getSchoolName();
    $academicYears = $model->getAcademicYears();

    // Resolve selected academic year: GET param, else is_current=1, else most recent
    $selectedYearId = isset($_GET['year']) ? (int) $_GET['year'] : null;
    if (!$selectedYearId) {
        foreach ($academicYears as $y) {
            if ($y['is_current']) { $selectedYearId = (int) $y['academic_year_id']; break; }
        }
    }
    if (!$selectedYearId && $academicYears) {
        $selectedYearId = (int) $academicYears[0]['academic_year_id'];
    }

    if ($selectedYearId) {
        $exams = $model->getExams($selectedYearId);
        $classes = $model->getClasses($selectedYearId);
    }

    $selectedExamId = isset($_GET['exam']) ? (int) $_GET['exam'] : null;
    if ((!$selectedExamId || !array_filter($exams, fn($e) => $e['id'] == $selectedExamId)) && $exams) {
        $selectedExamId = (int) $exams[0]['id'];
    }

    $selectedClassId = isset($_GET['class']) ? (int) $_GET['class'] : null;
    if ((!$selectedClassId || !array_filter($classes, fn($c) => $c['class_id'] == $selectedClassId)) && $classes) {
        $selectedClassId = (int) $classes[0]['class_id'];
    }

    foreach ($exams as $e) if ($e['id'] == $selectedExamId) $selectedExamName = $e['exam_name'];
    foreach ($academicYears as $y) if ($y['academic_year_id'] == $selectedYearId) $selectedYearName = $y['name'];
    foreach ($classes as $c) if ($c['class_id'] == $selectedClassId) $selectedClassName = $c['name'];

    if ($selectedClassId && $selectedYearId) {
        $subjects = $model->getSubjectsForClass($selectedClassId, $selectedYearId);
        $enrolled = $model->getEnrolledStudents($selectedClassId, $selectedYearId);
    } else {
        $enrolled = [];
    }

    $level = $selectedClassName ? inferLevelFromClassName($selectedClassName) : 'MSCE';
    $criteria = $model->getGradingCriteria($level);

    $resolveGrade = function (?int $marks) use ($criteria) {
        if ($marks === null) return null;
        foreach ($criteria as $c) {
            if ($marks >= (int) $c['min_marks'] && $marks <= (int) $c['max_marks']) {
                return $c;
            }
        }
        return null;
    };

    $gradeMap = [];
    if ($selectedExamId && $selectedClassId) {
        foreach ($model->getGrades($selectedExamId, $selectedClassId) as $row) {
            $gradeMap[$row['student_id']][$row['subject_id']] = $row['marks'];
        }
    }

    // Points = grade value cast to int, summed across subjects (MANEB-style
    // numeric grading via grading_criteria.grade). A subject counts as
    // failed if its grading_criteria.description contains "fail"; a
    // student's overall result is "Fail" if any entered subject failed.
    // Adjust this if your school's promotion rule differs (e.g. must
    // pass English, or a best-6-subjects points ceiling).
    $rows = [];
    foreach ($enrolled as $student) {
        $marksRow = [];
        $totalPoints = 0;
        $hasAnyMark = false;
        $failedSubject = false;

        foreach ($subjects as $subject) {
            $rawMarks = $gradeMap[$student['student_id']][$subject['subject_id']] ?? null;
            $marksInt = ($rawMarks !== null && is_numeric($rawMarks)) ? (int) $rawMarks : null;
            $grade = $resolveGrade($marksInt);

            if ($marksInt !== null) $hasAnyMark = true;
            if ($grade && is_numeric($grade['grade'])) $totalPoints += (int) $grade['grade'];
            if ($grade && stripos((string) $grade['description'], 'fail') !== false) $failedSubject = true;

            $marksRow[] = [
                'marks' => $marksInt,
                'grade' => $grade['grade'] ?? null,
            ];
        }

        $rows[] = [
            'student_id' => $student['student_id'],
            'name' => trim($student['first_name'] . ' ' . $student['last_name']),
            'gender' => $student['gender'],
            'marks' => $marksRow,
            'points' => $totalPoints,
            'has_marks' => $hasAnyMark,
            'result' => $hasAnyMark ? ($failedSubject ? 'Fail' : 'Pass') : null,
        ];
    }

    // Rank by total points ascending (lower = stronger, MANEB-style).
    // Students with no marks recorded yet sort last, unranked.
    usort($rows, function ($a, $b) {
        if ($a['has_marks'] !== $b['has_marks']) return $b['has_marks'] <=> $a['has_marks'];
        return $a['points'] <=> $b['points'];
    });
    $position = 0;
    $lastPoints = null;
    $rank = 0;
    foreach ($rows as &$row) {
        if (!$row['has_marks']) { $row['position'] = null; continue; }
        $rank++;
        if ($row['points'] !== $lastPoints) { $position = $rank; $lastPoints = $row['points']; }
        $row['position'] = $position;
    }
    unset($row);
    $students = $rows;

    foreach ($students as $row) {
        $isBoy = $row['gender'] === 'Male';
        if ($isBoy) $summary['boys_entered']++; else $summary['girls_entered']++;
        if ($row['result'] === 'Pass') { $isBoy ? $summary['boys_passed']++ : $summary['girls_passed']++; }
        if ($row['result'] === 'Fail') { $isBoy ? $summary['boys_failed']++ : $summary['girls_failed']++; }
    }
    $totalEntered = $summary['boys_entered'] + $summary['girls_entered'];
    $totalPassed  = $summary['boys_passed'] + $summary['girls_passed'];
    $totalFailed  = $summary['boys_failed'] + $summary['girls_failed'];
    $passRate     = $totalEntered > 0 ? round(($totalPassed / $totalEntered) * 100) : 0;

} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

require __DIR__ . '/../views/results_summary_view.php';