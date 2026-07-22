<?php

/**
 * @var array $student
 * @var array $subjects
 * @var array $grades
 * @var string $term
 * @var array $gradingCriteria
 */

$totalMarks = 0;
$subjectCount = 1;

// Filter only subjects with grades
$gradedSubjects = [];
foreach ($subjects as $subject) {
    $subjectId = $subject['subject_id'];
    if (!empty($grades[$student['student_id']][$subjectId])) {
        $gradedSubjects[] = $subject;
    }
}

// Handle logo
$settings = $student['settings'] ?? [];
$logoPath = $settings['logo_path'] ?? null;
$logoUrl = $logoPath ? '/nexgenschools/' . ltrim($logoPath, '/') : null;
$logoFullPath = $logoPath ? __DIR__ . '/../' . ltrim($logoPath, '/') : null;
?>

<style>
body { font-size: 12px; }
.report-container {
    border: 2px solid #000;
    padding: 10px 0;
    width: 670px;
    height: 950px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.header {
    text-align: center;
    border-bottom: 2px solid #000;
    padding-bottom: 2px;
}
.header-logo img {
    width: 45px;
    height: 45px;
    object-fit: contain;
    margin-bottom: 3px;
}
.school-name {
    font-size: 16px;
    font-weight: bold;
    margin: 2px 0 1px 0;
    line-height: 1.1;
}
.school-meta {
    font-size: 11px;
    margin: 1px 0;
    line-height: 1.2;
}
.report-title {
    font-weight: bold;
    font-size: 13px;
    margin: 3px 0 0 0;
    text-transform: uppercase;
}
.student-info table { width: 100%; border-collapse: collapse; padding-bottom: 10px; }
.grades-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.grades-table th, .grades-table td {
    border: 1px solid #000;
    padding: 2px;
    text-align: left;
}
.grades-table th { background-color: #f5f5f5; }
.sno { width: 7%; }
.subject { width: 35%; padding-left: 5px; }
.marks { width: 10%; }
.grade { width: 10%; }
.remarks { width: 18%; }
.teacher { width: 20%; }
table.signature, table.form-remarks, table.grading-criteria { width: 100%; border-collapse: collapse; }
.fee-structure th, .fee-structure td { border: 1px solid #000; padding: 6px; font-size: 12px; }
</style>

<div class="report-container">
   <!-- HEADER -->
    <div class="header">
        <div class="header-logo">
            <?php if ($logoFullPath && file_exists($logoFullPath)): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo">
            <?php endif; ?>
        </div>

        <p class="school-name"><?= htmlspecialchars($student['school_name'] ?? 'SCHOOL NAME') ?></p>
        <p class="school-meta">
            <?= htmlspecialchars($settings['address'] ?? '') ?><br>
            Email: <?= htmlspecialchars($settings['contact_email'] ?? '') ?><br>
            Phone: <?= htmlspecialchars($settings['contact_phone'] ?? '') ?>
        </p>
        <p class="report-title">School Progress Report</p>
    </div>

    <!-- STUDENT INFO -->
    <div class="student-info">
        <table>
            <tr>
                <td><strong>Student Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                <td colspan="2" style="text-align: center;"><strong>Academic Year:</strong> <?= htmlspecialchars($student['academic_year'] ?? '-') ?></td>
            </tr>
            <tr>
                <td><strong>Term:</strong> <?= htmlspecialchars($term) ?></td>
                <td colspan="2" style="text-align: center;"><strong>No of Students:</strong> <?= count($students) ?></td>
            </tr>
            <tr>
                <td><strong>Exam:</strong> <?= htmlspecialchars($student['exam_name'] ?? '-') ?></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td><strong>Class:</strong> <?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></td>
                <td colspan="2" style="text-align: center;"><strong>Position in Class:</strong> 30</td>
            </tr>
        </table>
    </div>

    <!-- GRADES TABLE -->
    <table class="grades-table">
        <thead>
            <tr>
                <th class="sno">S.No</th>
                <th class="subject">Subject</th>
                <th class="marks">Marks</th>
                <th class="grade">Grade</th>
                <th class="remarks">Remarks</th>
                <th class="teacher">Teacher</th>
            </tr>
        </thead>
        <tbody>
        <?php for ($i = 0; $i < 15; $i++): ?>
            <?php
            $subjectName = $marks = $grade = $remark = $teacherName = '';
            if (isset($gradedSubjects[$i])) {
                $subject = $gradedSubjects[$i];
                $subjectId = $subject['subject_id'];
                $subjectName = htmlspecialchars($subject['subject_name']);
                $gradeInfo = $grades[$student['student_id']][$subjectId];
                $marks = $gradeInfo['marks'];
                $totalMarks += $marks;
                $gradeRemark = getGradeRemarkByMarks($marks, $gradingCriteria);
                $grade = $gradeRemark['grade'];
                $remark = $gradeRemark['remark'];
                $teacherName = htmlspecialchars($subject['teacher_name']);
            }
            ?>
            <tr>
                <td class="sno"><?= $subjectCount ?></td>
                <td class="subject"><?= $subjectName ?></td>
                <td class="marks"><?= $marks ?></td>
                <td class="grade"><?= $grade ?></td>
                <td class="remarks"><?= $remark ?></td>
                <td class="teacher"><?= $teacherName ?></td>
            </tr>
        <?php $subjectCount++; endfor; ?>

        <?php
        $level = $student['level'];
        $gradedCount = count($gradedSubjects);
        $averageMarks = $gradedCount > 0 ? round($totalMarks / $gradedCount, 2) : 0;
        $displayGrade = $totalPoints = '-';
        if ($level === 'JCE') {
            $displayGrade = $gradedCount >= 6 ? getOverallGrade($averageMarks, $gradingCriteria) : '< 6 subjects';
        } elseif ($level === 'MSCE') {
            if ($gradedCount >= 6) {
                $pointsArray = [];
                foreach ($gradedSubjects as $subject) {
                    $marks = $grades[$student['student_id']][$subject['subject_id']]['marks'];
                    $gradeInfo = getGradeRemarkByMarks($marks, $gradingCriteria);
                    $gradeValue = is_numeric($gradeInfo['grade']) ? (int)$gradeInfo['grade'] : null;
                    if ($gradeValue !== null) $pointsArray[] = $gradeValue;
                }
                if (count($pointsArray) >= 6) {
                    sort($pointsArray);
                    $totalPoints = array_sum(array_slice($pointsArray, 0, 6));
                } else {
                    $totalPoints = '< 6 subjects';
                }
            } else {
                $totalPoints = '< 6 subjects';
            }
        }
        ?>
        <tr>
            <td colspan="2" style="text-align: right; border: 1px solid #000; padding: 5px;"><strong>Aggregate</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><strong><?= $totalMarks ?></strong></td>
            <td colspan="2" style="border: 1px solid #000; padding: 5px;">
                <?php if ($level === 'JCE'): ?>
                    <strong>Grade: <?= htmlspecialchars($displayGrade) ?></strong>
                <?php elseif ($level === 'MSCE'): ?>
                    <strong>Points: <?= htmlspecialchars($totalPoints) ?></strong>
                <?php else: ?>
                    <strong>-</strong>
                <?php endif; ?>
            </td>
            <td style="border: 1px solid #000; padding: 5px;">
                <strong>Status: <?= htmlspecialchars(getPassFailStatus($student['student_id'], $grades, $gradedSubjects)) ?></strong>
            </td>
        </tr>
        </tbody>
    </table>

    <!-- REMARKS & FOOTER -->
    <?php
    $teacherRemarks = getTeacherRemarksByAverage($averageMarks, $gradingCriteria);
    $formTeacherRemark = $teacherRemarks['form_teacher'];
    $headTeacherRemark = $teacherRemarks['head_teacher'];
    ?>
    <table>
        <tr>
            <td colspan="2" style="border: 1px solid #000; padding: 5px;"><strong>Form Teacher Remarks</strong></td>
            <td colspan="2" style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($formTeacherRemark) ?></td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #000; padding: 5px;"><strong>Head Teacher Remarks</strong></td>
            <td colspan="2" style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($headTeacherRemark) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Opening Date</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['opening_date'] ?? '-') ?></td>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Head Teacher's Name</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['head_teacher_name'] ?? '') ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Closing Date</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['closing_date'] ?? '-') ?></td>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Head Teacher's Signature</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Grading Criteria</strong></td>
            <td colspan="3" style="border: 1px solid #000; padding: 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <?php foreach ($gradingCriteria as $criteria): ?>
                            <td style="border-right: 1px solid #000; padding: 3px; font-size: 10px
; text-align: center;">
                                <?= htmlspecialchars($criteria['grade']) ?>
                                (<?= $criteria['min_marks'] . '-' . $criteria['max_marks'] ?>)
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- FEE STRUCTURE -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td colspan="4" style="border: 1px solid #000; padding: 5px; background-color: #f2f2f2;">
                <strong>Fee Structure</strong>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Boarding Students</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['fee_boarding'] ?? '-') ?></td>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Self-Boarding Students</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['fee_self_boarding'] ?? '-') ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Uniform & ID Card</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['fee_uniform'] ?? '-') ?></td>
            <td style="border: 1px solid #000; padding: 5px;"><strong>Registration Fee</strong></td>
            <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($settings['fee_registration'] ?? '-') ?></td>
        </tr>
    </table>
</div>
