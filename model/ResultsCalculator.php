<?php

require_once __DIR__ . '/ResultsModel.php';

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

/**
 * Resolves the year/exam/class filters from $get and computes the full
 * results summary (subjects, ranked students, pass/fail, gender summary).
 * Used by views/summary.php (on-screen) and both export endpoints, so all
 * three always show identical numbers.
 */
function computeResultsSummary(mysqli $conn, array $get): array
{
    $out = [
        'dbError' => null,
        'academicYears' => [], 'exams' => [], 'classes' => [],
        'subjects' => [], 'students' => [],
        'selectedYearId' => null, 'selectedExamId' => null, 'selectedClassId' => null,
        'selectedYearName' => '', 'selectedExamName' => '', 'selectedClassName' => '',
        'isJCE' => false, 'metricLabel' => 'Pts',
        'summary' => ['boys_entered' => 0, 'girls_entered' => 0, 'boys_passed' => 0, 'girls_passed' => 0, 'boys_failed' => 0, 'girls_failed' => 0],
        'totalEntered' => 0, 'totalPassed' => 0, 'totalFailed' => 0, 'passRate' => 0,
    ];

    try {
        $model = new ResultsModel($conn);

        $out['academicYears'] = $model->getAcademicYears();

        $selectedYearId = isset($get['year']) ? (int) $get['year'] : null;
        if (!$selectedYearId) {
            foreach ($out['academicYears'] as $y) {
                if ($y['is_current']) { $selectedYearId = (int) $y['academic_year_id']; break; }
            }
        }
        if (!$selectedYearId && $out['academicYears']) {
            $selectedYearId = (int) $out['academicYears'][0]['academic_year_id'];
        }

        if ($selectedYearId) {
            $out['exams'] = $model->getExams($selectedYearId);
            $out['classes'] = $model->getClasses($selectedYearId);
        }

        $selectedExamId = isset($get['exam']) ? (int) $get['exam'] : null;
        if ((!$selectedExamId || !array_filter($out['exams'], fn($e) => $e['id'] == $selectedExamId)) && $out['exams']) {
            $selectedExamId = (int) $out['exams'][0]['id'];
        }

        $selectedClassId = isset($get['class']) ? (int) $get['class'] : null;
        if ((!$selectedClassId || !array_filter($out['classes'], fn($c) => $c['class_id'] == $selectedClassId)) && $out['classes']) {
            $selectedClassId = (int) $out['classes'][0]['class_id'];
        }

        $selectedYearName = $selectedExamName = $selectedClassName = '';
        foreach ($out['exams'] as $e) if ($e['id'] == $selectedExamId) $selectedExamName = $e['exam_name'];
        foreach ($out['academicYears'] as $y) if ($y['academic_year_id'] == $selectedYearId) $selectedYearName = $y['name'];
        foreach ($out['classes'] as $c) if ($c['class_id'] == $selectedClassId) $selectedClassName = $c['name'];

        $enrolled = [];
        if ($selectedClassId && $selectedYearId) {
            $out['subjects'] = $model->getSubjectsForClass($selectedClassId, $selectedYearId);
            $enrolled = $model->getEnrolledStudents($selectedClassId, $selectedYearId);
        }
        $subjects = $out['subjects'];

        $level = $selectedClassName ? inferLevelFromClassName($selectedClassName) : 'MSCE';
        $isJCE = ($level === 'JCE');
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

        // English subject for this class — needed for the pass rule below.
        $englishSubjectId = null;
        foreach ($subjects as $s) {
            if (stripos($s['subject_name'], 'english') !== false) { $englishSubjectId = $s['subject_id']; break; }
        }

        $gradeMap = [];
        if ($selectedExamId && $selectedClassId) {
            foreach ($model->getGrades($selectedExamId, $selectedClassId) as $row) {
                $gradeMap[$row['student_id']][$row['subject_id']] = $row['marks'];
            }
        }

        // PROMOTION RULE: a student passes if they passed at least 6 subjects,
        // one of which is English. Points are the sum of the best 6 subjects'
        // grade points (lowest = strongest); English is only included there if
        // it naturally lands among those best 6 — it is not forced in.
        // NOTE: if a class offers fewer than 6 subjects, "6 subjects passed" is
        // unreachable and everyone will show as Fail — revisit if that applies.
        $PASS_SUBJECT_THRESHOLD = 6;
        $BEST_N_FOR_POINTS = 6;

        $rows = [];
        foreach ($enrolled as $student) {
            $marksRow = [];
            $hasAnyMark = false;
            $passedCount = 0;
            $englishPassed = false;
            $totalMarksAll = 0;
            $pointsPool = [];

            foreach ($subjects as $subject) {
                $rawMarks = $gradeMap[$student['student_id']][$subject['subject_id']] ?? null;
                $marksInt = ($rawMarks !== null && is_numeric($rawMarks)) ? (int) $rawMarks : null;
                $grade = $resolveGrade($marksInt);
                $subjectFailed = $grade && stripos((string) $grade['description'], 'fail') !== false;
                $subjectPassed = $grade && !$subjectFailed;

                if ($marksInt !== null) {
                    $hasAnyMark = true;
                    $totalMarksAll += $marksInt;
                }
                if ($subjectPassed) {
                    $passedCount++;
                    if ($subject['subject_id'] === $englishSubjectId) $englishPassed = true;
                }
                if ($grade && is_numeric($grade['grade'])) {
                    $pointsPool[] = (int) $grade['grade'];
                }

                $marksRow[] = ['marks' => $marksInt, 'grade' => $grade['grade'] ?? null];
            }

            sort($pointsPool);
            $totalPoints = array_sum(array_slice($pointsPool, 0, $BEST_N_FOR_POINTS));

            $rows[] = [
                'student_id' => $student['student_id'],
                'name' => trim($student['first_name'] . ' ' . $student['last_name']),
                'gender' => $student['gender'],
                'marks' => $marksRow,
                'total_marks' => $totalMarksAll,
                'total_points' => $totalPoints,
                'has_marks' => $hasAnyMark,
                'result' => $hasAnyMark
                    ? (($passedCount >= $PASS_SUBJECT_THRESHOLD && $englishPassed) ? 'Pass' : 'Fail')
                    : null,
            ];
        }

        // JCE ranks by total marks (higher is better); MSCE ranks by total
        // points (lower is better). No-marks-yet students sort last, unranked.
        // Ties share a position (competition ranking).
        usort($rows, function ($a, $b) use ($isJCE) {
            if ($a['has_marks'] !== $b['has_marks']) return $b['has_marks'] <=> $a['has_marks'];
            return $isJCE
                ? $b['total_marks'] <=> $a['total_marks']
                : $a['total_points'] <=> $b['total_points'];
        });
        $position = 0;
        $lastMetric = null;
        $rank = 0;
        foreach ($rows as &$row) {
            if (!$row['has_marks']) { $row['position'] = null; continue; }
            $metric = $isJCE ? $row['total_marks'] : $row['total_points'];
            $rank++;
            if ($metric !== $lastMetric) { $position = $rank; $lastMetric = $metric; }
            $row['position'] = $position;
        }
        unset($row);

        $summary = ['boys_entered' => 0, 'girls_entered' => 0, 'boys_passed' => 0, 'girls_passed' => 0, 'boys_failed' => 0, 'girls_failed' => 0];
        foreach ($rows as $row) {
            $isBoy = $row['gender'] === 'Male';
            if ($isBoy) $summary['boys_entered']++; else $summary['girls_entered']++;
            if ($row['result'] === 'Pass') { $isBoy ? $summary['boys_passed']++ : $summary['girls_passed']++; }
            if ($row['result'] === 'Fail') { $isBoy ? $summary['boys_failed']++ : $summary['girls_failed']++; }
        }
        $totalEntered = $summary['boys_entered'] + $summary['girls_entered'];
        $totalPassed  = $summary['boys_passed'] + $summary['girls_passed'];
        $totalFailed  = $summary['boys_failed'] + $summary['girls_failed'];
        $passRate     = $totalEntered > 0 ? round(($totalPassed / $totalEntered) * 100) : 0;

        $out = array_merge($out, [
            'students' => $rows,
            'selectedYearId' => $selectedYearId, 'selectedExamId' => $selectedExamId, 'selectedClassId' => $selectedClassId,
            'selectedYearName' => $selectedYearName, 'selectedExamName' => $selectedExamName, 'selectedClassName' => $selectedClassName,
            'isJCE' => $isJCE, 'metricLabel' => $isJCE ? 'Total' : 'Pts',
            'summary' => $summary,
            'totalEntered' => $totalEntered, 'totalPassed' => $totalPassed, 'totalFailed' => $totalFailed, 'passRate' => $passRate,
        ]);
    } catch (Throwable $e) {
        $out['dbError'] = $e->getMessage();
    }

    return $out;
}