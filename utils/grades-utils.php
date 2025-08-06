<?php
function isPassingGrade($marks) {
    return $marks >= 40;
}

function getGradeRemarkByMarks($marks, $criteriaList) {
    foreach ($criteriaList as $criteria) {
        if ($marks >= $criteria['min_marks'] && $marks <= $criteria['max_marks']) {
            return [
                'grade' => $criteria['grade'],
                'remark' => $criteria['description'], // Teacher remark
                'form_teacher_remark' => $criteria['form_teacher_remark'],
                'head_teacher_remark' => $criteria['head_teacher_remark']
            ];
        }
    }
    return ['grade' => '-', 'remark' => '-', 'form_teacher_remark' => '', 'head_teacher_remark' => ''];
}
function getTeacherRemarksByAverage($average, $gradingCriteria) {
    foreach ($gradingCriteria as $criteria) {
        if ($average >= $criteria['min_marks'] && $average <= $criteria['max_marks']) {
            return [
                'form_teacher' => $criteria['form_teacher_remark'] ?? '',
                'head_teacher' => $criteria['head_teacher_remark'] ?? ''
            ];
        }
    }
    return ['form_teacher' => '', 'head_teacher' => ''];
}


function getPassFailStatus($studentId, $grades, $subjects) {
    $passed = 0;
    $englishPassed = false;

    foreach ($subjects as $subject) {
        $sid = $subject['subject_id'];
        $grade = $grades[$studentId][$sid] ?? null;
        if ($grade && isPassingGrade($grade['marks'])) {
            $passed++;
            if (strtolower($subject['subject_name']) == 'english') {
                $englishPassed = true;
            }
        }
    }

    return ($passed >= 6 && $englishPassed) ? 'Passed' : 'Failed';
}
function getOverallGrade($average, $criteriaList) {
    foreach ($criteriaList as $criteria) {
        if ($average >= $criteria['min_marks'] && $average <= $criteria['max_marks']) {
            return $criteria['grade'];
        }
    }
    return '-';
}
